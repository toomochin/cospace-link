<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Co-Space Link') }}</title>

    {{-- admin.css と user.css の読み込み --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=1.0">
    <link rel="stylesheet" href="{{ asset('css/user.css') }}?v=1.0">
</head>

<body>

    <!-- 共通ヘッダー -->
    <header class="site-header">
        <div class="site-header-inner">

            <!-- ロゴ / アプリ名 -->
            <a href="{{ route('home') }}" class="site-logo">
                Co-Space Link
            </a>

            <!-- ナビゲーションリンク -->
            <nav class="site-nav">
                <a href="{{ route('home') }}" class="nav-link">
                    施設一覧
                </a>

                @auth
                    {{-- ログイン中の表示 --}}
                    <a href="{{ route('reservations.index') }}" class="nav-link">
                        マイページ（予約履歴）
                    </a>

                                        {{-- 管理者ユーザーだけに表示 --}}
                    @if(auth()->user()->is_admin)
                        {{-- ★ ここを admin.facilities.index から admin.dashboard に変更 --}}
                        <a href="{{ route('admin.dashboard') }}" class="nav-link-admin">
                            ⚙️ 管理画面
                        </a>
                    @endif

                    <div class="nav-user-group">
                        {{-- プロフィール編集へのリンク（画像アイコン ＋ ユーザー名） --}}
                        <a href="{{ route('profile.edit') }}" class="nav-profile-link" title="プロフィール編集">
                            @if (Auth::user()->profile_image_path)
                                <img src="{{ asset('storage/' . Auth::user()->profile_image_path) }}" alt="アイコン" class="nav-avatar-img">
                            @else
                                <span style="font-size: 1.1em;">👤</span>
                            @endif

                            <span style="font-weight: 500;">{{ Auth::user()->name }} さん</span>
                        </a>

                        <!-- ログアウトフォーム -->
                        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn-logout">
                                ログアウト
                            </button>
                        </form>
                    </div>
                @else
                    {{-- 未ログインの表示 --}}
                    <a href="{{ route('login') }}" class="nav-link">
                        ログイン
                    </a>
                    <a href="{{ route('register') }}" class="btn-user-primary" style="padding: 6px 12px; font-size: 0.85em;">
                        会員登録
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="main-content">
        @yield('content')
    </main>

</body>

</html>