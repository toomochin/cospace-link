@extends('layouts.app')

@section('content')
    <div
        style="max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e5e7eb; text-align: center;">
        <div style="font-size: 3em; color: #16a34a; margin-bottom: 10px;">✓</div>
        <h2 style="margin: 0 0 10px 0; color: #1f2937;">決済・予約が完了しました！</h2>
        <p style="color: #4b5563; margin-bottom: 25px;">ご予約ありがとうございます。マイページからいつでも予約状況をご確認いただけます。</p>

        <div
            style="background: #f9fafb; padding: 15px; border-radius: 6px; text-align: left; margin-bottom: 25px; font-size: 0.95em;">
            <p style="margin: 5px 0;"><strong>施設名:</strong> {{ $reservation->reservable->name ?? '' }}</p>
            <p style="margin: 5px 0;"><strong>利用日時:</strong>
                {{ \Carbon\Carbon::parse($reservation->start_time)->format('Y年m月d日(D) H:i') }} 〜
                {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
            </p>
        </div>

        <div style="display: flex; gap: 10px; justify-content: center;">
            <a href="{{ route('reservations.index') }}"
                style="padding: 10px 20px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.9em;">
                マイページ（予約履歴）を見る
            </a>
            <a href="{{ route('home') }}"
                style="padding: 10px 20px; background: #e5e7eb; color: #374151; text-decoration: none; border-radius: 4px; font-size: 0.9em;">
                トップへ戻る
            </a>
        </div>
    </div>
@endsection