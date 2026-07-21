@extends('layouts.app')

@section('content')
    <div style="max-width: 800px; margin: 0 auto;">
        <h2>マイページ（予約履歴）</h2>

        @if (session('status'))
            <div
                style="color: green; margin-bottom: 15px; padding: 10px; background: #e6ffe6; border: 1px solid #b3ffb3; border-radius: 4px;">
                {{ session('status') }}
            </div>
        @endif

        @forelse ($reservations as $reservation)
            <div
                style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span
                        style="display: inline-block; padding: 2px 8px; font-size: 0.8em; border-radius: 4px; margin-bottom: 8px; 
                                    {{ $reservation->status === 'confirmed' ? 'background: #e6f4ea; color: #137333;' : 'background: #fce8e6; color: #c5221f;' }}">
                        {{ $reservation->status === 'confirmed' ? '予約確定' : 'キャンセル済み' }}
                    </span>

                    {{-- reservable リレーションから施設名を取得 --}}
                    <h3 style="margin: 0 0 8px 0; font-size: 1.15em;">
                        {{ $reservation->reservable->name ?? '施設情報なし' }}
                    </h3>

                    {{-- start_time / end_time カラムを表示 --}}
                    <p style="margin: 0; color: #555; font-size: 0.9em;">
                        <strong>利用日時:</strong>
                        {{ \Carbon\Carbon::parse($reservation->start_time)->format('Y年m月d日(D) H:i') }} 〜
                        {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
                    </p>
                </div>

                <div>
                    {{-- 未来の予約かつ予約確定状態の場合のみキャンセルボタンを表示 --}}
                    @if ($reservation->status === 'confirmed' && \Carbon\Carbon::parse($reservation->start_time)->isFuture())
                        <form action="{{ route('reservations.destroy', $reservation->id) }}" method="POST"
                            onsubmit="return confirm('本当にこの予約をキャンセルしますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                style="padding: 8px 12px; background: #dc3545; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em;">
                                キャンセル
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p style="color: #666;">現在、予約履歴はありません。</p>
        @endforelse

        <div style="margin-top: 20px;">
            <a href="{{ route('home') }}" style="color: #007bff; text-decoration: none;">&larr; 施設一覧に戻る</a>
        </div>
    </div>
@endsection