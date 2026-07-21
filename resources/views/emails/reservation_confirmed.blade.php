<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body style="font-family: sans-serif; color: #333; line-height: 1.6;">
    <h2>{{ $reservation->user->name }} 様</h2>
    <p>Co-Space Link をご利用いただきありがとうございます。<br>以下の内容で予約が完了いたしました。</p>

    <div style="background: #f9fafb; padding: 15px; border-radius: 6px; border: 1px solid #e5e7eb; margin: 20px 0;">
        <p style="margin: 5px 0;"><strong>施設名:</strong> {{ $reservation->reservable->name ?? '施設情報なし' }}</p>
        <p style="margin: 5px 0;"><strong>利用日時:</strong>
            {{ \Carbon\Carbon::parse($reservation->start_time)->format('Y年m月d日(D) H:i') }} 〜
            {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
        </p>
    </div>

    <p>当日のご来店を心よりお待ちしております。</p>
</body>

</html>