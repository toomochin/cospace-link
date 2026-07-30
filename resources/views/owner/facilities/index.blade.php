@extends('layouts.app')

@section('content')
<div class='admin-card-container admin-list-card'>
    <div class='admin-header-flex'>
        <h2 class='admin-header-title'>自店舗の施設管理</h2>
        <div class='admin-actions-group'>
            <a href='{{ route('owner.facilities.create') }}' class='btn-admin-action blue'>施設を登録</a>
            <a href='{{ route('owner.dashboard') }}' class='link-secondary'>&larr; ダッシュボードに戻る</a>
        </div>
    </div>

    @if (session('status'))
        <div class='alert-success'>{{ session('status') }}</div>
    @endif

    <div class='table-wrapper'>
        <table class='admin-table owner-facilities-table'>
            <thead>
                <tr>
                    <th class='text-center' style='width: 50px;'>ID</th>
                    <th>施設名</th>
                    <th style='width: 100px;'>種別</th>
                    <th class='text-center' style='width: 110px;'>30分料金</th>
                    <th class='text-center' style='width: 75px;'>定員</th>
                    <th class='text-center' style='width: 80px;'>公開状態</th>
                    <th class='text-center' style='width: 130px;'>操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($facilities as $facility)
                    <tr>
                        <td class='text-center text-muted'>{{ $facility->id }}</td>
                        <td class='cell-truncate font-bold' title='{{ $facility->name }}'>{{ $facility->name }}</td>
                        <td>{{ $facility->type === 'meeting_room' ? '会議室' : 'エリア席' }}</td>
                        <td class='text-center'>&yen;{{ number_format($facility->price_per_30min) }}</td>
                        <td class='text-center'>{{ $facility->capacity }}名</td>
                        <td class='text-center'>
                            <span class='{{ $facility->is_active ? 'status-active' : 'status-stopped' }}'>
                                ● {{ $facility->is_active ? '公開中' : '停止中' }}
                            </span>
                        </td>
                        <td class='text-center'>
                            <div class='shop-table-actions'>
                                <a href='{{ route('owner.facilities.edit', $facility) }}' class='btn-toggle edit'>編集</a>
                                <form method='POST' action='{{ route('owner.facilities.destroy', $facility) }}'>
                                    @csrf
                                    @method('DELETE')
                                    <button type='submit' class='btn-toggle stop'>削除</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan='7' class='col-empty'>施設が登録されていません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
