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

        {{-- ★ お支払い金額と決済方法の分岐追加 --}}
        <p style="margin: 5px 0;"><strong>ご利用料金:</strong> ¥{{ number_format($reservation->price) }}</p>
        <p style="margin: 5px 0;"><strong>お支払い方法:</strong> 
            @if ($reservation->payment_type === 'onsite')
                <span style="color: #d97706; font-weight: bold;">現地払い</span>
            @elseif ($reservation->payment_type === 'free')
                <span style="color: #2563eb; font-weight: bold;">無料（招待・特別対応）</span>
            @else
                <span style="color: #16a34a; font-weight: bold;">クレジットカード決済（完了）</span>
            @endif
        </p>
    </div>

    {{-- ★ 現地払いの場合のみ注意事項を補足 --}}
    @if ($reservation->payment_type === 'onsite')
        <div style="background: #fffbebf8; border: 1px solid #fef3c7; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; color: #92400e;">
            ※ 本日のお支払いはご来店時に受付にて（¥{{ number_format($reservation->price) }}）お支払いをお願いいたします。
        </div>
    @endif

    <p>当日のご来店を心よりお待ちしております。</p>
</body>

</html>