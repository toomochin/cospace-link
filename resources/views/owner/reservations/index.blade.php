@extends('layouts.app')

@section('content')
    <div class='user-container'>
        <h2>{{ $shop->name }} 予約・売上</h2>

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
            <button type='submit'>絞り込む</button>
            <a href='{{ route('owner.reservations.index') }}'>条件をクリア</a>
        </form>
        <p>
            <a href='{{ route('owner.reservations.export', array_filter($filters)) }}'>現在の条件でCSV出力</a>
        </p>

        <div>
            <p>確定売上: ¥{{ number_format($confirmedSales) }}</p>
            <p>返金済み金額: ¥{{ number_format($refundedAmount) }}</p>
        </div>

        @forelse ($reservations as $reservation)
            @php
                $statusLabel = match ($reservation->status) {
                    'pending_payment' => '決済確認中',
                    'confirmed' => '予約確定',
                    'cancelled', 'canceled' => 'キャンセル済み',
                    default => $reservation->status,
                };
            @endphp
            <div>
                {{ $reservation->start_time->format('Y/m/d H:i') }} /
                {{ $reservation->reservable?->name }} /
                {{ $reservation->user?->name }} /
                {{ $reservation->reserved_seats }}名 /
                ¥{{ number_format($reservation->price) }} /
                {{ $statusLabel }}
            </div>
        @empty
            <p>条件に該当する予約はありません。</p>
        @endforelse

        {{ $reservations->links() }}
    </div>
@endsection
