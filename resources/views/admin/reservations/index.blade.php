@extends('layouts.app')

@section('content')
    <div class="admin-container">
        {{-- ヘッダーエリア --}}
        <div class="admin-header">
            <h2 class="admin-header-title">管理者：予約一覧（全ユーザー）</h2>
            <div class="admin-header-actions">
                <a href="{{ route('admin.reservations.create') }}" class="btn-primary">
                    ＋ 代理予約を登録
                </a>
                <a href="{{ route('admin.dashboard') }}" class="link-secondary">&larr; ダッシュボードに戻る</a>
            </div>
        </div>

        <form method='GET' action='{{ route('admin.reservations.index') }}' class='reservation-filter-form admin-reservation-filter-form'>
            <label class='reservation-filter-field'>店舗
                <select name='shop_id'>
                    <option value=''>全店舗</option>
                    @foreach ($shops as $shop)
                        <option value='{{ $shop->id }}' @selected((string) ($filters['shop_id'] ?? '') === (string) $shop->id)>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class='reservation-filter-field'>利用日（開始）<input type='date' name='date_from' value='{{ $filters['date_from'] ?? '' }}'></label>
            <label class='reservation-filter-field'>利用日（終了）<input type='date' name='date_to' value='{{ $filters['date_to'] ?? '' }}'></label>
            <label class='reservation-filter-field'>予約状態
                <select name='status'>
                    <option value=''>すべて</option>
                    <option value='pending_payment' @selected(($filters['status'] ?? '') === 'pending_payment')>決済確認中</option>
                    <option value='confirmed' @selected(($filters['status'] ?? '') === 'confirmed')>予約確定</option>
                    <option value='cancelled' @selected(($filters['status'] ?? '') === 'cancelled')>キャンセル済み</option>
                </select>
            </label>
            <label class='reservation-filter-field'>並び順
                <select name='order'>
                    <option value='newest' @selected(($filters['order'] ?? 'newest') === 'newest')>利用日時が新しい順</option>
                    <option value='oldest' @selected(($filters['order'] ?? '') === 'oldest')>利用日時が古い順</option>
                    <option value='shop_asc' @selected(($filters['order'] ?? '') === 'shop_asc')>店舗名順</option>
                    <option value='area_asc' @selected(($filters['order'] ?? '') === 'area_asc')>エリア順</option>
                    <option value='facility_asc' @selected(($filters['order'] ?? '') === 'facility_asc')>施設名順</option>
                    <option value='user_asc' @selected(($filters['order'] ?? '') === 'user_asc')>予約者名順</option>
                    <option value='status_asc' @selected(($filters['order'] ?? '') === 'status_asc')>予約状態順</option>
                </select>
            </label>

            <div class='reservation-filter-actions'>
                <button type='submit' class='btn-primary-sm'>絞り込む</button>
                <a href='{{ route('admin.reservations.index') }}' class='btn-cancel'>条件をクリア</a>
            </div>
        </form>
        <p>
            確定売上: ¥{{ number_format($confirmedSales) }} /
            返金済み金額: ¥{{ number_format($refundedAmount) }}
        </p>
        <p><a href='{{ route('admin.reservations.export', array_filter($filters)) }}'>現在の条件でCSV出力</a></p>

        {{-- テーブル一覧 --}}
        <div class='table-wrapper'>
        <table class="admin-table admin-reservations-table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th>店舗名</th>
                    <th>エリア</th>
                    <th>予約者</th>
                    <th>施設名</th>
                    <th class="col-datetime">利用日時</th>
                    <th class="col-status">ステータス</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $reservation)
                    <tr>
                        <td class="col-id">{{ $reservation->id }}</td>
                        <td>{{ $reservation->reservable?->shop?->name ?? '店舗情報なし' }}</td>
                        <td>{{ $reservation->reservable?->shop?->area_name ?? '未設定' }}</td>
                        <td>{{ $reservation->user->name ?? '不明' }}</td>
                        <td class="font-bold">{{ $reservation->reservable->name ?? '施設情報なし' }}</td>
                        <td class="col-datetime">
                            {{ \Carbon\Carbon::parse($reservation->start_time)->format('Y/m/d H:i') }} 〜
                            {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
                        </td>
                        <td class="col-status">
                            @php
                                $statusClass = match($reservation->status) {
                                    'confirmed' => 'confirmed',
                                    'cancelled' => 'cancelled',
                                    default     => 'pending',
                                };
                            @endphp
                            <span class="badge-status {{ $statusClass }}">
                                {{ $reservation->status === 'confirmed' ? '予約確定' : ($reservation->status === 'cancelled' ? 'キャンセル済' : '決済確認中') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="col-empty">予約データはありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        </div>

        {{-- 横揃えページネーションエリア --}}
        <div class="admin-pagination-container">
            {{ $reservations->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
