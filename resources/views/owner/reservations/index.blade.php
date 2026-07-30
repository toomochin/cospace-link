@extends('layouts.app')

@section('content')
<div class='admin-container'>
    <div class='admin-header'>
        <h2 class='admin-header-title'>{{ $shop->name }} 予約・売上</h2>
        <a href='{{ route('owner.dashboard') }}' class='link-secondary'>&larr; ダッシュボードに戻る</a>
    </div>

    @if ($errors->any())
        <ul class='text-error'>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method='GET' action='{{ route('owner.reservations.index') }}'>
        <label>利用日（開始）
            <input type='date' name='date_from' value='{{ $filters['date_from'] ?? '' }}'>
        </label>
        <label>利用日（終了）
            <input type='date' name='date_to' value='{{ $filters['date_to'] ?? '' }}'>
        </label>
        <label>予約状態
            <select name='status'>
                <option value=''>すべて</option>
                <option value='pending_payment' @selected(($filters['status'] ?? '') === 'pending_payment')>決済確認中</option>
                <option value='confirmed' @selected(($filters['status'] ?? '') === 'confirmed')>予約確定</option>
                <option value='cancelled' @selected(($filters['status'] ?? '') === 'cancelled')>キャンセル済み</option>
            </select>
        </label>
        <div class='admin-actions-group'>
            <button type='submit' class='btn-primary-sm'>絞り込む</button>
            <a href='{{ route('owner.reservations.index') }}' class='btn-cancel'>条件をクリア</a>
        </div>
    </form>

    <div class='dashboard-summary-grid'>
        <div class='summary-card success'>
            <div class='summary-label'>確定売上</div>
            <div class='summary-value'>&yen;{{ number_format($confirmedSales) }}</div>
        </div>
        <div class='summary-card danger'>
            <div class='summary-label'>返金済み金額</div>
            <div class='summary-value'>&yen;{{ number_format($refundedAmount) }}</div>
        </div>
    </div>

    <p><a href='{{ route('owner.reservations.export', array_filter($filters)) }}' class='btn-admin-action slate'>現在の条件でCSV出力</a></p>

    <div class='table-wrapper'>
        <table class='admin-table owner-reservations-table'>
            <thead>
                <tr>
                    <th style='width: 145px;'>利用日時</th>
                    <th>施設名</th>
                    <th>予約者</th>
                    <th class='text-center' style='width: 65px;'>人数</th>
                    <th class='text-center' style='width: 100px;'>料金</th>
                    <th class='text-center' style='width: 110px;'>状態</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $reservation)
                    @php
                        $statusLabel = match ($reservation->status) {
                            'pending_payment' => '決済確認中',
                            'confirmed' => '予約確定',
                            'cancelled', 'canceled' => 'キャンセル済み',
                            default => $reservation->status,
                        };
                        $statusClass = match ($reservation->status) {
                            'confirmed' => 'confirmed',
                            'cancelled', 'canceled' => 'cancelled',
                            default => 'pending',
                        };
                    @endphp
                    <tr>
                        <td class='col-datetime'>{{ $reservation->start_time->format('Y/m/d H:i') }}</td>
                        <td class='cell-truncate font-bold'>{{ $reservation->reservable?->name }}</td>
                        <td class='cell-truncate'>{{ $reservation->user?->name }}</td>
                        <td class='text-center'>{{ $reservation->reserved_seats }}名</td>
                        <td class='text-center'>&yen;{{ number_format($reservation->price) }}</td>
                        <td class='text-center'><span class='badge-status {{ $statusClass }}'>{{ $statusLabel }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan='6' class='col-empty'>条件に該当する予約はありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class='admin-pagination-container'>{{ $reservations->links() }}</div>
</div>
@endsection
