@extends('layouts.app')

@section('content')
<div class='admin-card'>
    <div class='admin-header-flex'>
        <h2 class='admin-header-title'>店舗情報編集</h2>
        <a href='{{ route('owner.dashboard') }}' class='link-secondary'>&larr; ダッシュボードに戻る</a>
    </div>

    @if (session('status'))
        <div class='alert-success'>{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <ul class='text-error'>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method='POST' action='{{ route('owner.shop.update') }}' enctype='multipart/form-data'>
        @csrf
        @method('PUT')
        <label>店舗名
            <input name='name' value='{{ old('name', $shop->name) }}' required>
        </label>
        <label>エリア名
            <input name='area_name' value='{{ old('area_name', $shop->area_name) }}' required>
        </label>
        <label>住所
            <input name='address' value='{{ old('address', $shop->address) }}' required>
        </label>
        <label>営業時間
            <input name='opening_hours' value='{{ old('opening_hours', $shop->opening_hours) }}' required>
        </label>
        <label>アクセス
            <textarea name='access' rows='3'>{{ old('access', $shop->access) }}</textarea>
        </label>
        <label>店舗の特徴
            <textarea name='description' rows='5'>{{ old('description', $shop->description) }}</textarea>
        </label>
        <fieldset>
            <legend>設備タグ</legend>
            @php($selectedAmenities = old('amenities', $shop->amenities ?? []))
            @foreach (\App\Support\AmenityNormalizer::SHOP_AMENITIES as $amenity)
                <label>
                    <input type='checkbox' name='amenities[]' value='{{ $amenity }}' @checked(in_array($amenity, $selectedAmenities, true))>
                    {{ $amenity }}
                </label>
            @endforeach
        </fieldset>
        @if ($shop->image_path)
            <div>
                <p class='preview-title'>現在の店舗画像</p>
                <img src='{{ Storage::url($shop->image_path) }}' alt='{{ $shop->name }}' class='preview-image'>
            </div>
        @endif
        <label>店舗画像
            <input type='file' name='image' accept='image/jpeg,image/png,image/webp'>
        </label>
        <button type='submit' class='btn-primary'>更新</button>
    </form>
</div>
@endsection
