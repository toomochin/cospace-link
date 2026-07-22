@extends('layouts.app')

@section('content')
    <div class="admin-container">
        {{-- ヘッダーエリア --}}
        <div class="admin-header">
            <h2 class="admin-header-title">管理画面：施設一覧</h2>
            <div class="admin-header-actions">
                <a href="{{ route('admin.facilities.create') }}" class="btn-primary">
                    ＋ 新規施設登録
                </a>
                <a href="{{ route('admin.reservations.index') }}" class="btn-secondary">
                    全予約一覧を見る
                </a>
            </div>
        </div>

        {{-- ステータスメッセージ --}}
        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

        {{-- テーブル一覧 --}}
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th>施設名</th>
                    <th>30分単価</th>
                    <th>定員</th>
                    <th class="col-status">状態</th>
                    <th class="col-actions">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($facilities as $facility)
                    <tr>
                        <td class="col-id">{{ $facility->id }}</td>
                        <td class="font-bold">{{ $facility->name }}</td>
                        <td>¥{{ number_format($facility->price_per_30min) }}</td>
                        <td>{{ $facility->capacity }}名</td>
                        <td class="col-status">
                            <span class="badge-status {{ $facility->is_active ? 'active' : 'inactive' }}">
                                {{ $facility->is_active ? '公開中' : '非公開' }}
                            </span>
                        </td>
                        <td class="col-actions">
                            <a href="{{ route('admin.facilities.edit', $facility->id) }}" class="link-action">
                                編集
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="col-empty">登録されている施設はありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection