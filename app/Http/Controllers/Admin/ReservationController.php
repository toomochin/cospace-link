<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * 管理者向け全ユーザー予約一覧表示
     */
    public function index()
    {
        $reservations = Reservation::with(['user', 'reservable'])
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        return view('admin.reservations.index', compact('reservations'));
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
        $startDateTime = Carbon::parse($request->date . ' ' . $request->start_time);
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
}