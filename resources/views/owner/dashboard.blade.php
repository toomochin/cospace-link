@extends('layouts.app')

@section('content')
    <div class='user-container'>
        <h2>{{ $shop->name }} 管理ダッシュボード</h2>
        <nav>
            <a href='{{ route('owner.facilities.index') }}'>施設管理</a>
            <a href='{{ route('owner.reservations.index') }}'>予約・売上</a>
            <a href='{{ route('owner.shop.edit') }}'>店舗情報編集</a>
        </nav>

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
                <div class='summary-value'>{{ $todayOperatingFacilitiesCount }} / {{ $activeFacilitiesCount }} <span class='summary-unit'>施設（{{ $todayFacilityUtilizationRate }}%）</span></div>
            </div>
        </div>

        <p>登録施設数: {{ $shop->facilities_count }}（公開中 {{ $activeFacilitiesCount }}）</p>
        <p>有効予約数: {{ $reservationCount }}</p>

        <h3>本日の予約</h3>
        @forelse ($todayReservations as $reservation)
            <div>
                {{ $reservation->start_time->format('H:i') }}〜{{ $reservation->end_time->format('H:i') }} /
                {{ $reservation->reservable?->name }} /
                {{ $reservation->user?->name }} /
                {{ $reservation->reserved_seats }}名
            </div>
        @empty
            <p>本日の確定予約はありません。</p>
        @endforelse

        <h3>直近の予約</h3>
        @forelse ($upcomingReservations as $reservation)
            <div>
                {{ $reservation->start_time->format('Y/m/d H:i') }} /
                {{ $reservation->reservable?->name }} /
                {{ $reservation->user?->name }}
            </div>
        @empty
            <p>今後の確定予約はありません。</p>
        @endforelse
    </div>
@endsection
