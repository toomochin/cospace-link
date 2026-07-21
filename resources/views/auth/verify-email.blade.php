<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>メール認証が必要です - Co-Space Link</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 50px;
            line-height: 1.6;
        }

        .status {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>メールアドレスの確認</h1>

    @if (session('status') == 'verification-link-sent')
        <div class="status">
            新しい認証リンクが、登録されたメールアドレスに送信されました。
        </div>
    @endif

    <p>
        ご登録ありがとうございます！<br>
        さきほど入力されたメールアドレスに認証用リンクを送信しました。<br>
        メール内のボタンかURLをクリックして、会員登録を完了させてください。
    </p>

    <p>もしメールが届いていない場合は、以下のボタンから再送信できます。</p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">認証メールを再送信する</button>
    </form>

    <hr>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
</body>

</html>