@extends('layouts.app')

@section('content')
<div class='admin-container'>
    <div class='admin-header-flex'>
        <div>
            <h2 class='admin-header-title'>{{ $shop->name }} 管理ダッシュボード</h2>
            <div class='admin-header-sub'>自店舗の本日の予約・売上状況</div>
        </div>
        <div class='admin-actions-group'>
            <a href='{{ route('owner.facilities.index') }}' class='btn-admin-action blue'>施設管理</a>
            <a href='{{ route('owner.reservations.index') }}' class='btn-admin-action slate'>予約・売上</a>
            <a href='{{ route('owner.shop.edit') }}' class='btn-admin-action dark'>店舗情報編集</a>
        </div>
    </div>

    <h3>本日の状況</h3>
    <div class='dashboard-summary-grid'>
        <div class='summary-card default'>
            <div class='summary-label'>本日の予約</div>
            <div class='summary-value'>{{ $todayReservationsCount }} <span class='summary-unit'>件</span></div>
        </div>
        <div class='summary-card success'>
            <div class='summary-label'>本日の確定売上</div>
            <div class='summary-value'>&yen;{{ number_format($todaySales) }}</div>
        </div>
        <div class='summary-card danger'>
            <div class='summary-label'>本日の返金額</div>
            <div class='summary-value'>&yen;{{ number_format($todayRefundedAmount) }}</div>
        </div>
        <div class='summary-card default'>
            <div class='summary-label'>本日の稼働施設</div>
            <div class='summary-value'>{{ $todayOperatingFacilitiesCount }} / {{ $activeFacilitiesCount }}</div>
            <div class='admin-header-sub'>稼働率 {{ $todayFacilityUtilizationRate }}%</div>
        </div>
        <div class='summary-card default'>
            <div class='summary-label'>登録施設</div>
            <div class='summary-value'>{{ $shop->facilities_count }} <span class='summary-unit'>件</span></div>
            <div class='admin-header-sub'>公開中 {{ $activeFacilitiesCount }}件</div>
        </div>
        <div class='summary-card default'>
            <div class='summary-label'>有効予約</div>
            <div class='summary-value'>{{ $reservationCount }} <span class='summary-unit'>件</span></div>
        </div>
    </div>

    <h3>本日の予約</h3>
    <div class='table-wrapper owner-dashboard-table'>
        <table class='admin-table'>
            <thead><tr><th>利用時間</th><th>施設名</th><th>予約者</th><th class='text-center'>人数</th></tr></thead>
            <tbody>
                @forelse ($todayReservations as $reservation)
                    <tr>
                        <td class='col-datetime'>{{ $reservation->start_time->format('H:i') }}〜{{ $reservation->end_time->format('H:i') }}</td>
                        <td class='font-bold'>{{ $reservation->reservable?->name }}</td>
                        <td>{{ $reservation->user?->name }}</td>
                        <td class='text-center'>{{ $reservation->reserved_seats }}名</td>
                    </tr>
                @empty
                    <tr><td colspan='4' class='col-empty'>本日の確定予約はありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h3>直近の予約</h3>
    <div class='table-wrapper owner-dashboard-table'>
        <table class='admin-table'>
            <thead><tr><th>利用日時</th><th>施設名</th><th>予約者</th></tr></thead>
            <tbody>
                @forelse ($upcomingReservations as $reservation)
                    <tr>
                        <td class='col-datetime'>{{ $reservation->start_time->format('Y/m/d H:i') }}</td>
                        <td class='font-bold'>{{ $reservation->reservable?->name }}</td>
                        <td>{{ $reservation->user?->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan='3' class='col-empty'>今後の確定予約はありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
