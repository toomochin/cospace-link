# CoSpace Link 本番運用手順

## 必須の環境設定

本番サーバーの `.env` は、少なくとも次を設定します。実際の秘密値をGitへ登録しないでください。

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=30
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
CACHE_STORE=redis
QUEUE_CONNECTION=redis
FILESYSTEM_DISK=s3
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

`APP_KEY`、DB、Redis、メール、S3の各認証情報も本番用の値を設定します。

## デプロイ

```bash
php artisan down --retry=60
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
php artisan queue:restart
php artisan up
```

デプロイ後に `/up` がHTTP 200を返すこと、ログイン、検索、現地決済予約、Stripeテスト決済を確認します。

## キューワーカー

Supervisorやコンテナ基盤で常時起動し、異常終了時に再起動させます。

```bash
php artisan queue:work redis --sleep=3 --tries=3 --timeout=90 --max-time=3600
```

失敗ジョブは `php artisan queue:failed` で確認し、原因修正後に `php artisan queue:retry <id>` を実行します。

## スケジューラー

サーバーのcronへ次の1行を登録します。

```cron
* * * * * cd /path/to/cospace-link && php artisan schedule:run >> /dev/null 2>&1
```

`php artisan schedule:list` で登録を確認できます。処理済みStripeイベントを90日後、失敗ジョブを7日後、ジョブバッチを設定期間後に削除します。複数台構成では全台が同じRedisキャッシュを使う必要があります。

## Stripe Webhook

Stripe Dashboardで本番URL `https://your-domain.example/stripe/webhook` を登録し、最低限次のイベントを購読します。

- `checkout.session.completed`
- `checkout.session.expired`

発行された署名シークレットを `STRIPE_WEBHOOK_SECRET` に設定します。WebhookのHTTP 2xx率と再送をStripe Dashboardで監視してください。

## バックアップと復元

DBは暗号化した日次バックアップを別ストレージへ保存し、少なくとも30日保持します。画像をローカル保存する場合は `storage/app/public` も保存します。S3利用時はバージョニングまたはライフサイクル保護を有効にします。

```bash
mysqldump --single-transaction --routines --triggers -h DB_HOST -u DB_USER -p DB_NAME | gzip > cospace-link-YYYYMMDD.sql.gz
```

復元は隔離環境で定期的に試験し、マイグレーション状態、ユーザー数、予約数、決済数、画像表示を確認します。復元試験なしのバックアップは正常とみなしません。

## 監視

- `/up` のHTTP応答
- 5xx件数と `storage/logs/laravel-*.log`
- キュー滞留数と失敗ジョブ数
- Stripe Webhookの失敗率
- MySQL、Redis、ディスク/S3容量
- バックアップ完了と復元試験日

アラートには秘密値、カード情報、セッションCookieを含めないでください。
