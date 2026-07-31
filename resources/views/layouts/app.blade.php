<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Co-Space Link') }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=1.0">
    <link rel="stylesheet" href="{{ asset('css/user.css') }}?v=1.0">
    <link rel="stylesheet" href="{{ asset('css/navigation.css') }}?v=1.0">
    <link rel='stylesheet' href='{{ asset('css/responsive.css') }}?v=1.0'>
</head>

<body>
    <header class="site-header">
        <div class="site-header-inner">
            <a href="{{ route('home') }}" class="site-logo">Co-Space Link</a>

            <nav class="site-nav" aria-label="メインナビゲーション">
                <a href="{{ route('home') }}" class="nav-button {{ request()->routeIs('home') ? 'active' : '' }}">
                    施設検索
                </a>

                @auth
                    @if (auth()->user()->isSystemAdmin())
                        <details class="nav-dropdown">
                            <summary class="nav-button nav-button-admin {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                                管理メニュー <span aria-hidden="true">▾</span>
                            </summary>
                            <div class="nav-dropdown-menu">
                                <a href="{{ route('admin.dashboard') }}" class="nav-dropdown-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">ダッシュボード</a>
                                <a href="{{ route('admin.shops.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.shops.*') ? 'active' : '' }}">加盟店舗</a>
                                <a href="{{ route('admin.facilities.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.facilities.*') ? 'active' : '' }}">全施設</a>
                                <a href="{{ route('admin.reservations.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.reservations.*') ? 'active' : '' }}">全予約</a>
                                <a href="{{ route('admin.users.index') }}" class="nav-dropdown-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">会員</a>
                            </div>
                        </details>
                    @elseif (auth()->user()->isShopOwner())
                        <details class="nav-dropdown">
                            <summary class="nav-button nav-button-owner {{ request()->routeIs('owner.*') ? 'active' : '' }}">
                                店舗管理 <span aria-hidden="true">▾</span>
                            </summary>
                            <div class="nav-dropdown-menu">
                                <a href="{{ route('owner.dashboard') }}" class="nav-dropdown-item {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">ダッシュボード</a>
                                <a href="{{ route('owner.facilities.index') }}" class="nav-dropdown-item {{ request()->routeIs('owner.facilities.*') ? 'active' : '' }}">施設管理</a>
                                <a href="{{ route('owner.reservations.index') }}" class="nav-dropdown-item {{ request()->routeIs('owner.reservations.*') ? 'active' : '' }}">予約・売上</a>
                                <a href="{{ route('owner.shop.edit') }}" class="nav-dropdown-item {{ request()->routeIs('owner.shop.*') ? 'active' : '' }}">店舗情報</a>
                            </div>
                        </details>
                    @else
                        <a href="{{ route('reservations.index') }}"
                            class="nav-button {{ request()->routeIs('reservations.index') ? 'active' : '' }}">
                            予約履歴
                        </a>
                    @endif

                    <details class="nav-dropdown nav-account">
                        <summary class="nav-account-trigger">
                            @if (auth()->user()->profile_image_path)
                                <img src="{{ asset('storage/'.auth()->user()->profile_image_path) }}"
                                    alt="" class="nav-avatar-img">
                            @else
                                <span aria-hidden="true">👤</span>
                            @endif
                            <span class="nav-account-name">{{ auth()->user()->name }}</span>
                            <span aria-hidden="true">▾</span>
                        </summary>
                        <div class="nav-dropdown-menu nav-dropdown-menu-right">
                            <a href="{{ route('profile.edit') }}" class="nav-dropdown-item">プロフィール編集</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-dropdown-item nav-dropdown-logout">ログアウト</button>
                            </form>
                        </div>
                    </details>
                @else
                    <a href="{{ route('login') }}" class="nav-button">ログイン</a>
                    <a href="{{ route('register') }}" class="btn-user-primary">会員登録</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="main-content">
        @yield('content')
    </main>
</body>

</html>
