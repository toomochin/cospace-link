@extends('layouts.app')

@section('content')
    <div style="max-width: 500px; margin: 0 auto; padding: 20px 0;">
        <h2 style="margin-bottom: 20px;">プロフィール設定</h2>

        {{-- 成功メッセージ --}}
        @if (session('status'))
            <div style="background: #d1e7dd; color: #0f5132; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                {{ session('status') }}
            </div>
        @endif

        {{-- エラー概要（全体表示） --}}
        @if ($errors->any())
            <div
                style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- プロフィール画像 --}}
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">プロフィール画像</label>
                @if ($user->profile_image_path)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('storage/' . $user->profile_image_path) }}" alt="ユーザー画像"
                            style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;">
                    </div>
                @endif
                <input type="file" name="profile_image" accept="image/*">
                @error('profile_image')
                    <span style="color: #dc2626; font-size: 0.85em; display: block; margin-top: 4px;">{{ $message }}</span>
                @enderror
            </div>

            {{-- ユーザー名 --}}
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">お名前 <span
                        style="color: red;">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                    style="width: 100%; padding: 10px; border: 1px solid {{ $errors->has('name') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                @error('name')
                    <span style="color: #dc2626; font-size: 0.85em; display: block; margin-top: 4px;">{{ $message }}</span>
                @enderror
            </div>

            {{-- メールアドレス --}}
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">メールアドレス <span
                        style="color: red;">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    style="width: 100%; padding: 10px; border: 1px solid {{ $errors->has('email') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                <small style="color: #666; font-size: 0.8em; display: block; margin-top: 4px;">
                    ※メールアドレスを変更すると再認証が必要になります。
                </small>
                @error('email')
                    <span style="color: #dc2626; font-size: 0.85em; display: block; margin-top: 4px;">{{ $message }}</span>
                @enderror
            </div>

            <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">

            {{-- パスワード変更（任意） --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">新しいパスワード（変更する場合のみ）</label>
                <input type="password" name="password" placeholder="8文字以上で入力"
                    style="width: 100%; padding: 10px; border: 1px solid {{ $errors->has('password') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                @error('password')
                    <span style="color: #dc2626; font-size: 0.85em; display: block; margin-top: 4px;">{{ $message }}</span>
                @enderror
            </div>

            {{-- 確認用パスワード --}}
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">新しいパスワード（確認用）</label>
                <input type="password" name="password_confirmation" placeholder="もう一度入力してください"
                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <button type="submit"
                style="width: 100%; background: #333; color: #fff; padding: 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                更新する
            </button>
        </form>
    </div>
@endsection