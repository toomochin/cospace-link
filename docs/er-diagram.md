# ER図

このER図は `database/migrations` の定義を基準に、2026年7月31日時点の
データベース構造を表したものです。

## 業務テーブル

```mermaid
erDiagram
    SHOPS ||--o{ FACILITIES : "施設を所有する"
    SHOPS o|--o{ USERS : "店舗管理者が所属する"
    USERS ||--o{ RESERVATIONS : "予約する"
    USERS ||--o{ PASSKEYS : "所有する"
    FACILITIES ||--o{ RESERVATIONS : "予約対象（論理関連）"
    RESERVATIONS ||--o{ PAYMENTS : "決済を持つ"
    RESERVATIONS o|--o{ MAIL_LOGS : "メール送信履歴を持つ"

    SHOPS {
        bigint id PK
        varchar name
        varchar area_name "INDEX"
        varchar address
        text access "NULL可"
        varchar opening_hours
        text description "NULL可"
        varchar image_path "NULL可"
        json amenities "NULL可・店舗共通設備"
        boolean is_active "INDEX"
        timestamp created_at
        timestamp updated_at
    }

    USERS {
        bigint id PK
        varchar name
        varchar email UK
        timestamp email_verified_at "NULL可"
        varchar password
        text two_factor_secret "NULL可"
        text two_factor_recovery_codes "NULL可"
        timestamp two_factor_confirmed_at "NULL可"
        boolean is_admin
        varchar role "INDEX・user / shop_owner / system_admin"
        bigint shop_id FK "NULL可・ON DELETE SET NULL"
        boolean is_active
        varchar remember_token "NULL可"
        timestamp created_at
        timestamp updated_at
    }

    PASSKEYS {
        bigint id PK
        bigint user_id FK
        varchar name
        varchar credential_id UK
        json credential
        timestamp last_used_at "NULL可"
        timestamp created_at
        timestamp updated_at
    }

    FACILITIES {
        bigint id PK
        bigint shop_id FK "ON DELETE CASCADE"
        varchar name
        varchar type "meeting_room / area"
        integer price_per_30min
        integer capacity
        varchar equipment "NULL可"
        json amenities "NULL可・施設固有設備"
        text description "NULL可"
        varchar image_path "NULL可"
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    RESERVATIONS {
        bigint id PK
        bigint user_id FK
        bigint reservable_id "ポリモーフィック参照"
        varchar reservable_type "ポリモーフィック参照"
        datetime start_time
        datetime end_time
        integer reserved_seats
        integer price
        varchar payment_type "credit_card / onsite / free"
        varchar status "pending_payment / confirmed / cancelled"
        timestamp created_at
        timestamp updated_at
    }

    PAYMENTS {
        bigint id PK
        bigint reservation_id FK
        varchar stripe_payment_intent_id UK "NULL可"
        varchar stripe_refund_id UK "NULL可"
        integer amount
        varchar status
        timestamp created_at
        timestamp updated_at
    }

    PROCESSED_WEBHOOKS {
        bigint id PK
        varchar stripe_event_id UK
        timestamp created_at
        timestamp updated_at
    }

    MAIL_LOGS {
        bigint id PK
        bigint reservation_id FK "NULL可"
        varchar mail_type
        varchar status "pending / sent / failed"
        text error_message "NULL可"
        timestamp created_at
        timestamp updated_at
    }
```

### 関連の補足

- `facilities.shop_id` → `shops.id` は物理外部キーです。店舗削除時に施設も削除されます。
- `users.shop_id` → `shops.id` はNULL許容の物理外部キーです。店舗削除時はNULLへ更新されます。`shop_owner` は1店舗に所属する前提です。
- `shops.amenities` はWi-Fi・電源・フリードリンクなどの店舗共通設備を保持します。
- `facilities.amenities` はモニター・ホワイトボード・防音・Web会議ブース可などの施設固有設備を保持します。
- `reservations.user_id` → `users.id` は物理外部キーです。ユーザー削除時に予約も削除されます。
- `reservations.reservable_id` と `reservations.reservable_type` はLaravelのポリモーフィック関連です。現在の実装では `facilities` が予約対象ですが、データベース上の物理外部キーはありません。
- `payments.reservation_id` → `reservations.id` は物理外部キーです。予約削除時に決済も削除されます。`reservation_id` に一意制約はないため、1予約に複数の決済レコードを保持できます。
- `mail_logs.reservation_id` → `reservations.id` はNULL許容の物理外部キーです。予約削除時は `NULL` に更新され、送信履歴は残ります。
- `processed_webhooks` はStripeイベントの重複処理を防ぐ独立テーブルです。

## Laravel基盤テーブル

アプリケーションの業務データとは直接関連しない、認証・セッション・キャッシュ・
キュー用のテーブルです。

```mermaid
erDiagram
    USERS o|--o{ SESSIONS : "利用する（論理関連）"

    PASSWORD_RESET_TOKENS {
        varchar email PK
        varchar token
        timestamp created_at "NULL可"
    }

    SESSIONS {
        varchar id PK
        bigint user_id "NULL可・索引"
        varchar ip_address "NULL可"
        text user_agent "NULL可"
        longtext payload
        integer last_activity
    }

    CACHE {
        varchar key PK
        mediumtext value
        bigint expiration
    }

    CACHE_LOCKS {
        varchar key PK
        varchar owner
        bigint expiration
    }

    JOBS {
        bigint id PK
        varchar queue
        longtext payload
        smallint attempts
        integer reserved_at "NULL可"
        integer available_at
        integer created_at
    }

    JOB_BATCHES {
        varchar id PK
        varchar name
        integer total_jobs
        integer pending_jobs
        integer failed_jobs
        longtext failed_job_ids
        mediumtext options "NULL可"
        integer cancelled_at "NULL可"
        integer created_at
        integer finished_at "NULL可"
    }

    FAILED_JOBS {
        bigint id PK
        varchar uuid UK
        varchar connection
        varchar queue
        longtext payload
        longtext exception
        timestamp failed_at
    }
```

`sessions.user_id` には索引がありますが、マイグレーション上の外部キー制約はありません。
