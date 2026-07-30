@extends('layouts.app')

@section('content')
    <div class="user-container">
        <h2>加盟店舗編集</h2>
        @include('admin.shops._errors')
        <form method="POST" action="{{ route('admin.shops.update', $shop) }}">
            @csrf
            @method('PUT')
            <label>店舗名
                <input name="name" value="{{ old('name', $shop->name) }}" required>
                @error('name') <span class="text-error">{{ $message }}</span> @enderror
            </label>
            <label>エリア
                <input name="area_name" value="{{ old('area_name', $shop->area_name) }}" required>
                @error('area_name') <span class="text-error">{{ $message }}</span> @enderror
            </label>
            <label>管理者メール
                <input type="email" name="owner_email" value="{{ old('owner_email', $shop->owners->first()?->email) }}" required>
                @error('owner_email') <span class="text-error">{{ $message }}</span> @enderror
            </label>
            <button type="submit">更新</button>
        </form>
    </div>
@endsection
