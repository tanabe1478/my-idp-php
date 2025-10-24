# OAuth2/OpenID Connect 認可サーバー - 開発ガイドライン

## 目次

1. [プロジェクト概要](#プロジェクト概要)
2. [技術スタック](#技術スタック)
3. [開発環境のセットアップ](#開発環境のセットアップ)
4. [TDD方法論](#tdd方法論)
5. [Tidy First原則](#tidy-first原則)
6. [コミット規律](#コミット規律)
7. [コード品質基準](#コード品質基準)
8. [OAuth2/OpenID Connect実装戦略](#oauth2openid-connect実装戦略)
9. [セキュリティガイドライン](#セキュリティガイドライン)
10. [PostgreSQLガイドライン](#postgresqlガイドライン)
11. [CakePHP規約](#cakephp規約)
12. [プロジェクト構造](#プロジェクト構造)
13. [テスト戦略](#テスト戦略)

---

## プロジェクト概要

このプロジェクトは、CakePHPを使用してゼロから構築する教育用のOAuth2およびOpenID Connect認可サーバーです。主な目的は以下の通りです：

- **PHPを深く学ぶ** - 外部のOAuthライブラリを使わずに複雑なプロトコルを実装
- **TDD実践をマスター** - Kent Beckの方法論に従う
- **Tidy First原則を適用** - クリーンで進化可能なコードを維持
- **OAuth2/OpenID Connectを理解** - 根本的なレベルで理解する

**重要**: これは学習プロジェクトです。すべてのOAuth2/OpenID Connect機能を手動で実装し、学習機会を最大化します。

---

## 技術スタック

### コア技術

- **PHP**: 8.3.x (最新安定版)
- **CakePHP**: 5.x (最新安定版 - 現在5.1.x)
- **データベース**: PostgreSQL (最新安定版)
- **テスト**: PHPUnit (CakePHPにバンドル)
- **コード品質**: PHP_CodeSniffer, PHPStan

### なぜこれらのバージョン？

- **PHP 8.3**: 最新機能（readonly クラス、型付きクラス定数など）を提供
- **CakePHP 5**: PHP 8.1+が必要な最新フレームワークバージョン
- **PostgreSQL**: 堅牢なACIDコンプライアンス、認可サーバーのデータ整合性に必須

---

## 開発環境のセットアップ

### 前提条件

```bash
# PHPバージョン確認（8.3.xであるべき）
php -v

# Composer確認
composer --version

# PostgreSQL確認
psql --version
```

### 初期セットアップ

```bash
# CakePHPプロジェクトのインストール
composer create-project --prefer-dist cakephp/app:~5.0 .

# config/app_local.phpでデータベース設定
# PostgreSQL接続設定を使用

# データベース作成
createdb idp_development
createdb idp_test

# マイグレーション実行（作成後）
bin/cake migrations migrate

# テスト実行でセットアップ確認
vendor/bin/phpunit
```

---

## TDD方法論

Kent BeckのTest-Driven Developmentアプローチに厳密に従います。

### Red-Green-Refactorサイクル

```
1. RED（レッド）: 望ましい動作を定義する失敗するテストを書く
2. GREEN（グリーン）: テストを通すための最小限のコードを書く
3. REFACTOR（リファクタリング）: テストをグリーンに保ちながらコード構造を改善
```

### TDDのルール

1. **常にテストを先に書く**
   - 失敗するテストなしにプロダクションコードを書かない
   - テストがAPIと動作を定義する

2. **最もシンプルな失敗するテストを書く**
   - 機能の小さな増分に焦点を当てる
   - テストの失敗を明確で情報豊富にする

3. **通すための最小限のコードを書く**
   - 将来の要件を予測しない
   - 「賢い」コードを書きたい衝動に抵抗する
   - 動く可能性のある最もシンプルなもの

4. **グリーンの時のみリファクタリング**
   - リファクタリング前にすべてのテストが通っている必要がある
   - リファクタリングステップごとにテストを実行

### テスト命名規則

動作を表現する説明的な名前を使用：

```php
// 良い例
public function testShouldGenerateAuthorizationCodeWhenUserConsents()
public function testShouldRejectExpiredAuthorizationCode()

// 悪い例
public function testCode()
public function testExpiration()
```

### 不具合修正時

1. バグを実証するAPI レベルの失敗するテストを書く
2. 問題を再現する最小のテストを書く
3. 両方のテストを通すようにコードを修正
4. 必要に応じてリファクタリング

---

## Tidy First原則

コード品質を維持しリスクを最小化するために、構造変更と動作変更を分離します。

### 2種類の変更

1. **構造変更（整理）**
   - 変数、メソッド、クラスの名前変更
   - メソッドやクラスの抽出
   - コードのより良い場所への移動
   - コードの整形
   - **動作を変更しない**

2. **動作変更**
   - 新機能の追加
   - バグ修正
   - 既存機能の変更
   - **コードが行うことを変更する**

### ルール

1. **構造変更と動作変更を同じコミットに混ぜない**
2. **両方の変更が必要な場合は常に先に整理する**
3. **構造変更が動作を変更しないことを検証**
   - 構造変更前にすべてのテストを実行
   - 構造変更を行う
   - 再度すべてのテストを実行 - まだ通るべき

### ワークフロー例

```
# 散らかったコードに機能を追加する必要がある

1. コミット: "Tidy: トークン検証ロジックを別メソッドに抽出"
   (前後でテストが通る)

2. コミット: "リフレッシュトークンローテーションのサポートを追加"
   (動作変更)
```

### いつ整理するか

- **機能追加前**: まずコードを変更しやすくする
- **機能追加後**: 実装をクリーンアップ
- **重複に気づいた時**: 共通コードを抽出
- **コードが理解しにくい時**: 命名と構造を改善

---

## コミット規律

### コミットするのは以下の場合のみ

1. **すべてのテストが通っている**（グリーン状態）
2. **すべてのリンター/静的解析の警告が解決されている**
3. **変更が単一の論理的な作業単位を表している**
4. **コミットメッセージが変更を明確に説明している**

### コミットメッセージフォーマット

```
[タイプ]: 簡潔な説明

必要に応じて詳細な説明。

# タイプ:
- Tidy: 構造変更、動作変更なし
- Feature: 新機能
- Fix: バグ修正
- Test: テストの追加または変更
- Docs: ドキュメント変更
- Config: 設定変更
```

### 例

```
Tidy: トークン生成をTokenGeneratorサービスに抽出

AuthorizationControllerからトークン生成ロジックを専用のサービスクラスに
移動し、テスタビリティと関心の分離を改善。

---

Feature: 認可コードフローを実装

コード生成、保存、アクセストークンへの交換を含む
OAuth2認可コードグラントタイプのサポートを追加。

---

Fix: 認可コードの再利用を防止

リプレイ攻撃を防ぐため、認可コードは交換時に
即座に使用済みとしてマークされるようになった。
```

### 小さく頻繁なコミット

- 各グリーンフェーズ後にコミット
- 各成功したリファクタリング後にコミット
- 少数の大きなコミットより多数の小さなコミットを優先
- 各コミットはデプロイ可能であるべき（テストが通る）

---

## コード品質基準

### PHP 8.3ベストプラクティス

```php
// 型付きプロパティを使用
class Client
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        private string $secret,
    ) {}
}

// 定数にはenumを使用
enum GrantType: string
{
    case AUTHORIZATION_CODE = 'authorization_code';
    case REFRESH_TOKEN = 'refresh_token';
    case CLIENT_CREDENTIALS = 'client_credentials';
}

// match式を使用
$responseType = match($grantType) {
    GrantType::AUTHORIZATION_CODE => 'code',
    GrantType::REFRESH_TOKEN => 'token',
    default => throw new InvalidArgumentException(),
};
```

### SOLID原則

1. **単一責任原則**: 各クラスは変更する理由が1つ
2. **開放閉鎖原則**: 拡張に対して開き、変更に対して閉じる
3. **リスコフの置換原則**: サブタイプは基本型の代わりに使えるべき
4. **インターフェース分離原則**: 1つの汎用より多くの特定インターフェース
5. **依存性逆転原則**: 具象ではなく抽象に依存する

### コード品質ルール

1. **重複を徹底的に排除**
   - 同じコードを2回書いたら抽出する
   - 継承またはコンポジションを適切に使用

2. **意図を明確に表現**
   - 名前は目的を明らかにすべき
   - 普遍的に知られていない限り略語を避ける
   - メソッドは名前が示すことを行うべき

3. **メソッドを小さく保つ**
   - 理想的には最大10〜15行
   - メソッドごとに1つの抽象化レベル
   - 抽出できなくなるまで抽出

4. **状態と副作用を最小化**
   - 可能な限り純粋関数を優先
   - 副作用を明白で限定的にする
   - 可能な場合は不変性

5. **依存関係を明示的にする**
   - コンストラクタインジェクションを使用
   - サービスロケーターパターンを避ける
   - CakePHPの依存性注入を使用

### 静的解析

最大レベルでPHPStanを実行：

```bash
# インストール
composer require --dev phpstan/phpstan

# 実行
vendor/bin/phpstan analyse src tests --level=9
```

### コードスタイル

PSR-12コーディング規約に従う：

```bash
# チェック
vendor/bin/phpcs src tests --standard=PSR12

# 修正
vendor/bin/phpcbf src tests --standard=PSR12
```

---

## OAuth2/OpenID Connect実装戦略

学習目的でOAuth2/OpenID Connectをゼロから実装するため、TDDを使用して段階的に構築します。

### 実装順序

#### フェーズ1: 基盤
1. クライアント管理（登録、保存、検証）
2. ユーザー認証（基本ログイン）
3. スコープ管理

#### フェーズ2: 認可コードフロー
1. 認可エンドポイント
2. 認可コード生成と保存
3. トークンエンドポイント（コード交換）
4. アクセストークン生成（JWT）
5. トークン検証

#### フェーズ3: トークン管理
1. リフレッシュトークン
2. トークン取り消し
3. トークンイントロスペクション

#### フェーズ4: OpenID Connect
1. IDトークン生成
2. UserInfoエンドポイント
3. ディスカバリードキュメント
4. JWKSエンドポイント

#### フェーズ5: 追加フロー
1. クライアントクレデンシャルグラント
2. PKCE拡張
3. 動的クライアント登録

### 構築する主要コンポーネント

```
src/
  Service/
    OAuth2/
      ClientValidator.php
      AuthorizationCodeGenerator.php
      TokenGenerator.php
      ScopeValidator.php
    JWT/
      JWTEncoder.php
      JWKSGenerator.php
    Crypto/
      SecureRandom.php
      HashGenerator.php
```

### 従うべき仕様

- [RFC 6749](https://tools.ietf.org/html/rfc6749) - OAuth 2.0 Framework
- [RFC 7636](https://tools.ietf.org/html/rfc7636) - PKCE
- [RFC 7662](https://tools.ietf.org/html/rfc7662) - Token Introspection
- [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html)

これらの仕様を手元に置き、実装中に参照してください。

---

## セキュリティガイドライン

認可サーバーは高価値のターゲットです。セキュリティは最重要です。

### 重要なセキュリティ要件

1. **機密データをログに記録しない**
   - パスワード、トークン、シークレット、認可コード
   - ログではプレースホルダーを使用：`Validating token [REDACTED]`

2. **シークレットには定数時間比較を使用**
   ```php
   // 良い
   hash_equals($expected, $actual)

   // 悪い - タイミング攻撃に脆弱
   $expected === $actual
   ```

3. **暗号学的に安全なランダム値を生成**
   ```php
   // 良い
   random_bytes(32)

   // 悪い
   rand() または mt_rand()
   ```

4. **クライアントシークレットをハッシュ化**
   ```php
   // 強力なアルゴリズムでpassword_hashを使用
   $hashedSecret = password_hash($secret, PASSWORD_ARGON2ID);
   ```

5. **短命な認可コード**
   - 最大10分のライフタイム
   - 1回のみ使用
   - client_idとredirect_uriにバインド

6. **アクセストークンのセキュリティ**
   - 短いライフタイム（学習用には15分を推奨）
   - 署名検証付きのJWTを使用
   - 最小限のクレームを含める

7. **リフレッシュトークンのセキュリティ**
   - データベースにハッシュ化して保存
   - トークンローテーションを実装
   - 疑わしい活動時に取り消す

8. **本番環境ではHTTPSのみ**
   - HTTP経由でトークンを送信しない
   - セキュアCookieを使用（Secure、HttpOnly、SameSite）

9. **リダイレクトURIを厳密に検証**
   - 完全一致、ワイルドカードなし
   - すべての許可されたURIを事前登録
   - オープンリダイレクトを防ぐ

10. **レート制限**
    - すべてのエンドポイントにレート制限を実装
    - 特にトークンと認可エンドポイント

### 入力検証

```php
// 常に検証とサニタイズ
class ClientRegistrationRequest
{
    public function __construct(
        public readonly string $name,
        public readonly array $redirectUris,
        public readonly array $grantTypes,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Name required');
        }

        foreach ($this->redirectUris as $uri) {
            if (!filter_var($uri, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Invalid URI');
            }
            if (parse_url($uri, PHP_URL_SCHEME) !== 'https') {
                throw new InvalidArgumentException('HTTPS required');
            }
        }
    }
}
```

---

## PostgreSQLガイドライン

### 接続設定

```php
// config/app_local.php
'Datasources' => [
    'default' => [
        'driver' => Cake\Database\Driver\Postgres::class,
        'host' => 'localhost',
        'username' => 'postgres',
        'password' => 'secret',
        'database' => 'idp_development',
        'encoding' => 'utf8',
        'timezone' => 'UTC',
        'cacheMetadata' => true,
        'quoteIdentifiers' => false,
    ],
    'test' => [
        // defaultと同じだがidp_testデータベースを使用
    ],
]
```

### データベース設計原則

1. **複数ステップの操作にはトランザクションを使用**
   ```php
   $this->getConnection()->transactional(function () {
       // 複数のデータベース操作
   });
   ```

2. **PostgreSQLの機能を活用**
   - 主キーにはUUID（`uuid-ossp`拡張を使用）
   - 柔軟なデータ保存にはJSONB
   - 頻繁にクエリされるカラムにインデックス
   - 条件付きクエリには部分インデックス

3. **認可コードのスキーマ例**
   ```sql
   CREATE TABLE authorization_codes (
       id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
       code VARCHAR(128) UNIQUE NOT NULL,
       client_id UUID NOT NULL,
       user_id UUID NOT NULL,
       redirect_uri TEXT NOT NULL,
       scope TEXT,
       expires_at TIMESTAMP NOT NULL,
       used_at TIMESTAMP,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       INDEX idx_code (code),
       INDEX idx_expires (expires_at),
       CONSTRAINT fk_client FOREIGN KEY (client_id) REFERENCES clients(id),
       CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id)
   );
   ```

4. **スキーマ変更にはマイグレーション**
   ```bash
   # マイグレーション作成
   bin/cake bake migration CreateAuthorizationCodes

   # マイグレーション実行
   bin/cake migrations migrate

   # ロールバック
   bin/cake migrations rollback
   ```

---

## CakePHP規約

### ディレクトリ構造

CakePHP規約に従う：

```
src/
  Controller/
    OAuth2/
      AuthorizeController.php
      TokenController.php
  Model/
    Table/
      ClientsTable.php
      AuthorizationCodesTable.php
    Entity/
      Client.php
      AuthorizationCode.php
  Service/
    OAuth2/
      (ビジネスロジックサービス)

tests/
  TestCase/
    Controller/
      OAuth2/
    Service/
      OAuth2/
```

### 命名規則

- **コントローラー**: `Controller`サフィックス付きのPascalCase
- **モデル**: エンティティは単数形PascalCase、テーブルは複数形
- **サービス**: PascalCase（CakePHP規約ではないが、我々の規約）
- **メソッド**: camelCase
- **定数**: UPPER_SNAKE_CASE

### 依存性注入

CakePHPのサービスコンテナを使用：

```php
// src/Controller/OAuth2/TokenController.php
class TokenController extends AppController
{
    public function __construct(
        private TokenGenerator $tokenGenerator,
        private ClientValidator $clientValidator,
    ) {
        parent::__construct();
    }
}

// config/services.php
return [
    'services' => [
        TokenGenerator::class => DI\autowire(),
        ClientValidator::class => DI\autowire(),
    ],
];
```

### CakePHPでのテスト

```php
namespace App\Test\TestCase\Service\OAuth2;

use Cake\TestSuite\TestCase;

class TokenGeneratorTest extends TestCase
{
    protected array $fixtures = [
        'app.Clients',
        'app.Users',
    ];

    public function testShouldGenerateValidAccessToken(): void
    {
        $generator = new TokenGenerator();

        $token = $generator->generateAccessToken(
            clientId: 'client-123',
            userId: 'user-456',
            scope: 'read write'
        );

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }
}
```

---

## プロジェクト構造

```
idp-php/
├── bin/                    # CakePHPコンソールコマンド
├── config/                 # 設定ファイル
│   ├── app.php            # メイン設定
│   ├── app_local.php      # ローカル上書き（gitignore）
│   ├── routes.php         # ルート定義
│   └── services.php       # 依存性注入
├── docs/                   # プロジェクトドキュメント
│   ├── DEVELOPMENT_GUIDE.md
│   ├── ARCHITECTURE.md    # 作成予定
│   └── API.md            # 作成予定
├── logs/                   # アプリケーションログ
├── plugins/               # CakePHPプラグイン
├── resources/             # アセット、ビュー
├── src/
│   ├── Controller/
│   │   └── OAuth2/       # OAuth2エンドポイント
│   ├── Model/
│   │   ├── Entity/       # エンティティクラス
│   │   └── Table/        # テーブルクラス（リポジトリ）
│   ├── Service/
│   │   ├── OAuth2/       # OAuth2ビジネスロジック
│   │   ├── JWT/          # JWT処理
│   │   └── Crypto/       # 暗号ユーティリティ
│   └── Application.php
├── tests/
│   ├── Fixture/          # テストデータフィクスチャ
│   └── TestCase/         # テストケース
├── tmp/                   # 一時ファイル、キャッシュ
├── vendor/               # 依存関係
├── composer.json
├── phpunit.xml.dist
└── .gitignore
```

---

## テスト戦略

### テストタイプ

1. **ユニットテスト**
   - 個々のクラスを単独でテスト
   - 依存関係をモック
   - 高速実行
   - 高カバレッジ

2. **統合テスト**
   - 複数のコンポーネントを一緒にテスト
   - テストデータベースを使用
   - 実際のデータベース操作をテスト
   - サービス間の相互作用をテスト

3. **機能テスト**
   - HTTPエンドポイントをテスト
   - 完全なリクエスト/レスポンスサイクルをテスト
   - OAuth2フローをエンドツーエンドで検証

### テストカバレッジ目標

- 最低90%のコードカバレッジ
- セキュリティクリティカルなコードは100%カバレッジ
- すべてのOAuth2フローを完全にテスト

### テスト実行

```bash
# すべてのテスト
vendor/bin/phpunit

# 特定のテストファイル
vendor/bin/phpunit tests/TestCase/Service/OAuth2/TokenGeneratorTest.php

# カバレッジ付き
vendor/bin/phpunit --coverage-html coverage/

# 高速：ユニットテストのみ
vendor/bin/phpunit --testsuite Unit
```

### テストフィクスチャ

現実的なテストデータを作成：

```php
// tests/Fixture/ClientsFixture.php
class ClientsFixture extends TestFixture
{
    public array $records = [
        [
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Test Client',
            'secret' => '$argon2id$...', // ハッシュ化済み
            'redirect_uris' => '["https://client.example.com/callback"]',
            'grant_types' => '["authorization_code","refresh_token"]',
            'created_at' => '2024-01-01 00:00:00',
        ],
    ];
}
```

---

## 開発ワークフロー例

認可コード生成機能を追加する流れを見てみましょう：

### ステップ1: 失敗するテストを書く（RED）

```php
// tests/TestCase/Service/OAuth2/AuthorizationCodeGeneratorTest.php
public function testShouldGenerateUniqueAuthorizationCode(): void
{
    $generator = new AuthorizationCodeGenerator();

    $code = $generator->generate();

    $this->assertNotEmpty($code);
    $this->assertEquals(128, strlen($code)); // 要件例
}
```

テスト実行：**失敗**（クラスが存在しない）

### ステップ2: 通すようにする（GREEN）

```php
// src/Service/OAuth2/AuthorizationCodeGenerator.php
namespace App\Service\OAuth2;

class AuthorizationCodeGenerator
{
    public function generate(): string
    {
        return bin2hex(random_bytes(64));
    }
}
```

テスト実行：**成功**

### ステップ3: リファクタリング（必要に応じて）

テストが通っている。コードはシンプル。リファクタリングはまだ不要。

### ステップ4: コミット

```
git add tests/TestCase/Service/OAuth2/AuthorizationCodeGeneratorTest.php
git add src/Service/OAuth2/AuthorizationCodeGenerator.php
git commit -m "Feature: 認可コード生成を追加

random_bytes()を使用した暗号学的に安全な認可コード生成を実装。
コードは128文字（64バイトの16進エンコード）。"
```

### ステップ5: 次のテストを追加

機能の次の増分のためにサイクルを続ける。

---

## 開始チェックリスト

- [ ] PHP 8.3をインストール
- [ ] PostgreSQLをインストール
- [ ] Composerをインストール
- [ ] プロジェクトデータベースを作成
- [ ] CakePHPをインストール
- [ ] テストが正常に実行されることを確認
- [ ] 開発ツールをインストール（PHPStan、PHP_CodeSniffer）
- [ ] OAuth2 RFC 6749を読む
- [ ] OpenID Connect Core仕様を読む
- [ ] TDDを使用して最初の機能を開始

---

## リソース

### 公式ドキュメント
- [CakePHP 5.x ドキュメント](https://book.cakephp.org/5/ja/index.html)
- [PHP 8.3 ドキュメント](https://www.php.net/manual/ja/)
- [PostgreSQL ドキュメント](https://www.postgresql.jp/document/)

### OAuth2/OpenID Connect
- [OAuth 2.0 RFC 6749](https://tools.ietf.org/html/rfc6749)
- [OAuth 2.0 Security Best Practices](https://tools.ietf.org/html/draft-ietf-oauth-security-topics)
- [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html)
- [OAuth 2.0 Simplified](https://www.oauth.com/)

### TDDとクリーンコード
- Kent Beck - "Test-Driven Development: By Example"
- Kent Beck - "Tidy First?"
- Martin Fowler - "Refactoring: Improving the Design of Existing Code"

---

## 質問や問題？

これは生きたドキュメントです。プロジェクトの進化に伴い、何がうまくいくかを学びながら更新してください。

**覚えておいて**：
- テストが先、常に
- グリーンの時のみコミット
- 機能追加前に整理
- コピーではなく実装することで学ぶ

ハッピーコーディング！
