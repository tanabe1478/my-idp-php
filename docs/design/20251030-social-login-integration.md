# Phase 6: ソーシャルログイン統合 設計ドキュメント

## 作成日
2025-10-30

## 概要

このドキュメントは、OAuth2/OpenID Connect認可サーバーに外部IdP（Google、GitHub等）を使用したソーシャルログイン機能を追加する設計を定義します。

## 目的

- ユーザーが外部IdP（Google、GitHub）を使ってこの認可サーバーにログインできるようにする
- ローカルアカウントと外部アカウントを紐付ける
- 既存のOAuth2/OpenID Connect機能と統合する

## 要件

### 機能要件

1. **外部IdP対応**
   - Google OAuthでのログイン
   - GitHub OAuthでのログイン
   - 将来的に他のプロバイダーを追加可能な設計

2. **アカウント管理**
   - 初回ログイン時に新規ユーザーアカウントを作成
   - 既存ユーザーに外部アカウントをリンク
   - 複数の外部アカウントを1つのローカルアカウントにリンク可能
   - ローカルパスワード認証との併用

3. **セキュリティ**
   - CSRF保護
   - state パラメータによるリクエスト検証
   - アクセストークンの安全な保存（暗号化）
   - エラーハンドリング

### 非機能要件

1. **テスタビリティ**
   - ユニットテスト可能な設計
   - 統合テストでの動作確認

2. **保守性**
   - プロバイダー追加が容易な設計
   - 既存コードへの影響を最小限に

## アーキテクチャ

### コンポーネント構成

```
┌─────────────────┐
│  User Browser   │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│  UsersController                        │
│  - socialLogin()                        │
│  - socialCallback()                     │
└────────┬────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│  SocialAuthService                      │
│  - authenticate($provider, $code)       │
│  - getAuthorizationUrl($provider)       │
│  - linkAccount($userId, $socialAccount) │
└────────┬────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│  Provider Adapters                      │
│  - GoogleOAuthAdapter                   │
│  - GitHubOAuthAdapter                   │
│  Interface: SocialAuthProviderInterface │
└────────┬────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│  External OAuth Provider                │
│  - Google OAuth 2.0                     │
│  - GitHub OAuth                         │
└─────────────────────────────────────────┘
```

### データベーススキーマ

#### social_accounts テーブル（新規作成）

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | UUID | 主キー |
| user_id | UUID | 外部キー（users.id） |
| provider | VARCHAR(50) | プロバイダー名（google, github） |
| provider_user_id | VARCHAR(255) | プロバイダー側のユーザーID |
| email | VARCHAR(255) | プロバイダーから取得したメールアドレス |
| name | VARCHAR(255) | プロバイダーから取得した名前 |
| avatar_url | TEXT | プロバイダーから取得したアバターURL |
| access_token_encrypted | TEXT | 暗号化されたアクセストークン |
| refresh_token_encrypted | TEXT | 暗号化されたリフレッシュトークン（nullable） |
| expires_at | TIMESTAMP | トークンの有効期限（nullable） |
| raw_data | JSON | プロバイダーから取得した生データ |
| created | TIMESTAMP | 作成日時 |
| modified | TIMESTAMP | 更新日時 |

**インデックス:**
- UNIQUE(provider, provider_user_id)
- INDEX(user_id)
- INDEX(email)

#### users テーブル（変更なし）

既存のusersテーブルはそのまま使用。`password_hash`はNULL許可に変更を検討（ソーシャルログインのみのユーザー対応）。

## クラス設計

### 1. SocialAuthProviderInterface

```php
namespace App\Service\Social;

interface SocialAuthProviderInterface
{
    /**
     * Get authorization URL
     *
     * @param string $redirectUri Callback URL
     * @param array $options Additional options (scope, state, etc.)
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(string $redirectUri, array $options = []): string;

    /**
     * Get access token using authorization code
     *
     * @param string $code Authorization code
     * @param string $redirectUri Callback URL
     * @return array Token data [access_token, refresh_token, expires_in]
     */
    public function getAccessToken(string $code, string $redirectUri): array;

    /**
     * Get user profile using access token
     *
     * @param string $accessToken Access token
     * @return array User profile [id, email, name, avatar_url, raw]
     */
    public function getUserProfile(string $accessToken): array;

    /**
     * Get provider name
     *
     * @return string Provider name (google, github)
     */
    public function getProviderName(): string;
}
```

### 2. GoogleOAuthAdapter

```php
namespace App\Service\Social;

use League\OAuth2\Client\Provider\Google;

class GoogleOAuthAdapter implements SocialAuthProviderInterface
{
    private Google $provider;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->provider = new Google([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]);
    }

    // Interface implementation...
}
```

### 3. GitHubOAuthAdapter

```php
namespace App\Service\Social;

use League\OAuth2\Client\Provider\Github;

class GitHubOAuthAdapter implements SocialAuthProviderInterface
{
    private Github $provider;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->provider = new Github([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]);
    }

    // Interface implementation...
}
```

### 4. SocialAuthService

```php
namespace App\Service\Social;

use App\Model\Table\UsersTable;
use App\Model\Table\SocialAccountsTable;
use Cake\ORM\Locator\LocatorAwareTrait;

class SocialAuthService
{
    use LocatorAwareTrait;

    private array $providers = [];

    public function __construct()
    {
        // Initialize providers from config
    }

    /**
     * Get authorization URL for provider
     *
     * @param string $provider Provider name
     * @param string $redirectUri Callback URL
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(string $provider, string $redirectUri): string;

    /**
     * Authenticate user with provider
     *
     * @param string $provider Provider name
     * @param string $code Authorization code
     * @param string $redirectUri Callback URL
     * @return \App\Model\Entity\User User entity
     */
    public function authenticate(string $provider, string $code, string $redirectUri): User;

    /**
     * Link social account to existing user
     *
     * @param string $userId User ID
     * @param string $provider Provider name
     * @param array $profileData Profile data from provider
     * @return \App\Model\Entity\SocialAccount
     */
    public function linkAccount(string $userId, string $provider, array $profileData): SocialAccount;
}
```

### 5. SocialAccount Entity

```php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class SocialAccount extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'provider' => true,
        'provider_user_id' => true,
        'email' => true,
        'name' => true,
        'avatar_url' => true,
        'access_token_encrypted' => true,
        'refresh_token_encrypted' => true,
        'expires_at' => true,
        'raw_data' => true,
        'user' => true,
    ];

    protected array $_hidden = [
        'access_token_encrypted',
        'refresh_token_encrypted',
    ];

    /**
     * Encrypt and set access token
     */
    protected function _setAccessToken(string $token): string;

    /**
     * Decrypt and get access token
     */
    protected function _getAccessToken(): ?string;

    /**
     * Check if token is expired
     */
    public function isTokenExpired(): bool;
}
```

### 6. SocialAccountsTable

```php
namespace App\Model\Table;

use Cake\ORM\Table;

class SocialAccountsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('social_accounts');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator;

    public function buildRules(RulesChecker $rules): RulesChecker;

    /**
     * Find by provider and provider user ID
     */
    public function findByProviderUser(string $provider, string $providerUserId);
}
```

## API仕様

### エンドポイント

#### 1. GET /users/social-login/{provider}

ソーシャルログインを開始します。

**パラメータ:**
- `provider` (path): プロバイダー名（google, github）

**レスポンス:**
- 302 Redirect: プロバイダーの認可URLにリダイレクト

#### 2. GET /users/social-callback/{provider}

ソーシャルログインのコールバックを処理します。

**パラメータ:**
- `provider` (path): プロバイダー名（google, github）
- `code` (query): 認可コード
- `state` (query): CSRF保護用のstateパラメータ

**レスポンス:**
- 302 Redirect: ログイン成功後、ホームページまたはリダイレクトURLへ
- エラー時: エラーメッセージとともにログインページへ

## セキュリティ考慮事項

1. **CSRF保護**
   - `state`パラメータを使用してリクエストを検証
   - セッションに`state`を保存し、コールバック時に比較

2. **トークンの暗号化**
   - アクセストークンとリフレッシュトークンはデータベースに暗号化して保存
   - CakePHPの`Security::encrypt()`/`Security::decrypt()`を使用

3. **スコープの最小化**
   - 必要最小限のスコープのみをリクエスト
   - Google: `openid email profile`
   - GitHub: `user:email`

4. **エラーハンドリング**
   - プロバイダーからのエラーを適切に処理
   - ユーザーに有用なエラーメッセージを表示
   - セキュリティ上の詳細は隠蔽

## テスト方針

### 1. ユニットテスト

- SocialAuthProviderInterface実装のテスト（モック使用）
- SocialAuthServiceのロジックテスト
- SocialAccount Entity/Tableのテスト

### 2. 統合テスト

- ソーシャルログインフロー全体のテスト（モックプロバイダー使用）
- アカウントリンクのテスト
- エラーケースのテスト

### 3. 手動テスト

- Google OAuth実環境テスト
- GitHub OAuth実環境テスト

## 設定

### config/app_local.php

```php
'Social' => [
    'Google' => [
        'clientId' => env('GOOGLE_CLIENT_ID', ''),
        'clientSecret' => env('GOOGLE_CLIENT_SECRET', ''),
    ],
    'GitHub' => [
        'clientId' => env('GITHUB_CLIENT_ID', ''),
        'clientSecret' => env('GITHUB_CLIENT_SECRET', ''),
    ],
],
```

### .env

```
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
```

## 実装順序

1. データベースマイグレーション（social_accountsテーブル）
2. SocialAccount Entity/Table実装（TDD）
3. SocialAuthProviderInterface定義
4. GoogleOAuthAdapter実装（TDD）
5. GitHubOAuthAdapter実装（TDD）
6. SocialAuthService実装（TDD）
7. UsersController拡張（socialLogin, socialCallbackアクション）
8. ビュー作成（ソーシャルログインボタン追加）
9. 統合テスト作成
10. 手動テスト・動作確認

## 参照仕様

- [OAuth 2.0 (RFC 6749)](https://tools.ietf.org/html/rfc6749)
- [Google OAuth 2.0](https://developers.google.com/identity/protocols/oauth2)
- [GitHub OAuth](https://docs.github.com/en/developers/apps/building-oauth-apps/authorizing-oauth-apps)
- [league/oauth2-client](https://github.com/thephpleague/oauth2-client)

## 今後の拡張

- 他のプロバイダー追加（Facebook、Twitter、Microsoft等）
- アカウントリンク解除機能
- ソーシャルアカウント管理画面
- プロバイダーからのプロフィール情報自動更新
