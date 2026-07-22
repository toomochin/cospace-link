<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Co-Space Link') }}</title>
    <!-- スタイル等の読み込み -->
</head>

<body style="margin: 0; background-color: #f8f9fa; font-family: sans-serif; color: #333;">

    <!-- 共通ヘッダー -->
    <header style="background: #ffffff; border-bottom: 1px solid #e5e7eb; padding: 0 20px;">
        <div
            style="max-width: 1000px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; height: 60px;">

            <!-- ロゴ / アプリ名 -->
            <a href="{{ route('home') }}"
                style="font-size: 1.25em; font-weight: bold; color: #1f2937; text-decoration: none;">
                Co-Space Link
            </a>

            <!-- ナビゲーションリンク -->
            <nav style="display: flex; gap: 20px; align-items: center;">
                <a href="{{ route('home') }}" style="color: #4b5563; text-decoration: none; font-size: 0.95em;">
                    施設一覧
                </a>

                @auth
                    {{-- 管理者ユーザーだけに表示 --}}
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.facilities.index') }}"
                            style="margin-right: 15px; color: #dc2626; font-weight: bold; text-decoration: none;">
                            ⚙️ 管理画面
                        </a>
                    @endif

                    {{-- ログイン中の表示 --}}
                    <a href="{{ route('reservations.index') }}"
                        style="color: #4b5563; text-decoration: none; font-size: 0.95em;">
                        マイページ（予約履歴）
                    </a>

                    <div
                        style="display: flex; align-items: center; gap: 10px; margin-left: 10px; border-left: 1px solid #e5e7eb; padding-left: 15px;">
                        {{-- プロフィール編集へのリンク（画像アイコン ＋ ユーザー名） --}}
                        <a href="{{ route('profile.edit') }}"
                            style="display: flex; align-items: center; gap: 6px; text-decoration: none; color: #4b5563; font-size: 0.85em;"
                            title="プロフィール編集">

                            @if (Auth::user()->profile_image_path)
                                <img src="{{ asset('storage/' . Auth::user()->profile_image_path) }}" alt="アイコン"
                                    style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; border: 1px solid #d1d5db;">
                            @else
                                <span style="font-size: 1.1em;">👤</span>
                            @endif

                            <span style="font-weight: 500;">{{ Auth::user()->name }} さん</span>
                        </a>

                        <!-- ログアウトフォーム -->
                        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit"
                                style="background: none; border: none; color: #dc2626; font-size: 0.85em; cursor: pointer; padding: 0; margin-left: 5px;">
                                ログアウト
                            </button>
                        </form>
                    </div>
                @else
                    {{-- 未ログインの表示 --}}
                    <a href="{{ route('login') }}" style="color: #4b5563; text-decoration: none; font-size: 0.95em;">
                        ログイン
                    </a>
                    <a href="{{ route('register') }}"
                        style="padding: 6px 12px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.85em;">
                        会員登録
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main style="padding: 30px 20px;">
        @yield('content')
    </main>

</body>

</html>