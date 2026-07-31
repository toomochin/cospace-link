@extends('layouts.app')

@section('content')
    @php
        $searchParams = request()->only(['area', 'keyword', 'date', 'start_time', 'end_time', 'type', 'amenities']);
    @endphp
    <div class="user-container">
        <h2 class="user-title" style="margin-bottom: 20px;">施設横断検索</h2>

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('home') }}" method="GET" class="filter-form">
            <input type="text" name="area" value="{{ request('area') }}" placeholder="エリア（渋谷・梅田など）"
                class="form-control">
            <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="店舗名・施設名・特徴"
                class="form-control filter-input-keyword">
            <input type="date" name="date" value="{{ request('date') }}" min="{{ now()->format('Y-m-d') }}"
                class="form-control">
            <input type="time" name="start_time" value="{{ request('start_time') }}" step="1800" class="form-control">
            <input type="time" name="end_time" value="{{ request('end_time') }}" step="1800" class="form-control">

            <select name="type" class="form-control">
                <option value="">すべての種別</option>
                <option value="meeting_room" @selected(request('type') === 'meeting_room')>会議室</option>
                <option value="area" @selected(request('type') === 'area')>エリア（席）</option>
            </select>

            @foreach (\App\Support\AmenityNormalizer::SEARCHABLE_AMENITIES as $amenity)
                <label>
                    <input type="checkbox" name="amenities[]" value="{{ $amenity }}"
                        @checked(in_array($amenity, request('amenities', []), true))>
                    {{ $amenity }}
                </label>
            @endforeach

            <button type="submit" class="btn-user-dark" style="width: auto; padding: 10px 20px;">検索</button>
        </form>

        <div class="facility-grid">
            @forelse ($facilities as $facility)
                <div class="facility-card">
                    <div class="facility-card-body facility-card-inner">
                        <div>
                            <span class="user-badge user-badge-gray">
                                {{ $facility->type === 'meeting_room' ? '会議室' : 'エリア席' }}
                            </span>
                            <p style="margin: 8px 0 4px; color: #4b5563;">
                                {{ $facility->shop->name }} ／ {{ $facility->shop->area_name }}
                            </p>
                            <h3 style="margin: 0 0 10px; font-size: 1.15em;">{{ $facility->name }}</h3>
                            <p style="color: #6b7280;">{{ $facility->description }}</p>
                            @if ($facility->equipment)
                                <div class="facility-equipment-box"><strong>設備:</strong> {{ $facility->equipment }}</div>
                            @endif
                        </div>
                        <div>
                            <p>定員: {{ $facility->capacity }}名</p>
                            <p class="facility-card-price">
                                ¥{{ number_format($facility->price_per_30min) }}
                                <span class="facility-meta-unit">/ 30分</span>
                            </p>
                            <a href="{{ route('facilities.show', ['facility' => $facility->id] + $searchParams) }}" class="btn-user-primary"
                                style="display: block; text-align: center;">詳細・予約</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-empty" style="grid-column: 1 / -1;">条件に合う空き施設が見つかりませんでした。</p>
            @endforelse
        </div>
    </div>
@endsection
