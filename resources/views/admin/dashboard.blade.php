@extends('layouts.app')

@section('content')
<div class="admin-card-container">
    {{-- ヘッダーエリア（タイトル ＋ アクションボタン） --}}
    <div class="admin-header-flex">
        <div>
            <h2 class="admin-header-title" style="margin: 0;">管理者ダッシュボード</h2>
            <span class="admin-header-sub">{{ now()->format('Y年m月d日(D)') }} の状況</span>
        </div>

        {{-- アクションボタン群 --}}
        <div class="admin-actions-group">
            <a href="{{ route('admin.reservations.create') }}" class="btn-primary" style="padding: 8px 16px; font-size: 0.9rem; text-decoration: none;">
                ＋ 代理予約を登録
            </a>
            <a href="{{ route('admin.reservations.index') }}" class="btn-admin-action blue">
                📅 予約一覧
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn-admin-action slate">
                👥 会員一覧
            </a>
            <a href="{{ route('admin.facilities.index') }}" class="btn-admin-action dark">
                🏢 施設管理
            </a>
        </div>
    </div>

    {{-- サマリーカードエリア --}}
    <div class="dashboard-summary-grid">
        <div class="summary-card default">
            <div class="summary-label">本日の予約件数</div>
            <div class="summary-value">{{ $todayReservationsCount }} <span class="summary-unit">件</span></div>
        </div>
        <div class="summary-card success">
            <div class="summary-label">本日の売上見込み</div>
            <div class="summary-value">&yen;{{ number_format($todaySales) }}</div>
        </div>
        <div class="summary-card danger">
            <div class="summary-label">本日のキャンセル</div>
            <div class="summary-value">{{ $todayCancellationsCount }} <span class="summary-unit">件</span></div>
        </div>
    </div>

    {{-- 予約スケジュール タイムラインエリア --}}
    <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 15px; color: #334155;">本日の利用スケジュール (9:00 - 21:00)</h3>
    
    <div class="table-wrapper">
        <table class="admin-table" style="min-width: 800px; table-layout: auto;">
            <thead>
                <tr class="text-center">
                    <th style="width: 180px; text-align: left; border-right: 1px solid #cbd5e1;">施設名</th>
                    @for ($h = 9; $h < 21; $h++)
                        <th style="width: 60px; text-align: center; border-right: 1px solid #e2e8f0;">{{ sprintf('%02d:00', $h) }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse ($facilities as $facility)
                    <tr>
                        <td style="font-weight: bold; border-right: 1px solid #cbd5e1; background: #f8fafc;">
                            {{ $facility->name }}
                            <div class="text-muted" style="font-size: 0.75rem; font-weight: normal;">
                                {{ $facility->type === 'meeting_room' ? '個室' : 'エリア' }} (定員{{ $facility->capacity }}名)
                            </div>
                        </td>
                        @for ($h = 9; $h < 21; $h++)
                            @php
                                $slotStart = \Carbon\Carbon::today()->setHour($h)->setMinute(0);
                                $slotEnd = (clone $slotStart)->addHour();

                                $reserved = $todayReservations->filter(function ($res) use ($facility, $slotStart, $slotEnd) {
                                    return $res->reservable_id == $facility->id 
                                        && \Carbon\Carbon::parse($res->start_time) < $slotEnd 
                                        && \Carbon\Carbon::parse($res->end_time) > $slotStart;
                                });
                            @endphp
                            <td class="text-center" style="padding: 4px; border-right: 1px solid #e2e8f0;">
                                @if ($reserved->count() > 0)
                                    <div style="background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; padding: 4px 2px; font-size: 0.75rem;">
                                        @foreach ($reserved as $res)
                                            <div>{{ $res->user->name ?? '予約済' }}</div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size: 0.8rem;">-</span>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center text-muted" style="padding: 20px;">登録されている施設がありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection