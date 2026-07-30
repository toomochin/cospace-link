@extends('layouts.app')

@section('content')
    <div class="user-container">
        <h2>店舗情報編集</h2>
        @if (session('status'))
            <p>{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <ul class=text-error>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <form method="POST" action="{{ route('owner.shop.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <label>アクセス
                <textarea name='access' rows='3'>{{ old('access', $shop->access) }}</textarea>
            </label>
            <label>店舗の特徴
                <textarea name='description' rows='5'>{{ old('description', $shop->description) }}</textarea>
            </label>
            <fieldset>
                <legend>設備タグ</legend>
                @php($selectedAmenities = old('amenities', $shop->amenities ?? []))
                @foreach (['Wi-Fi', '電源', 'Web会議ブース可'] as $amenity)
                    <label>
                        <input type='checkbox' name='amenities[]' value='{{ $amenity }}' @checked(in_array($amenity, $selectedAmenities, true))>
                        {{ $amenity }}
                    </label>
                @endforeach
            </fieldset>
            @if ($shop->image_path)
                <div>
                    <p>現在の店舗画像</p>
                    <img src='{{ Storage::url($shop->image_path) }}' alt='{{ $shop->name }}' style='max-width: 320px; height: auto;'>
                </div>
            @endif
            <input name="name" value="{{ old('name', $shop->name) }}">
            <input name="area_name" value="{{ old('area_name', $shop->area_name) }}">
            <input name="address" value="{{ old('address', $shop->address) }}">
            <input name="opening_hours" value="{{ old('opening_hours', $shop->opening_hours) }}">
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <button type="submit">更新</button>
        </form>
    </div>
@endsection
