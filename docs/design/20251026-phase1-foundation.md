# フェーズ1: 基盤 設計ドキュメント

## 設計日
2025-10-26

## 概要

OAuth2/OpenID Connect認可サーバーの基盤となる機能を実装する。このフェーズでは、クライアント管理、ユーザー認証、スコープ管理の3つの主要な機能を設計・実装する。

## 目標

1. **クライアント管理**: OAuth2クライアントアプリケーションの登録、保存、検証
2. **ユーザー認証**: リソースオーナー（ユーザー）の基本的な認証機能
3. **スコープ管理**: アクセス権限の範囲を定義・管理

## アーキテクチャ

### レイヤー構成

```
Controller Layer (薄く保つ)
    ↓
Service Layer (ビジネスロジック)
    ↓
Model Layer (ORM/Entity)
    ↓
Database (PostgreSQL)
```

### 主要コンポーネント

- **Entity**: データ構造とバリデーションルール
- **Table**: データベースクエリとリレーション
- **Service**: ビジネスロジックと複雑な操作
- **Controller**: HTTPリクエスト/レスポンス処理

---

## データベーススキーマ設計

### 1. Clientsテーブル

OAuth2クライアントアプリケーション情報を保存。

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | UUID | NOT NULL | gen_random_uuid() | 主キー |
| client_id | VARCHAR(255) | NOT NULL | - | クライアント識別子（外部公開） |
| client_secret | VARCHAR(255) | NULL | - | クライアントシークレット（機密クライアント用） |
| name | VARCHAR(255) | NOT NULL | - | クライアント名 |
| redirect_uris | TEXT | NOT NULL | - | リダイレクトURIのJSON配列 |
| grant_types | TEXT | NOT NULL | - | 許可するグラントタイプのJSON配列 |
| is_confidential | BOOLEAN | NOT NULL | false | 機密クライアントかどうか |
| is_active | BOOLEAN | NOT NULL | true | クライアントが有効かどうか |
| created | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 作成日時 |
| modified | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 更新日時 |

**制約**:
- UNIQUE: `client_id`
- INDEX: `client_id`, `is_active`

**セキュリティ考慮事項**:
- `client_secret`はハッシュ化して保存（bcrypt、cost=12）
- `client_id`は暗号学的に安全な乱数から生成（32文字）
- `redirect_uris`は厳密に検証（完全一致のみ許可）

---

### 2. Usersテーブル

リソースオーナー（ユーザー）の認証情報を保存。

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | UUID | NOT NULL | gen_random_uuid() | 主キー |
| username | VARCHAR(255) | NOT NULL | - | ユーザー名 |
| email | VARCHAR(255) | NOT NULL | - | メールアドレス |
| password_hash | VARCHAR(255) | NOT NULL | - | パスワードハッシュ |
| is_active | BOOLEAN | NOT NULL | true | ユーザーが有効かどうか |
| created | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 作成日時 |
| modified | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 更新日時 |

**制約**:
- UNIQUE: `username`, `email`
- INDEX: `username`, `email`, `is_active`

**セキュリティ考慮事項**:
- パスワードはbcryptでハッシュ化（cost=12）
- パスワードは平文で保存しない
- ログにパスワードを記録しない

---

### 3. Scopesテーブル

アクセス権限のスコープを定義。

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | UUID | NOT NULL | gen_random_uuid() | 主キー |
| name | VARCHAR(255) | NOT NULL | - | スコープ名（例: profile, email） |
| description | TEXT | NULL | - | スコープの説明 |
| is_active | BOOLEAN | NOT NULL | true | スコープが有効かどうか |
| created | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 作成日時 |
| modified | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 更新日時 |

**制約**:
- UNIQUE: `name`
- INDEX: `name`, `is_active`

**初期データ**:
- `openid`: OpenID Connectのベーススコープ
- `profile`: プロフィール情報へのアクセス
- `email`: メールアドレスへのアクセス

---

### 4. ClientsScopesテーブル（多対多の関係）

クライアントが利用可能なスコープを定義。

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | UUID | NOT NULL | gen_random_uuid() | 主キー |
| client_id | UUID | NOT NULL | - | 外部キー: clients.id |
| scope_id | UUID | NOT NULL | - | 外部キー: scopes.id |
| created | TIMESTAMP | NOT NULL | CURRENT_TIMESTAMP | 作成日時 |

**制約**:
- FOREIGN KEY: `client_id` REFERENCES `clients(id)` ON DELETE CASCADE
- FOREIGN KEY: `scope_id` REFERENCES `scopes(id)` ON DELETE CASCADE
- UNIQUE: (`client_id`, `scope_id`)
- INDEX: `client_id`, `scope_id`

---

## クラス設計

### Entity層

#### 1. Client Entity

```php
<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Client extends Entity
{
    protected array $_accessible = [
        'client_id' => true,
        'client_secret' => true,
        'name' => true,
        'redirect_uris' => true,
        'grant_types' => true,
        'is_confidential' => true,
        'is_active' => true,
        'scopes' => true,
        'created' => false,
        'modified' => false,
    ];

    protected array $_hidden = [
        'client_secret',
    ];

    // client_secretのハッシュ化は自動的に行われる（setter使用）
    protected function _setClientSecret(string $password): ?string
    {
        if (strlen($password) > 0) {
            return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }
        return null;
    }

    // redirect_urisのJSON処理
    protected function _getRedirectUris(?string $value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    protected function _setRedirectUris(array $value): string
    {
        return json_encode($value);
    }

    // grant_typesのJSON処理
    protected function _getGrantTypes(?string $value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    protected function _setGrantTypes(array $value): string
    {
        return json_encode($value);
    }

    // client_secretの検証
    public function verifySecret(string $secret): bool
    {
        return password_verify($secret, $this->client_secret);
    }
}
```

#### 2. User Entity

```php
<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class User extends Entity
{
    protected array $_accessible = [
        'username' => true,
        'email' => true,
        'password' => true,
        'is_active' => true,
        'created' => false,
        'modified' => false,
    ];

    protected array $_hidden = [
        'password_hash',
    ];

    // パスワードのハッシュ化
    protected function _setPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // パスワードの検証
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }
}
```

#### 3. Scope Entity

```php
<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Scope extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'description' => true,
        'is_active' => true,
        'created' => false,
        'modified' => false,
    ];
}
```

---

### Table層

#### 1. ClientsTable

```php
<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ClientsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('clients');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Scopes', [
            'foreignKey' => 'client_id',
            'targetForeignKey' => 'scope_id',
            'joinTable' => 'clients_scopes',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('client_id')
            ->maxLength('client_id', 255)
            ->requirePresence('client_id', 'create')
            ->notEmptyString('client_id')
            ->add('client_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('redirect_uris')
            ->requirePresence('redirect_uris', 'create')
            ->notEmptyString('redirect_uris');

        $validator
            ->scalar('grant_types')
            ->requirePresence('grant_types', 'create')
            ->notEmptyString('grant_types');

        $validator
            ->boolean('is_confidential')
            ->notEmptyString('is_confidential');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

        return $validator;
    }

    public function findByClientId(string $clientId)
    {
        return $this->find()
            ->where(['client_id' => $clientId])
            ->contain(['Scopes'])
            ->first();
    }
}
```

#### 2. UsersTable

```php
<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('username')
            ->maxLength('username', 255)
            ->requirePresence('username', 'create')
            ->notEmptyString('username')
            ->add('username', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email')
            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password')
            ->minLength('password', 8, 'パスワードは8文字以上である必要があります');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

        return $validator;
    }

    public function findByUsername(string $username)
    {
        return $this->find()
            ->where(['username' => $username, 'is_active' => true])
            ->first();
    }

    public function findByEmail(string $email)
    {
        return $this->find()
            ->where(['email' => $email, 'is_active' => true])
            ->first();
    }
}
```

#### 3. ScopesTable

```php
<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ScopesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('scopes');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Clients', [
            'foreignKey' => 'scope_id',
            'targetForeignKey' => 'client_id',
            'joinTable' => 'clients_scopes',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name')
            ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

        return $validator;
    }

    public function findByNames(array $names)
    {
        return $this->find()
            ->where(['name IN' => $names, 'is_active' => true])
            ->all();
    }
}
```

---

## セキュリティ考慮事項

### 1. パスワード・シークレット管理

- **ハッシュ化**: bcryptを使用、cost=12
- **保存**: 平文では絶対に保存しない
- **ログ**: パスワードやシークレットをログに記録しない
- **検証**: 定数時間比較を使用（`password_verify`が自動的に実行）

### 2. クライアント認証

- **client_id生成**: 暗号学的に安全な乱数（`random_bytes(16)`をhex変換）
- **client_secret生成**: 暗号学的に安全な乱数（`random_bytes(32)`をbase64変換）
- **機密性**: `is_confidential`フラグで公開クライアントと機密クライアントを区別

### 3. リダイレクトURI検証

- **完全一致**: リダイレクトURIは事前登録されたURIと完全一致する必要がある
- **ワイルドカード禁止**: セキュリティ上、ワイルドカードは使用しない
- **HTTPSのみ**: 本番環境ではHTTPSのみを許可（開発環境を除く）

### 4. 入力検証

- **すべての入力を検証**: ホワイトリスト方式で検証
- **型の厳格化**: PHPの型宣言を活用（strict_types=1）
- **SQLインジェクション対策**: ORMを使用し、パラメータ化クエリを使用

---

## テスト方針

### 1. ユニットテスト（PHPUnit）

**対象**:
- Entity: バリデーション、setter/getter、ビジネスロジック
- Table: カスタムファインダー、バリデーションルール
- Service: ビジネスロジック全般

**カバレッジ目標**: 90%以上

### 2. インテグレーションテスト

**対象**:
- データベース操作
- エンティティのリレーション
- トランザクション処理

### 3. テストデータ

**Fixture**を使用:
- 各テストは独立したデータセットを持つ
- テストごとにデータベースをリセット

### 4. テストの原則

- **TDDサイクル**: Red → Green → Refactor
- **1テスト1アサーション**: 可能な限り
- **明確なテスト名**: `shouldReturnClientWhenValidClientIdProvided`のような記述的な名前

---

## マイグレーション計画

### マイグレーション実行順序

1. `20251026_create_clients.php`
2. `20251026_create_users.php`
3. `20251026_create_scopes.php`
4. `20251026_create_clients_scopes.php`
5. `20251026_seed_initial_scopes.php`（シードデータ）

### ロールバック対応

- すべてのマイグレーションは`down()`メソッドを実装
- テスト環境でロールバックをテスト

---

## 実装タスク

### Phase 1.1: データベース

- [ ] Clientsテーブルマイグレーション作成
- [ ] Usersテーブルマイグレーション作成
- [ ] Scopesテーブルマイグレーション作成
- [ ] ClientsScopesテーブルマイグレーション作成
- [ ] 初期スコープデータのシード作成

### Phase 1.2: Model層

- [ ] Client Entityテスト作成（TDD）
- [ ] Client Entity実装
- [ ] ClientsTableテスト作成（TDD）
- [ ] ClientsTable実装
- [ ] User Entityテスト作成（TDD）
- [ ] User Entity実装
- [ ] UsersTableテスト作成（TDD）
- [ ] UsersTable実装
- [ ] Scope Entityテスト作成（TDD）
- [ ] Scope Entity実装
- [ ] ScopesTableテスト作成（TDD）
- [ ] ScopesTable実装

### Phase 1.3: Service層（次フェーズで実装）

- クライアント登録サービス
- ユーザー認証サービス
- スコープ検証サービス

---

## 参照仕様

- [RFC 6749 - OAuth 2.0 Authorization Framework](https://tools.ietf.org/html/rfc6749)
  - Section 2: Client Registration
  - Section 3.1.2: Redirection Endpoint
  - Section 3.3: Access Token Scope
- [RFC 7636 - PKCE](https://tools.ietf.org/html/rfc7636)
- [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html)
  - Section 5.4: Requesting Claims using Scope Values

---

## 変更履歴

### 2025-10-26: 初版作成
- データベーススキーマ設計
- Entity/Table設計
- セキュリティ考慮事項の文書化
- テスト方針の策定
