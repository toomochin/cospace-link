@extends('layouts.app')

@section('content')
    <div class="user-container" style="max-width: 500px; padding-top: 20px; padding-bottom: 40px;">
        <h2 class="user-title" style="margin-bottom: 20px;">プロフィール設定</h2>

        {{-- 成功メッセージ --}}
        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

        {{-- エラー概要（全体表示） --}}
        @if ($errors->any())
            <div class="alert-danger">
                <ul>
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
            <div class="form-group">
                <label class="form-label">プロフィール画像</label>
                @if ($user->profile_image_path)
                    <div>
                        <img src="{{ asset('storage/' . $user->profile_image_path) }}" alt="ユーザー画像" class="avatar-preview">
                    </div>
                @endif
                <input type="file" name="profile_image" accept="image/*" class="form-control">
                @error('profile_image')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- ユーザー名 --}}
            <div class="form-group">
                <label class="form-label">
                    お名前 <span class="required-mark">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                    class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- メールアドレス --}}
            <div class="form-group">
                <label class="form-label">
                    メールアドレス <span class="required-mark">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}">
                <p class="form-help">
                    ※メールアドレスを変更すると再認証が必要になります。
                </p>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <hr class="form-divider">

            {{-- パスワード変更（任意） --}}
            <div class="form-group">
                <label class="form-label">新しいパスワード（変更する場合のみ）</label>
                <input type="password" name="password" placeholder="8文字以上で入力"
                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}">
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 確認用パスワード --}}
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label">新しいパスワード（確認用）</label>
                <input type="password" name="password_confirmation" placeholder="もう一度入力してください"
                    class="form-control">
            </div>

            <button type="submit" class="btn-user-dark">
                更新する
            </button>
        </form>
    </div>
@endsection