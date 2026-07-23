@extends('layouts.app')

@section('content')
<div class="admin-card-container">
    {{-- ヘッダーエリア --}}
    <div class="admin-header-flex">
        <h2 class="admin-header-title">会員管理</h2>
        <a href="{{ route('admin.dashboard') }}" class="link-secondary">&larr; ダッシュボードに戻る</a>
    </div>

    {{-- フラッシュメッセージ --}}
    @if (session('status'))
        <div class="alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- 会員一覧テーブル --}}
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">ID</th>
                    <th style="width: 110px;">氏名</th>
                    <th>メールアドレス</th>
                    <th style="width: 65px;" class="text-center">権限</th>
                    <th style="width: 70px;" class="text-center">状態</th>
                    <th style="width: 85px;" class="text-center">登録日</th>
                    <th style="width: 90px;" class="text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="text-center text-muted">{{ $user->id }}</td>
                        <td class="cell-truncate" style="font-weight: bold;" title="{{ $user->name }}">
                            {{ $user->name }}
                        </td>
                        <td class="cell-truncate text-muted" title="{{ $user->email }}">
                            {{ $user->email }}
                        </td>
                        <td class="text-center">
                            @if ($user->is_admin)
                                <span class="badge badge-admin">管理者</span>
                            @else
                                <span class="badge badge-user">一般</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($user->is_active)
                                <span class="status-active">● 有効</span>
                            @else
                                <span class="status-stopped">● 停止</span>
                            @endif
                        </td>
                        <td class="text-center text-muted" style="font-size: 0.8rem;">
                            {{ $user->created_at->format('Y/m/d') }}
                        </td>
                        <td class="text-center">
                            @if (Auth::id() === $user->id)
                                <span class="text-muted" style="font-size: 0.75rem;">(自分)</span>
                            @else
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                        class="btn-toggle {{ $user->is_active ? 'stop' : 'activate' }}"
                                        onclick="return confirm('ユーザー「{{ $user->name }}」のステータスを変更しますか？')">
                                        {{ $user->is_active ? '利用停止' : '有効化' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding: 20px;">会員が登録されていません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ページネーション --}}
    <div style="margin-top: 15px;">
        {{ $users->links() }}
    </div>
</div>
@endsection