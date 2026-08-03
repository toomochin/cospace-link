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
                <a href="{{ route('admin.dashboard') }}" class="link-secondary" style="margin-right: 10px;">&larr; ダッシュボードに戻る</a>
            </div>
        </div>

        {{-- ステータスメッセージ --}}
        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif
        <form method='GET' action='{{ route('admin.facilities.index') }}' class='reservation-filter-form'>
            <label class='reservation-filter-field'>店舗
                <select name='shop_id'>
                    <option value=''>全店舗</option>
                    @foreach ($shops as $shop)
                        <option value='{{ $shop->id }}' @selected((string) ($filters['shop_id'] ?? '') === (string) $shop->id)>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class='reservation-filter-field'>エリア
                <select name='area_name'>
                    <option value=''>全エリア</option>
                    @foreach ($areas as $area)
                        <option value='{{ $area }}' @selected(($filters['area_name'] ?? '') === $area)>{{ $area }}</option>
                    @endforeach
                </select>
            </label>
            <label class='reservation-filter-field'>施設種別
                <select name='type'>
                    <option value=''>すべて</option>
                    <option value='meeting_room' @selected(($filters['type'] ?? '') === 'meeting_room')>個室・会議室</option>
                    <option value='area' @selected(($filters['type'] ?? '') === 'area')>エリア席</option>
                </select>
            </label>
            <label class='reservation-filter-field'>公開状態
                <select name='status'>
                    <option value=''>すべて</option>
                    <option value='active' @selected(($filters['status'] ?? '') === 'active')>公開中</option>
                    <option value='inactive' @selected(($filters['status'] ?? '') === 'inactive')>非公開</option>
                </select>
            </label>
            <label class='reservation-filter-field'>並び順
                <select name='order'>
                    <option value='id_asc' @selected(($filters['order'] ?? 'id_asc') === 'id_asc')>登録順</option>
                    <option value='id_desc' @selected(($filters['order'] ?? '') === 'id_desc')>新しい登録順</option>
                    <option value='shop_asc' @selected(($filters['order'] ?? '') === 'shop_asc')>店舗名順</option>
                    <option value='area_asc' @selected(($filters['order'] ?? '') === 'area_asc')>エリア順</option>
                    <option value='name_asc' @selected(($filters['order'] ?? '') === 'name_asc')>施設名 昇順</option>
                    <option value='name_desc' @selected(($filters['order'] ?? '') === 'name_desc')>施設名 降順</option>
                    <option value='price_asc' @selected(($filters['order'] ?? '') === 'price_asc')>料金が安い順</option>
                    <option value='price_desc' @selected(($filters['order'] ?? '') === 'price_desc')>料金が高い順</option>
                    <option value='capacity_asc' @selected(($filters['order'] ?? '') === 'capacity_asc')>定員が少ない順</option>
                    <option value='capacity_desc' @selected(($filters['order'] ?? '') === 'capacity_desc')>定員が多い順</option>
                    <option value='status_desc' @selected(($filters['order'] ?? '') === 'status_desc')>公開中を先に表示</option>
                </select>
            </label>
            <div class='reservation-filter-actions'>
                <button type='submit' class='btn-primary-sm'>絞り込む</button>
                <a href='{{ route('admin.facilities.index') }}' class='btn-cancel'>条件をクリア</a>
            </div>
        </form>


        {{-- テーブル一覧 --}}
        <div class='table-wrapper'>
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th>店舗名</th>
                    <th>エリア</th>
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
                        <td>{{ $facility->shop?->name ?? '店舗情報なし' }}</td>
                        <td>{{ $facility->shop?->area_name ?? '未設定' }}</td>
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
                        <td colspan="8" class="col-empty">登録されている施設はありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
