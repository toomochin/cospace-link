@extends('layouts.app')

@section('content')
    <div class="user-container">
        <h2>加盟店舗管理</h2>
        @include('admin.shops._errors')
        <a href="{{ route('admin.shops.create') }}">加盟店舗を登録</a>
        @foreach ($shops as $shop)
            <div>
                <strong>{{ $shop->name }}</strong> / {{ $shop->area_name }}
                / {{ $shop->is_active ? '掲載中' : '停止中' }}
                / 施設 {{ $shop->facilities_count }}件
                / 管理者 {{ $shop->owners->first()?->email ?? '未設定' }}
                <a href="{{ route('admin.shops.edit', $shop) }}">編集</a>
                <form method="POST" action="{{ route('admin.shops.toggle-status', $shop) }}" style="display:inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="{{ $shop->is_active ? 'btn-danger' : 'btn-primary-sm' }}">
                        {{ $shop->is_active ? '掲載停止' : '再掲載' }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
