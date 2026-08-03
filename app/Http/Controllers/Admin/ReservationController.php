<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationController extends Controller
{
    /**
     * 管理者向け全ユーザー予約一覧表示
     */
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->reservationQuery($filters);
        $confirmedSales = (clone $query)->where('reservations.status', 'confirmed')->sum('price');
        $refundedAmount = $this->refundedQuery($filters)->sum('payments.amount');
        $this->applyOrder($query, $filters['order'] ?? 'newest');
        $reservations = $query
            ->with(['user', 'reservable.shop'])
            ->paginate(20)
            ->withQueryString();
        $shops = Shop::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.reservations.index', compact(
            'reservations', 'shops', 'filters', 'confirmedSales', 'refundedAmount'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $query = $this->reservationQuery($filters);
        $this->applyOrder($query, $filters['order'] ?? 'newest');
        $reservations = $query
            ->leftJoin('payments', 'payments.reservation_id', '=', 'reservations.id')
            ->select([
                'reservations.*', 'payments.status as payment_status',
                'payments.amount as payment_amount', 'payments.stripe_refund_id',
            ])
            ->with(['user', 'reservable.shop'])
            ->get();

        return response()->streamDownload(function () use ($reservations): void {
            $output = fopen('php://output', 'w');
            fwrite($output, pack('C*', 0xEF, 0xBB, 0xBF));
            fputcsv($output, [
                '予約ID', '店舗名', '施設名', '利用開始', '利用終了', '利用者名',
                '利用者メール', '人数', '予約金額', '支払方法', '予約状態',
                '決済状態', '返金額', '返金ID',
            ]);

            foreach ($reservations as $reservation) {
                $status = match ($reservation->status) {
                    'pending_payment' => '決済確認中',
                    'confirmed' => '予約確定',
                    'cancelled', 'canceled' => 'キャンセル済み',
                    default => $reservation->status,
                };
                $paymentStatus = match (true) {
                    $reservation->payment_status === 'refunded' => '返金済み',
                    $reservation->payment_status === 'succeeded' => '決済済み',
                    $reservation->payment_type === 'onsite' => '現地払い',
                    default => '決済確認中',
                };

                fputcsv($output, [
                    $reservation->id,
                    $reservation->reservable?->shop?->name,
                    $reservation->reservable?->name,
                    $reservation->start_time->format('Y-m-d H:i'),
                    $reservation->end_time->format('Y-m-d H:i'),
                    $reservation->user?->name,
                    $reservation->user?->email,
                    $reservation->reserved_seats,
                    $reservation->price,
                    $reservation->payment_type === 'onsite' ? '現地払い' : 'クレジットカード',
                    $status,
                    $paymentStatus,
                    $reservation->payment_status === 'refunded' ? $reservation->payment_amount : 0,
                    $reservation->stripe_refund_id,
                ]);
            }

            fclose($output);
        }, 'all-shop-reservations-'.now()->format('Ymd').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * 代理予約作成画面表示
     */
    public function create()
    {
        // アクティブな一般ユーザー一覧を取得
        $users = User::where('is_admin', false)
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        // 公開中の施設一覧を取得
        $facilities = Facility::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.reservations.create', compact('users', 'facilities'));
    }

    /**
     * 代理予約保存処理（定員・重複判定含む）
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'facility_id' => ['required', 'exists:facilities,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration' => ['required', 'integer', 'min:1', 'max:12'], // 30分単位のコマ数
            'reserved_seats' => ['required', 'integer', 'min:1'],
            'payment_type' => ['required', 'in:stripe,local,free'], // ★ 現地払い・無料対応の選択を追加
        ], [
            'user_id.required' => '対象の会員を選択してください。',
            'facility_id.required' => '対象の施設を選択してください。',
            'date.required' => '利用日を選択してください。',
            'date.after_or_equal' => '本日以降の日付を選択してください。',
            'start_time.required' => '開始時間を選択してください。',
            'duration.required' => '利用時間を選択してください。',
            'reserved_seats.required' => '人数を選択してください。',
            'payment_type.required' => '支払方法を選択してください。',
        ]);

        $facility = Facility::findOrFail($request->facility_id);

        // 利用人数の定員チェック
        if ($request->reserved_seats > $facility->capacity) {
            return back()->withInput()->withErrors([
                'reserved_seats' => "選択した施設の定員（{$facility->capacity}名）を超えています。",
            ]);
        }

        // 開始日時と終了日時の算出
        $startDateTime = Carbon::parse($request->date.' '.$request->start_time);
        $endDateTime = (clone $startDateTime)->addMinutes($request->duration * 30);

        // 重複予約（予約の重複判定）
        $existingReservationsCount = Reservation::where('reservable_type', Facility::class)
            ->where('reservable_id', $facility->id)
            ->whereNotIn('status', ['cancelled', 'canceled'])
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where('start_time', '<', $endDateTime)
                    ->where('end_time', '>', $startDateTime);
            })
            ->count();

        if ($facility->type === 'meeting_room' && $existingReservationsCount > 0) {
            return back()->withInput()->withErrors([
                'start_time' => '指定された時間帯は既に予約が入っています。',
            ]);
        }

        if ($facility->type === 'area') {
            $alreadyReservedSeats = Reservation::where('reservable_type', Facility::class)
                ->where('reservable_id', $facility->id)
                ->whereNotIn('status', ['cancelled', 'canceled'])
                ->where(function ($query) use ($startDateTime, $endDateTime) {
                    $query->where('start_time', '<', $endDateTime)
                        ->where('end_time', '>', $startDateTime);
                })
                ->sum('reserved_seats');

            if (($alreadyReservedSeats + $request->reserved_seats) > $facility->capacity) {
                $available = $facility->capacity - $alreadyReservedSeats;

                return back()->withInput()->withErrors([
                    'reserved_seats' => "指定の時間帯の残席数は {$available} 名分です。",
                ]);
            }
        }

        // 代理予約の作成
        Reservation::create([
            'user_id' => $request->user_id,
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'reserved_seats' => $request->reserved_seats,
            'status' => 'confirmed', // 代理予約は即時確定扱い
            'payment_type' => $request->payment_type, // ★ 選択された支払方法を保存
        ]);

        return redirect()->route('admin.reservations.index')->with('status', '代理予約を登録しました。');
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', Rule::in(['pending_payment', 'confirmed', 'cancelled'])],
            'order' => ['nullable', Rule::in([
                'newest', 'oldest', 'shop_asc', 'area_asc', 'facility_asc', 'user_asc', 'status_asc',
            ])],
        ]);
    }

    private function applyOrder(Builder $query, string $order): Builder
    {
        [$sort, $direction] = match ($order) {
            'oldest' => ['start_time', 'asc'],
            'shop_asc' => ['shop', 'asc'],
            'area_asc' => ['area', 'asc'],
            'facility_asc' => ['facility', 'asc'],
            'user_asc' => ['user', 'asc'],
            'status_asc' => ['status', 'asc'],
            default => ['start_time', 'desc'],
        };

        $column = match ($sort) {
            'shop' => Shop::query()
                ->select('shops.name')
                ->join('facilities as order_facilities', 'order_facilities.shop_id', '=', 'shops.id')
                ->whereColumn('order_facilities.id', 'reservations.reservable_id')
                ->limit(1),
            'area' => Shop::query()
                ->select('shops.area_name')
                ->join('facilities as order_facilities', 'order_facilities.shop_id', '=', 'shops.id')
                ->whereColumn('order_facilities.id', 'reservations.reservable_id')
                ->limit(1),
            'facility' => Facility::query()
                ->select('facilities.name')
                ->whereColumn('facilities.id', 'reservations.reservable_id')
                ->limit(1),
            'user' => User::query()
                ->select('users.name')
                ->whereColumn('users.id', 'reservations.user_id')
                ->limit(1),
            'status' => 'reservations.status',
            default => 'reservations.start_time',
        };

        return $query
            ->orderBy($column, $direction)
            ->orderBy('reservations.id', $direction);
    }


    private function reservationQuery(array $filters): Builder
    {
        return Reservation::query()
            ->whereHasMorph('reservable', [Facility::class], function ($query) use ($filters): void {
                $query->when($filters['shop_id'] ?? null, fn ($query, $shopId) => $query->where('shop_id', $shopId));
            })
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('reservations.start_time', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('reservations.start_time', '<=', $date))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('reservations.status', $status));
    }

    private function refundedQuery(array $filters)
    {
        return DB::table('payments')
            ->join('reservations', 'payments.reservation_id', '=', 'reservations.id')
            ->join('facilities', function ($join): void {
                $join->on('reservations.reservable_id', '=', 'facilities.id')
                    ->where('reservations.reservable_type', Facility::class);
            })
            ->where('payments.status', 'refunded')
            ->when($filters['shop_id'] ?? null, fn ($query, $shopId) => $query->where('facilities.shop_id', $shopId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('reservations.start_time', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('reservations.start_time', '<=', $date))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('reservations.status', $status));
    }
}
