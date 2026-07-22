@extends('layouts.app')

@section('content')
    <div style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 20px 0;">
        <div
            style="width: 100%; max-width: 420px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e5e7eb;">

            <h2 style="margin: 0 0 24px 0; font-size: 1.5em; text-align: center; color: #1f2937;">新規会員登録</h2>

            {{-- エラーメッセージ表示エリア --}}
            @if ($errors->any())
                <div
                    style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px; font-size: 0.875em; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- お名前 -->
                <div style="margin-bottom: 18px;">
                    <label for="name"
                        style="display: block; font-size: 0.875em; font-weight: 600; color: #374151; margin-bottom: 6px;">お名前</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" autofocus
                        style="width: 100%; padding: 10px 12px; border: 1px solid {{ $errors->has('name') ? '#dc2626' : '#d1d5db' }}; border-radius: 6px; font-size: 0.95em; box-sizing: border-box;">
                    @error('name')
                        <span style="color: #dc2626; font-size: 0.8em; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- メールアドレス -->
                <div style="margin-bottom: 18px;">
                    <label for="email"
                        style="display: block; font-size: 0.875em; font-weight: 600; color: #374151; margin-bottom: 6px;">メールアドレス</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        style="width: 100%; padding: 10px 12px; border: 1px solid {{ $errors->has('email') ? '#dc2626' : '#d1d5db' }}; border-radius: 6px; font-size: 0.95em; box-sizing: border-box;">
                    @error('email')
                        <span style="color: #dc2626; font-size: 0.8em; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- パスワード -->
                <div style="margin-bottom: 18px;">
                    <label for="password"
                        style="display: block; font-size: 0.875em; font-weight: 600; color: #374151; margin-bottom: 6px;">パスワード</label>
                    <input id="password" type="password" name="password"
                        style="width: 100%; padding: 10px 12px; border: 1px solid {{ $errors->has('password') ? '#dc2626' : '#d1d5db' }}; border-radius: 6px; font-size: 0.95em; box-sizing: border-box;">
                    @error('password')
                        <span style="color: #dc2626; font-size: 0.8em; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- パスワード（確認用） -->
                <div style="margin-bottom: 24px;">
                    <label for="password_confirmation"
                        style="display: block; font-size: 0.875em; font-weight: 600; color: #374151; margin-bottom: 6px;">パスワード（確認用）</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                        style="width: 100%; padding: 10px 12px; border: 1px solid {{ $errors->has('password_confirmation') ? '#dc2626' : '#d1d5db' }}; border-radius: 6px; font-size: 0.95em; box-sizing: border-box;">
                </div>

                <!-- 登録ボタン -->
                <button type="submit"
                    style="width: 100%; padding: 12px; background: #2563eb; color: #fff; font-weight: bold; font-size: 0.95em; border: none; border-radius: 6px; cursor: pointer;">
                    アカウントを作成する
                </button>

                <!-- ログインへの案内 -->
                <div style="text-align: center; margin-top: 20px; font-size: 0.875em; color: #6b7280;">
                    すでにアカウントをお持ちの方は
                    <a href="{{ route('login') }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">ログイン</a>
                </div>
            </form>
        </div>
    </div>
@endsection