<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body style="font-family: sans-serif; color: #333; line-height: 1.6;">
    <h2>{{ $reservation->user->name }} 様</h2>
    <p>以下の予約のキャンセル手続きが完了いたしました。</p>

    <div style="background: #fff5f5; padding: 15px; border-radius: 6px; border: 1px solid #feb2b2; margin: 20px 0;">
        <p style="margin: 5px 0;"><strong>施設名:</strong> {{ $reservation->reservable->name ?? '施設情報なし' }}</p>
        <p style="margin: 5px 0;"><strong>対象日時:</strong>
            {{ \Carbon\Carbon::parse($reservation->start_time)->format('Y年m月d日(D) H:i') }} 〜
            {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
        </p>
    </div>

    <p>またのご利用をお待ちしております。</p>
</body>

</html>