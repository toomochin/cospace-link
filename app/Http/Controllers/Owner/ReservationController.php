<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 店舗管理者向けの自店舗予約一覧・CSV出力を担当する。
 */
class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user()->shop;
        $filters = $this->filters($request);
        $query = $this->reservationQuery($shop->id, $filters);

        $confirmedSales = (clone $query)->where('status', 'confirmed')->sum('price');
        $refundedAmount = DB::table('payments')
            ->join('reservations', 'payments.reservation_id', '=', 'reservations.id')
            ->join('facilities', function ($join): void {
                $join->on('reservations.reservable_id', '=', 'facilities.id')
                    ->where('reservations.reservable_type', Facility::class);
            })
            ->where('facilities.shop_id', $shop->id)
            ->where('payments.status', 'refunded')
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('reservations.start_time', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('reservations.start_time', '<=', $date))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('reservations.status', $status))
            ->sum('payments.amount');

        $reservations = $query
            ->with(['user', 'reservable'])
            ->orderByDesc('start_time')
            ->paginate(20)
            ->withQueryString();

        return view('owner.reservations.index', compact(
            'shop',
            'reservations',
            'confirmedSales',
            'refundedAmount',
            'filters',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $shop = $request->user()->shop;
        $filters = $this->filters($request);
        $reservations = $this->reservationQuery($shop->id, $filters)
            ->leftJoin('payments', 'payments.reservation_id', '=', 'reservations.id')
            ->select([
                'reservations.*',
                'payments.status as payment_status',
                'payments.amount as payment_amount',
                'payments.stripe_refund_id',
            ])
            ->with(['user', 'reservable'])
            ->orderByDesc('reservations.start_time')
            ->get();

        return response()->streamDownload(function () use ($reservations): void {
            $output = fopen('php://output', 'w');
            fwrite($output, pack('C*', 0xEF, 0xBB, 0xBF));
            fputcsv($output, [
                '予約ID', '利用開始', '利用終了', '施設名', '利用者名', '利用者メール',
                '人数', '予約金額', '支払方法', '予約状態', '決済状態', '返金額', '返金ID',
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
                    $reservation->start_time->format('Y-m-d H:i'),
                    $reservation->end_time->format('Y-m-d H:i'),
                    $reservation->reservable?->name,
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
        }, 'shop-reservations-'.now()->format('Ymd').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', Rule::in(['pending_payment', 'confirmed', 'cancelled'])],
        ]);
    }

    private function reservationQuery(int $shopId, array $filters): Builder
    {
        return Reservation::query()
            ->whereHasMorph('reservable', [Facility::class], fn ($query) => $query->where('shop_id', $shopId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('reservations.start_time', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('reservations.start_time', '<=', $date))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('reservations.status', $status));
    }
}
