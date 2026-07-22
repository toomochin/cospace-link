@extends('layouts.app')

@section('content')
    <div class="auth-wrapper">
        <div class="auth-card">

            <h2 class="auth-title">ログイン</h2>

            {{-- エラーメッセージ表示エリア --}}
            @if ($errors->any())
                <div class="alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- メールアドレス -->
                <div class="form-group">
                    <label for="email" class="form-label">メールアドレス</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- パスワード -->
                <div class="form-group" style="margin-bottom: 24px;">
                    <label for="password" class="form-label">パスワード</label>
                    <input id="password" type="password" name="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}">
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- ログインボタン -->
                <button type="submit" class="btn-user-primary" style="width: 100%; padding: 12px; font-size: 0.95em;">
                    ログイン
                </button>

                <!-- 登録への案内 -->
                <div class="auth-footer-link">
                    アカウントをお持ちでない方は
                    <a href="{{ route('register') }}">会員登録</a>
                </div>
            </form>
        </div>
    </div>
@endsection