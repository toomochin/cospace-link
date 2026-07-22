@extends('layouts.app')

@section('content')
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 420px;">

            <h2 class="auth-title">新規会員登録</h2>

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

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- お名前 -->
                <div class="form-group">
                    <label for="name" class="form-label">お名前</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" autofocus
                        class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- メールアドレス -->
                <div class="form-group">
                    <label for="email" class="form-label">メールアドレス</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- パスワード -->
                <div class="form-group">
                    <label for="password" class="form-label">パスワード</label>
                    <input id="password" type="password" name="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}">
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- パスワード（確認用） -->
                <div class="form-group" style="margin-bottom: 24px;">
                    <label for="password_confirmation" class="form-label">パスワード（確認用）</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                        class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}">
                </div>

                <!-- 登録ボタン -->
                <button type="submit" class="btn-user-primary" style="width: 100%; padding: 12px; font-size: 0.95em;">
                    アカウントを作成する
                </button>

                <!-- ログインへの案内 -->
                <div class="auth-footer-link">
                    すでにアカウントをお持ちの方は
                    <a href="{{ route('login') }}">ログイン</a>
                </div>
            </form>
        </div>
    </div>
@endsection