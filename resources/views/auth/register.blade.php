<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>新規会員登録 - Co-Space Link</title>
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
    <h1>新規会員登録</h1>

    @if ($errors->any())
        <div class="error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">お名前</label><br>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="email">メールアドレス</label><br>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="password">パスワード</label><br>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">パスワード（確認用）</label><br>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit">登録する</button>
    </form>

    <p><a href="{{ route('login') }}">ログインはこちら</a></p>
</body>

</html>