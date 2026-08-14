# CoSpace Link

複数のコワーキングスペースを、エリア・日時・設備から横断検索して予約できるポータル型システムです。一般ユーザー、店舗管理者、全体管理者の3ロールに対応しています。

## 店舗事業者が本プラットフォームへ乗り換える理由（導入メリット）

既存の予約ツール（自社サイト、紙、Excel台帳など）からCoSpace Linkへ一元化・乗り換えを行う主な動機および事業者様への価値提案は以下の4点です。

1. **エリア検索による「新規顧客の獲得」と「直前空き枠の収益化」**  
   自社予約ツールでは「自社店舗名を認知している既存客」しか獲得できません。ポータル型のCoSpace Linkに集約することで、「渋谷 Web会議 個室」「梅田 14:00〜」といったエリア・日時指定で検索するプラットフォーム上の潜在顧客を獲得できます。また、埋まりにくい平日日中や直前空き枠を可視化し稼働率を向上させます。

2. **事前決済とキャンセル・返金業務の完全自動化（運用コスト削減）**  
   事前Stripeクレジットカード決済を必須とすることで、無断キャンセル（ノーショー）による売上未回収リスクを解消します。ユーザー都合のキャンセル・返金処理や確認メール送信も全自動で行われるため、店舗側の事務・電話応対コストを大幅に抑えられます。

3. **複雑な定員管理・排他制御のシステム化（ダブルブッキングの防止）**  
   会議室の重複防止だけでなく、フリーアドレス席の「時間帯ごとの同時利用人数計算（定員オーバー防止）」など、紙やExcelでは管理ミスが起きやすい高度な予約排他制御を、導入するだけでそのまま利用できます。

4. **「代理予約」機能による既存客・現地利用者のスムーズな統合**  
   店舗管理者画面から「代理予約（現地払い・無料対応含む）」を登録できます。電話予約や飛び込みの現地利用客も同一の管理画面でリアルタイムに在庫共有できるため、従来の運用を破綻させずに移行が可能です。

---

## 主な機能

### 一般ユーザー

- 会員登録、メール認証、ログイン、プロフィール編集
- エリア、キーワード、日時、種別、設備タグによる空き施設検索
- 検索結果カードの施設画像表示、コンパクトな施設詳細表示
- 30分単位の空き状況、重複予約防止、エリア席の定員管理
- Stripeカード決済、現地払い、無料予約
- 予約履歴、キャンセル、Stripe返金、メール通知

### 店舗管理者

- 自店舗ダッシュボード
- 店舗情報、営業時間、画像、店舗共通設備の編集
- 施設の登録・編集・公開管理、施設固有設備の設定
- 自店舗の予約・売上・返金確認、CSV出力
- 会員を指定した代理予約の登録（現地払い・無料対応）

### 全体管理者

- ポータル全体の集計
- 加盟店舗の登録、編集、掲載停止、再掲載
- 店舗管理者の招待
- 全施設・全予約への店舗名／エリア表示
- 施設一覧の店舗、エリア、種別、公開状態による絞り込み・並び替え
- 予約一覧の店舗、利用日、予約状態による絞り込み・並び替え、CSV出力
- 会員、代理予約の管理

## 設備タグ

| 区分         | タグ                                            |
| ------------ | ----------------------------------------------- |
| 店舗共通設備 | Wi-Fi、電源、フリードリンク                     |
| 施設固有設備 | モニター、ホワイトボード、防音、Web会議ブース可 |

## 使用技術

- PHP 8.3以上（Sail: PHP 8.5）
- Laravel 13 / Laravel Fortify
- MySQL 8.4 / Redis / Nginx
- Stripe Checkout / Webhook / Refund API
- Docker Compose / Laravel Sail / Mailpit
- PHPUnit 12

## 環境構築

```bash
git clone git@github.com:toomochin/cospace-link.git
cd cospace-link
cp .env.example .env
composer install
```

`.env` の主な開発用設定:

```dotenv
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cospace_link
DB_USERNAME=sail
DB_PASSWORD=password
REDIS_HOST=redis
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS=admin@example.com
```

開発環境ではメール確認用に Mailpit を使用しています。

```bash
./vendor/bin/sail up -d --build
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
./vendor/bin/sail artisan test
```

DBを作り直す場合:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

## Stripe設定

```dotenv
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

上記を自分のStripeテスト環境のキーへ差し替えてください。

```bash
stripe listen --forward-to http://localhost/stripe/webhook
```

購読イベントは `checkout.session.completed` と `checkout.session.expired` です。

## 初期アカウント

`migrate:fresh --seed` で作成され、パスワードはすべて `password` です。

| ロール       | メールアドレス              | 所属         |
| ------------ | --------------------------- | ------------ |
| 全体管理者   | `admin@example.com`         | ポータル全体 |
| 店舗管理者   | `shibuya-owner@example.com` | CoSpace 渋谷 |
| 店舗管理者   | `umeda-owner@example.com`   | CoSpace 梅田 |
| 一般ユーザー | `test@example.com`          | なし         |

初期データとしてCoSpace 渋谷・CoSpace 梅田の2店舗と、公開中・メンテナンス中を含む15施設が登録されます。

## 開発用URL

| サービス   | URL                   |
| ---------- | --------------------- |
| アプリ     | http://localhost      |
| phpMyAdmin | http://localhost:8080 |
| Mailpit    | http://localhost:8025 |

## 主要テーブル

| テーブル       | 用途                                         |
| -------------- | -------------------------------------------- |
| `shops`        | 加盟店舗、営業時間、店舗共通設備、掲載状態   |
| `facilities`   | 施設、30分料金、定員、施設固有設備、公開状態 |
| `users`        | 3ロールと店舗所属                            |
| `reservations` | 予約日時、人数、料金、決済方法、状態         |
| `payments`     | Stripe決済・返金情報                         |

## ER図

![ER図](docs/ER図.png)

- [Mermaid版](docs/er-diagram.md)

## テスト

```bash
./vendor/bin/sail artisan test
```

現在のテスト実績: 74 tests / 270 assertions
