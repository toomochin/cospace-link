@extends('layouts.app')

@section('content')
    <div class="user-container">
        <h2 class="user-title" style="margin-bottom: 20px;">施設一覧</h2>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

        {{-- 検索・絞り込みフォーム --}}
        <form action="{{ route('home') }}" method="GET" class="filter-form">
            <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="施設名・設備・説明文で検索"
                class="form-control filter-input-keyword">

            <select name="type" class="form-control" style="width: auto;">
                <option value="">すべての種別</option>
                <option value="meeting_room" {{ request('type') === 'meeting_room' ? 'selected' : '' }}>会議室</option>
                <option value="area" {{ request('type') === 'area' ? 'selected' : '' }}>エリア（席）</option>
            </select>

            <button type="submit" class="btn-user-dark" style="width: auto; padding: 10px 20px;">
                検索
            </button>
        </form>

        <!-- 施設タイプ別絞り込みタブ -->
        <div class="filter-tab-container">
            <a href="{{ route('home', array_merge(request()->query(), ['type' => null])) }}"
                class="filter-tab-item {{ !request('type') ? 'active' : '' }}">
                すべて
            </a>

            <a href="{{ route('home', array_merge(request()->query(), ['type' => 'meeting_room'])) }}"
                class="filter-tab-item {{ request('type') === 'meeting_room' ? 'active' : '' }}">
                🚪 会議室・個室
            </a>

            <a href="{{ route('home', array_merge(request()->query(), ['type' => 'area'])) }}"
                class="filter-tab-item {{ request('type') === 'area' ? 'active' : '' }}">
                🪑 フリーデスク・エリア
            </a>
        </div>

        {{-- 施設カード一覧 --}}
        <div class="facility-grid">
            @forelse ($facilities as $facility)
                <div class="facility-card">
                    <div class="facility-card-body facility-card-inner">
                        <div>
                            <span class="user-badge user-badge-gray" style="margin-bottom: 8px;">
                                {{ $facility->type === 'meeting_room' ? '会議室' : 'エリア席' }}
                            </span>
                            <h3 style="margin: 0 0 10px 0; font-size: 1.15em; color: #1f2937;">{{ $facility->name }}</h3>
                            <p style="color: #6b7280; font-size: 0.9em; margin-bottom: 10px; line-height: 1.4;">
                                {{ $facility->description }}
                            </p>

                            @if ($facility->equipment)
                                <div class="facility-equipment-box" style="margin-top: 8px; font-size: 0.85em;">
                                    <strong>設備:</strong> {{ $facility->equipment }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <div class="facility-card-footer">
                                <p style="margin: 0 0 5px 0; font-size: 0.9em; color: #4b5563;">
                                    定員: {{ $facility->capacity }}名
                                </p>
                                <p class="facility-card-price">
                                    ¥{{ number_format($facility->price_per_30min) }}
                                    <span class="facility-meta-unit">/ 30分</span>
                                </p>
                            </div>

                            {{-- 詳細画面へのリンクボタン --}}
                            <a href="{{ route('facilities.show', $facility->id) }}" class="btn-user-primary" style="display: block; text-align: center;">
                                詳細・予約
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-empty" style="grid-column: 1 / -1;">該当する施設が見つかりませんでした。</p>
            @endforelse
        </div>
    </div>
@endsection