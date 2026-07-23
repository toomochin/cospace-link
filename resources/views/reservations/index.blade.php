@extends('layouts.app')

@section('content')
    <div class="user-container">
        <h2 class="user-title" style="margin-bottom: 20px;">マイページ（予約履歴）</h2>

        {{-- ステータスメッセージ --}}
        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

        {{-- 予約履歴リスト --}}
        @forelse ($reservations as $reservation)
            <div class="reservation-card">
                <div>
                    {{-- 予約ステータスバッジ --}}
                    <span class="user-badge {{ $reservation->status === 'confirmed' ? 'user-badge-green' : 'user-badge-gray' }}" style="margin-bottom: 8px;">
                        {{ $reservation->status === 'confirmed' ? '予約確定' : 'キャンセル済み' }}
                    </span>

                    {{-- ★ お支払い方法バッジの追加 --}}
                    @if ($reservation->payment_type === 'onsite')
                        <span class="user-badge" style="background-color: #fef3c7; color: #92400e; margin-bottom: 8px;">
                            現地払い
                        </span>
                    @elseif ($reservation->payment_type === 'free')
                        <span class="user-badge" style="background-color: #dbeafe; color: #1e40af; margin-bottom: 8px;">
                            無料
                        </span>
                    @else
                        <span class="user-badge" style="background-color: #e0f2fe; color: #0369a1; margin-bottom: 8px;">
                            クレジットカード（決済済）
                        </span>
                    @endif

                    {{-- 施設名 --}}
                    <h3 class="reservation-card-title">
                        {{ $reservation->reservable->name ?? '施設情報なし' }}
                    </h3>

                    {{-- 利用日時 --}}
                    <p class="reservation-card-info">
                        <strong>利用日時:</strong>
                        {{ \Carbon\Carbon::parse($reservation->start_time)->format('Y年m月d日(D) H:i') }} 〜
                        {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
                    </p>

                    {{-- ★ 利用料金の表示追加 --}}
                    <p class="reservation-card-info" style="margin-top: 4px;">
                        <strong>ご利用料金:</strong>
                        ¥{{ number_format($reservation->price) }}
                    </p>
                </div>

                <div>
                    {{-- 未来の予約かつ予約確定状態の場合のみキャンセルボタンを表示 --}}
                    @if ($reservation->status === 'confirmed' && \Carbon\Carbon::parse($reservation->start_time)->isFuture())
                        <form action="{{ route('reservations.destroy', $reservation->id) }}" method="POST"
                            onsubmit="return confirm('本当にこの予約をキャンセルしますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-user-danger">
                                キャンセル
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-empty">現在、予約履歴はありません。</p>
        @endforelse

        <div style="margin-top: 24px;">
            <a href="{{ route('home') }}" class="btn-user-secondary">&larr; 施設一覧に戻る</a>
        </div>
    </div>
@endsection