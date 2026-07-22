@extends('layouts.app')

@section('content')
    <div class="user-container">
        <div class="user-card completed-card">
            <div class="completed-icon">✓</div>
            <h2 class="user-title" style="margin-bottom: 10px;">決済・予約が完了しました！</h2>
            <p class="completed-desc">ご予約ありがとうございます。マイページからいつでも予約状況をご確認いただけます。</p>

            <div class="completed-summary">
                <p><strong>施設名:</strong> {{ $reservation->reservable->name ?? '' }}</p>
                <p><strong>利用日時:</strong>
                    {{ \Carbon\Carbon::parse($reservation->start_time)->format('Y年m月d日(D) H:i') }} 〜
                    {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
                </p>
            </div>

            <div class="completed-actions">
                <a href="{{ route('reservations.index') }}" class="btn-user-primary">
                    マイページ（予約履歴）を見る
                </a>
                <a href="{{ route('home') }}" class="btn-user-secondary">
                    トップへ戻る
                </a>
            </div>
        </div>
    </div>
@endsection