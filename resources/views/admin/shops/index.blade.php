@extends('layouts.app')

@section('content')
<div class='admin-card-container admin-list-card'>
    <div class='admin-header-flex'>
        <h2 class='admin-header-title'>加盟店舗管理</h2>
        <div class='admin-actions-group'>
            <a href='{{ route('admin.shops.create') }}' class='btn-admin-action blue'>加盟店舗を登録</a>
            <a href='{{ route('admin.dashboard') }}' class='link-secondary'>&larr; ダッシュボードに戻る</a>
        </div>
    </div>

    @include('admin.shops._errors')

    <div class='table-wrapper'>
        <table class='admin-table admin-shops-table'>
            <thead>
                <tr>
                    <th class='text-center' style='width: 40px;'>ID</th>
                    <th style='width: 140px;'>店舗名</th>
                    <th style='width: 80px;'>エリア</th>
                    <th>店舗管理者</th>
                    <th class='text-center' style='width: 65px;'>施設数</th>
                    <th class='text-center' style='width: 75px;'>掲載状態</th>
                    <th class='text-center' style='width: 130px;'>操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shops as $shop)
                    <tr>
                        <td class='text-center text-muted'>{{ $shop->id }}</td>
                        <td class='cell-truncate' style='font-weight: bold;' title='{{ $shop->name }}'>{{ $shop->name }}</td>
                        <td class='cell-truncate' title='{{ $shop->area_name }}'>{{ $shop->area_name }}</td>
                        <td class='cell-truncate text-muted' title='{{ $shop->owners->first()?->email ?? '未設定' }}'>
                            {{ $shop->owners->first()?->email ?? '未設定' }}
                        </td>
                        <td class='text-center'>{{ $shop->facilities_count }}件</td>
                        <td class='text-center'>
                            @if ($shop->is_active)
                                <span class='status-active'>● 掲載中</span>
                            @else
                                <span class='status-stopped'>● 停止中</span>
                            @endif
                        </td>
                        <td class='text-center'>
                            <div class='shop-table-actions'>
                                <a href='{{ route('admin.shops.edit', $shop) }}' class='btn-toggle edit'>編集</a>
                                <form method='POST' action='{{ route('admin.shops.toggle-status', $shop) }}'>
                                    @csrf
                                    @method('PATCH')
                                    <button type='submit' class='btn-toggle {{ $shop->is_active ? 'stop' : 'activate' }}'>
                                        {{ $shop->is_active ? '掲載停止' : '再掲載' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan='7' class='text-center text-muted' style='padding: 20px;'>加盟店舗が登録されていません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
