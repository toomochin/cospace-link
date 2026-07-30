@extends('layouts.app')

@section('content')
    <div class="user-container">
        <h2>自店舗の施設管理</h2>
        <a href="{{ route('owner.facilities.create') }}">施設を登録</a>

        @foreach ($facilities as $facility)
            <div>
                {{ $facility->name }} /
                ¥{{ number_format($facility->price_per_30min) }} / 30分 /
                {{ $facility->is_active ? '公開中' : '停止中' }}
                <a href="{{ route('owner.facilities.edit', $facility) }}">編集</a>
                <form method="POST" action="{{ route('owner.facilities.destroy', $facility) }}" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit">削除</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
