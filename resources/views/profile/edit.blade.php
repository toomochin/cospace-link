@extends('layouts.app')

@section('content')
    <div style="max-width: 500px; margin: 0 auto;">
        <h2>プロフィール設定</h2>

        @if (session('status'))
            <div style="color: green; margin-bottom: 15px;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="color: red; margin-bottom: 15px;">
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
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">プロフィール画像</label>
                @if ($user->profile_image_path)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('storage/' . $user->profile_image_path) }}" alt="ユーザー画像"
                            style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                    </div>
                @endif
                <input type="file" name="profile_image" accept="image/*">
            </div>

            {{-- ユーザー名 --}}
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">お名前 <span style="color: red;">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    style="width: 100%; padding: 8px;">
            </div>

            <button type="submit" style="background: #333; color: #fff; padding: 10px 20px; border: none; cursor: pointer;">
                更新する
            </button>
        </form>
    </div>
@endsection