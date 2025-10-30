# OAuth2/OpenID Connect 認可サーバー

CakePHP 5.xで実装されたOAuth2/OpenID Connect認可サーバーです。

## プロジェクト概要

このプロジェクトは、OAuth2およびOpenID Connectプロトコルに準拠した認可サーバーの実装です。
TDD（テスト駆動開発）アプローチで開発されています。

## 必要な環境

- PHP 8.3.27以上
- PostgreSQL 14以上
- Composer 2.x
- asdf（推奨）

### 主要な依存関係

- CakePHP 5.2.9
- cakephp/authentication 3.3.2
- PHPUnit 12.4.1

## セットアップ

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd idp-php
```

### 2. 依存関係のインストール

```bash
composer install
```

### 3. データベースのセットアップ

#### PostgreSQLサービスの起動

```bash
brew services start postgresql@14
```

#### データベースの作成

```bash
# 開発用データベース
createdb idp_development

# テスト用データベース
createdb idp_test
```

#### データベース設定

`config/app_local.php`を作成し、データベース接続情報を設定します：

```php
<?php
return [
    'Datasources' => [
        'default' => [
            'host' => 'localhost',
            'username' => 'your_username',
            'password' => 'your_password',
            'database' => 'idp_development',
            'url' => env('DATABASE_URL', null),
        ],
        'test' => [
            'host' => 'localhost',
            'username' => 'your_username',
            'password' => 'your_password',
            'database' => 'idp_test',
            'url' => env('DATABASE_TEST_URL', null),
        ],
    ],
];
```

#### マイグレーションの実行

```bash
bin/cake migrations migrate
```

### 4. テストユーザーの作成

開発環境用のテストユーザーを作成します：

```bash
psql -d idp_development -c "
INSERT INTO users (id, username, email, password_hash, is_active, created, modified) VALUES
('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'testuser1', 'testuser1@example.com',
 '\$2y\$12\$FL.ZlFBAJkwwuIU2Bdtf8OQ9tiSVAUVhXsDYh4CBkFec.4VUlbeJ2', true, NOW(), NOW()),
('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'testuser2', 'testuser2@example.com',
 '\$2y\$12\$4GHmwGbFxPwnsO9xCy4r/.2PTg/1qcC/LiYuDIIy9jKdmfL0i4iDW', true, NOW(), NOW()),
('cccccccc-cccc-cccc-cccc-cccccccccccc', 'inactiveuser', 'inactive@example.com',
 '\$2y\$12\$yzzUX7Ob69IYFBW9DS.YnOn1rPVu6c6frBznCz2HHSwRSZFRgXbZG', false, NOW(), NOW());
"
```

## 起動方法

### 開発サーバーの起動

推奨: 起動スクリプトを使用（データベース接続チェック付き）

```bash
./bin/start-dev.sh
```

または、直接起動:

```bash
bin/cake server
```

サーバーが起動したら、ブラウザで以下にアクセスできます：

- トップページ: http://localhost:8765/
- ログインページ: http://localhost:8765/users/login

### テストユーザー

以下のアカウントでログインできます：

| ユーザー名 | パスワード | ステータス |
|-----------|-----------|-----------|
| testuser1 | password123 | 有効 |
| testuser2 | password456 | 有効 |
| inactiveuser | password789 | 無効 |

### ソーシャルログイン（オプション）

Google および GitHub を使ったソーシャルログインも利用できます。

設定手順: [docs/SOCIAL_LOGIN_SETUP.md](docs/SOCIAL_LOGIN_SETUP.md)

設定後、ログインページに「Sign in with Google」「Sign in with GitHub」ボタンが表示されます。

## テストの実行

### 全テストの実行

```bash
vendor/bin/phpunit
```

### 特定のテストの実行

```bash
# Userモデルのテスト
vendor/bin/phpunit tests/TestCase/Model/Table/UsersTableTest.php

# ログイン機能のテスト
vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php
```

### テストカバレッジ

```bash
vendor/bin/phpunit --coverage-html coverage/
```

## プロジェクト構造

```
idp-php/
├── config/              # 設定ファイル
├── src/
│   ├── Controller/      # コントローラー
│   ├── Model/          # モデル（Entity, Table）
│   └── Application.php # アプリケーション設定
├── tests/
│   ├── Fixture/        # テストデータ
│   └── TestCase/       # テストケース
├── templates/          # ビューテンプレート
├── webroot/            # 公開ディレクトリ
└── docs/               # ドキュメント
    ├── DEVELOPMENT_GUIDE.md
    └── PROGRESS.md
```

## 開発状況

現在の実装状況については`docs/PROGRESS.md`を参照してください。

### 完了した機能

- データベーススキーマ設計
- Clientモデル（Entity/Table）
- Userモデル（Entity/Table）
- ユーザー認証機能（ログイン/ログアウト）

### 次のステップ

1. クライアント登録機能
2. クライアント認証機能
3. 認可コードフロー実装

## ドキュメント

- [開発ガイドライン](docs/DEVELOPMENT_GUIDE.md)
- [進捗状況](docs/PROGRESS.md)
- [プロジェクト固有ガイド](.claude/CLAUDE.md)
- [ソーシャルログイン設定](docs/SOCIAL_LOGIN_SETUP.md)
- [統合テスト手順](docs/INTEGRATION_TEST.md)

## ライセンス

MIT License
