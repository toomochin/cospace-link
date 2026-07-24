# CoSpace Link（コワーキングスペース・施設予約システム）

Laravelで作成したコワーキングスペースおよび施設予約アプリケーションです。空き状況の確認からオンライン決済（Stripe）/現地払いでの予約、マイページでの予約管理、さらに管理者による施設・予約・会員の一括管理機能を利用できます。

## 主な機能

- ユーザー登録・ログイン・メール認証
- ログイン時のロール別自動リダイレクト（一般ユーザー: トップ画面 / 管理者: ダッシュボード）
- 施設一覧・詳細表示および空き状況カレンダーの閲覧（30分単位での重複チェック）
- 予約手続き（利用日時の指定、現地払いおよびStripeによるクレジットカード決済対応）
- 予約確定・予約キャンセル時の自動メール通知機能
- マイページで予約履歴の確認およびキャンセル処理
- 管理者ダッシュボード（全予約の確認、代理予約の作成、施設情報のCRUD・画像管理、会員の有効/無効切り替え）

## 使用技術

- PHP 8.x / Laravel 11.x
- MySQL 8.0
- Nginx 1.21
- Laravel Fortify
- Stripe API (PHP SDK)
- Docker Compose / Laravel Sail
- Mailpit（開発用メール受信）

## 環境構築

Docker ビルド

1. git clone git@github.com:toomochin/cospace-link.git
2. cd cospace-link
3. ./vendor/bin/sail up -d --build

Laravel 環境構築

1. ./vendor/bin/sail exec laravel.test bash
2. composer install
3. cp .env.example .env
4. .env ファイルの変更

```
DB_HOST=mysql
DB_DATABASE=cospace_link
DB_USERNAME=sail
DB_PASSWORD=password
MAIL_FROM_ADDRESS=admin@example.com

【Stripe決済を使用する場合、最下部に以下を追記】
STRIPE_KEY=your_stripe_publishable_key_here
STRIPE_SECRET=your_stripe_secret_key_here
※ここを自分のStripeのキーに差し替えてください
```

5. php artisan key:generate
6. php artisan migrate --seed
7. php artisan storage:link
8. php artisan test

## メール認証

開発環境ではメール確認用に Mailpit を使用しています。`.env` を次のように設定してください。

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS=admin@example.com
```

会員登録時に送信される認証メールおよび予約確定・キャンセルメールは、[Mailpit](http://localhost:8025) で確認できます。

## データベース概要

| テーブル       | 用途                                                                             |
| -------------- | -------------------------------------------------------------------------------- |
| `users`        | ユーザー情報および管理者フラグ（is_admin）、アカウント状態                       |
| `facilities`   | 施設情報（施設名、説明、収容人数、30分あたりの料金、画像パス、公開ステータス）   |
| `reservations` | 施設予約情報（開始・終了日時、利用人数、決済合計金額、決済方法、予約ステータス） |

## ER図

erDiagram
users ||--o{ reservations : "作成する"
facilities ||--o{ reservations : "予約される"

    users {
        bigint id PK
        string name
        string email
        string password
        timestamp email_verified_at
        boolean is_admin "管理者フラグ"
        boolean is_active "アカウント状態"
    }

    facilities {
        bigint id PK
        string name
        text description
        integer capacity "収容人数"
        integer price_per_30min "30分単価"
        string image_path
        boolean is_active "公開ステータス"
    }

    reservations {
        bigint id PK
        bigint user_id FK "予約ユーザー"
        bigint reservable_id "予約対象ID (Facility ID)"
        string reservable_type "予約対象モデル (App\\Models\\Facility)"
        datetime start_time
        datetime end_time
        integer reserved_seats
        integer price "決済金額"
        string payment_type "credit_card / onsite"
        string status "confirmed / pending_payment / cancelled"
    }

## 初期アカウント

`make init` または `make fresh` 実行時に、以下のアカウントが作成されます。パスワードはすべて `password` です。

| 役割 / アカウント名       | メールアドレス    | パスワード | 初期状態                         |
| ------------------------- | ----------------- | ---------- | -------------------------------- |
| 管理者 / システム管理者   | admin@example.com | password   | 管理者権限あり (is_admin = true) |
| 一般ユーザー / テスト太郎 | user@example.com  | password   | 一般ユーザー権限                 |

## アプリケーションを開く

| サービス         | URL                   |
| ---------------- | --------------------- |
| アプリケーション | http://localhost      |
| phpMyAdmin       | http://localhost:8080 |
| Mailpit          | http://localhost:8025 |

Mailpitでは、会員登録時のメール認証通知や予約確定・キャンセル通知メールを確認できます。

## テストの実行

PHP コンテナ内または Laravel Sail コマンドでテストを実行します。
PHPUnit（In-Memory SQLite）を使用してテストを実行します。事前準備は不要です。

```bash
# テストの実行
./vendor/bin/sail artisan test
```

## ディレクトリ構成

```text
.
├── app/                  # コントローラー、モデル、ミドルウェア、レスポンス等
│   ├── Http/Controllers/ # 一般用および管理者用（Admin/）コントローラー
│   ├── Models/           # User, Facility, Reservation モデル
│   └── Providers/        # FortifyServiceProvider 等
├── database/             # マイグレーション、シーダー
├── resources/views/      # Blade テンプレート（auth/, reservations/, admin/ 等）
├── routes/               # web.php, console.php 等
├── tests/                # Feature / Unit テスト
└── docker-compose.yml
```
