@extends('layouts.app')

@section('content')
    <div style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 20px 0;">
        <div
            style="width: 100%; max-width: 400px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;">

            <h2 style="margin: 0 0 24px 0; font-size: 1.5em; text-align: center; color: #1f2937;">ログイン</h2>

            {{-- エラーメッセージ表示 --}}
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

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- メールアドレス -->
                <div style="margin-bottom: 18px;">
                    <label for="email"
                        style="display: block; font-size: 0.875em; font-weight: 600; color: #374151; margin-bottom: 6px;">メールアドレス</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95em; box-sizing: border-box;">
                </div>

                <!-- パスワード -->
                <div style="margin-bottom: 24px;">
                    <label for="password"
                        style="display: block; font-size: 0.875em; font-weight: 600; color: #374151; margin-bottom: 6px;">パスワード</label>
                    <input id="password" type="password" name="password" required
                        style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95em; box-sizing: border-box;">
                </div>

                <!-- ログインボタン -->
                <button type="submit"
                    style="width: 100%; padding: 12px; background: #2563eb; color: #fff; font-weight: bold; font-size: 0.95em; border: none; border-radius: 6px; cursor: pointer; transition: background 0.2s;">
                    ログイン
                </button>

                <!-- 登録への案内 -->
                <div style="text-align: center; margin-top: 20px; font-size: 0.875em; color: #6b7280;">
                    アカウントをお持ちでない方は
                    <a href="{{ route('register') }}"
                        style="color: #2563eb; text-decoration: none; font-weight: 600;">会員登録</a>
                </div>
            </form>
        </div>
    </div>
@endsection