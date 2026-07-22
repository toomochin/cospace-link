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
                <a href="{{ route('admin.facilities.index') }}" class="link-secondary">
                    &larr; 施設管理に戻る
                </a>
            </div>
        </div>

        {{-- テーブル一覧 --}}
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
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
                                {{ $reservation->status === 'confirmed' ? '予約確定' : ($reservation->status === 'cancelled' ? 'キャンセル済' : '決済待ち') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="col-empty">予約データはありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- 横揃えページネーションエリア --}}
        <div class="admin-pagination-container">
            {{ $reservations->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection