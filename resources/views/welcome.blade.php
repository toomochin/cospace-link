@extends('layouts.app')

@section('content')
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="margin-bottom: 20px;">施設一覧</h2>

        @if (session('status'))
            <div style="color: green; margin-bottom: 15px; padding: 10px; background: #e6ffe6; border: 1px solid #b3ffb3;">
                {{ session('status') }}
            </div>
        @endif

        {{-- 検索・絞り込みフォーム --}}
        <form action="{{ route('home') }}" method="GET" style="margin-bottom: 25px; display: flex; gap: 10px;">
            <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="施設名・設備・説明文で検索"
                style="padding: 10px; flex: 1; border: 1px solid #ccc; border-radius: 4px;">

            <select name="type" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">すべての種別</option>
                <option value="meeting_room" {{ request('type') === 'meeting_room' ? 'selected' : '' }}>会議室</option>
                <option value="area" {{ request('type') === 'area' ? 'selected' : '' }}>エリア（席）</option>
            </select>

            <button type="submit"
                style="padding: 10px 20px; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
                検索
            </button>
        </form>

        <!-- 施設タイプ別絞り込みタブ -->
        <div style="display: flex; gap: 10px; margin-bottom: 25px;">
            <a href="{{ route('home', array_merge(request()->query(), ['type' => null])) }}"
                style="padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: bold; font-size: 0.9em; {{ !request('type') ? 'background: #2563eb; color: #fff;' : 'background: #f3f4f6; color: #4b5563;' }}">
                すべて
            </a>

            <a href="{{ route('home', array_merge(request()->query(), ['type' => 'meeting_room'])) }}"
                style="padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: bold; font-size: 0.9em; {{ request('type') === 'meeting_room' ? 'background: #2563eb; color: #fff;' : 'background: #f3f4f6; color: #4b5563;' }}">
                🚪 会議室・個室
            </a>

            <a href="{{ route('home', array_merge(request()->query(), ['type' => 'area'])) }}"
                style="padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: bold; font-size: 0.9em; {{ request('type') === 'area' ? 'background: #2563eb; color: #fff;' : 'background: #f3f4f6; color: #4b5563;' }}">
                🪑 フリーデスク・エリア
            </a>
        </div>

        {{-- 施設カード一覧 --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
            @forelse ($facilities as $facility)
                <div
                    style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background: #fff; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <span
                            style="display: inline-block; padding: 3px 8px; font-size: 0.8em; background: #eee; border-radius: 4px; margin-bottom: 8px;">
                            {{ $facility->type === 'meeting_room' ? '会議室' : 'エリア席' }}
                        </span>
                        <h3 style="margin: 0 0 10px 0; font-size: 1.15em;">{{ $facility->name }}</h3>
                        <p style="color: #666; font-size: 0.9em; margin-bottom: 10px; line-height: 1.4;">
                            {{ $facility->description }}
                        </p>

                        @if ($facility->equipment)
                            <p
                                style="font-size: 0.85em; color: #444; background: #f8f9fa; padding: 6px; border-radius: 4px; margin-bottom: 10px;">
                                <strong>設備:</strong> {{ $facility->equipment }}
                            </p>
                        @endif
                    </div>

                    <div style="border-top: 1px solid #eee; margin-top: 10px; padding-top: 10px;">
                        <p style="margin: 0 0 5px 0; font-size: 0.9em; color: #555;">
                            定員: {{ $facility->capacity }}名
                        </p>
                        <p style="margin: 0; font-weight: bold; color: #007bff; font-size: 1.1em;">
                            ¥{{ number_format($facility->price_per_30min) }} <span
                                style="font-size: 0.8em; color: #666; font-weight: normal;">/ 30分</span>
                        </p>
                    </div>
                    {{-- 詳細画面へのリンクボタン --}}
                    <a href="{{ route('facilities.show', $facility->id) }}"
                        style="padding: 8px 14px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.9em;">
                        詳細・予約
                    </a>
                </div>
            @empty
                <p style="grid-column: 1 / -1; color: #666;">該当する施設が見つかりませんでした。</p>
            @endforelse
        </div>
    </div>
@endsection