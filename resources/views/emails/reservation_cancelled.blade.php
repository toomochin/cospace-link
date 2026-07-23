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

        {{-- ★ お支払い方法・返金に関する案内の追加 --}}
        <p style="margin: 5px 0;"><strong>お支払い方法:</strong>
            @if ($reservation->payment_type === 'onsite')
                現地払い（お支払いは発生いたしません）
            @elseif ($reservation->payment_type === 'free')
                無料対応
            @else
                クレジットカード決済
            @endif
        </p>
    </div>

    {{-- ★ 決済方法に応じた補足メッセージ --}}
    @if ($reservation->payment_type === 'onsite')
        <p style="font-size: 0.9rem; color: #666;">
            ※ 現地払いの予約のため、今回のお手続きによるご請求・精算はございません。
        </p>
    @elseif ($reservation->payment_type === 'free')
        <p style="font-size: 0.9rem; color: #666;">
            ※ 無料予約のキャンセルのため、ご請求・精算はございません。
        </p>
    @else
        {{-- credit_card または default --}}
        <p style="font-size: 0.9rem; color: #666;">
            ※ クレジットカード決済の返金につきましては、ご使用のカード会社の締め日により返金タイミングが異なる場合がございます。
        </p>
    @endif

    <p>またのご利用をお待ちしております。</p>
</body>

</html>