<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ログイン - Co-Space Link</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 50px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>
    <h1>ログイン</h1>

    <!-- エラーメッセージの表示 -->
    @if ($errors->any())
        <div class="error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">メールアドレス</label><br>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">パスワード</label><br>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">ログイン</button>
    </form>

    <p><a href="{{ route('register') }}">新規会員登録はこちら</a></p>
</body>

</html>