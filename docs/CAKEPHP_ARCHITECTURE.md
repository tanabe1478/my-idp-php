# CakePHPアーキテクチャガイド

このドキュメントは、CakePHP初心者がこのプロジェクトのコードを理解するためのガイドです。

## 目次

1. [CakePHPの基本概念](#cakephpの基本概念)
2. [プロジェクト構造](#プロジェクト構造)
3. [リクエストフロー](#リクエストフロー)
4. [主要なコンポーネント](#主要なコンポーネント)
5. [このプロジェクトの実装](#このプロジェクトの実装)
6. [テスト構造](#テスト構造)
7. [便利な規約](#便利な規約)

---

## CakePHPの基本概念

### MVC（Model-View-Controller）パターン

CakePHPは**MVC**アーキテクチャを採用しています：

```
ブラウザ → Router → Controller → Model → Database
                        ↓
                      View
                        ↓
                    ブラウザ
```

- **Model（モデル）**: データベースとのやりとりを担当
- **View（ビュー）**: HTMLの表示を担当
- **Controller（コントローラー）**: リクエストを受けて、ModelとViewを制御

### Convention over Configuration（設定より規約）

CakePHPは**命名規約**に従うことで、設定なしで動作します：

| 要素 | 規約 | 例 |
|------|------|-----|
| テーブル名 | 複数形、スネークケース | `users`, `clients` |
| モデル（Table） | 単数形、パスカルケース + Table | `UsersTable`, `ClientsTable` |
| モデル（Entity） | 単数形、パスカルケース | `User`, `Client` |
| コントローラー | 複数形、パスカルケース + Controller | `UsersController` |
| ビュー | コントローラー名/アクション名 | `Users/login.php` |

---

## プロジェクト構造

```
idp-php/
├── bin/                        # 実行可能スクリプト
│   ├── cake                    # CakePHP CLI
│   └── start-dev.sh           # 開発サーバー起動スクリプト
│
├── config/                     # 設定ファイル
│   ├── app.php                # アプリケーション設定
│   ├── app_local.php          # ローカル設定（DB接続など）
│   ├── bootstrap.php          # 初期化処理
│   └── routes.php             # ルーティング定義
│
├── src/                        # アプリケーションコード
│   ├── Application.php        # アプリケーションクラス（重要！）
│   ├── Controller/            # コントローラー
│   │   ├── AppController.php # ベースコントローラー
│   │   ├── UsersController.php
│   │   └── PagesController.php
│   │
│   ├── Model/                 # モデル
│   │   ├── Entity/            # エンティティ（データオブジェクト）
│   │   │   ├── User.php
│   │   │   ├── Client.php
│   │   │   └── Scope.php
│   │   │
│   │   └── Table/             # テーブルクラス（DB操作）
│   │       ├── UsersTable.php
│   │       ├── ClientsTable.php
│   │       └── ScopesTable.php
│   │
│   └── View/                  # ビューヘルパー（カスタム）
│
├── templates/                  # ビューテンプレート
│   ├── layout/                # レイアウト
│   │   └── default.php        # デフォルトレイアウト
│   ├── Users/                 # Usersコントローラー用
│   │   ├── login.php
│   │   └── index.php
│   └── Pages/                 # Pagesコントローラー用
│       └── home.php
│
├── tests/                      # テストコード
│   ├── Fixture/               # テストデータ
│   │   ├── UsersFixture.php
│   │   └── ClientsFixture.php
│   │
│   └── TestCase/              # テストケース
│       ├── Controller/        # コントローラーのテスト
│       └── Model/             # モデルのテスト
│           ├── Entity/
│           └── Table/
│
├── webroot/                    # 公開ディレクトリ
│   ├── index.php              # エントリーポイント
│   ├── css/
│   └── js/
│
└── docs/                       # ドキュメント
    ├── DEVELOPMENT_GUIDE.md
    ├── PROGRESS.md
    └── CAKEPHP_ARCHITECTURE.md  # このファイル
```

---

## リクエストフロー

### 例: ログインリクエスト (`POST /users/login`)

```
1. webroot/index.php
   ↓ エントリーポイント

2. src/Application.php
   ↓ Middlewareスタックを通過
   ↓ - ErrorHandlerMiddleware
   ↓ - RoutingMiddleware
   ↓ - AuthenticationMiddleware ← 認証チェック
   ↓ - CsrfProtectionMiddleware

3. config/routes.php
   ↓ ルーティング: /users/login → UsersController::login()

4. src/Controller/UsersController.php
   ↓ login()メソッド実行
   ↓ - $this->Authentication で認証処理
   ↓ - $this->Flash でフラッシュメッセージ
   ↓ - $this->redirect() でリダイレクト

5. src/Model/Table/UsersTable.php
   ↓ データベースからユーザー検索

6. src/Model/Entity/User.php
   ↓ ユーザーデータをオブジェクト化
   ↓ パスワード検証

7. templates/Users/login.php
   ↓ ビューレンダリング（ログイン成功時はスキップ）

8. templates/layout/default.php
   ↓ レイアウト適用

9. ブラウザに返却
```

---

## 主要なコンポーネント

### 1. Application.php

**場所**: `src/Application.php`

**役割**: アプリケーション全体の初期化と設定

```php
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    // Middlewareスタックの定義
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // 各Middlewareを順番に登録
        $middlewareQueue
            ->add(new ErrorHandlerMiddleware(...))
            ->add(new RoutingMiddleware(...))
            ->add(new AuthenticationMiddleware($this))  // 認証
            ->add(new CsrfProtectionMiddleware(...));   // CSRF対策

        return $middlewareQueue;
    }

    // 認証サービスの設定
    public function getAuthenticationService(...)
    {
        // Password identifier: username/password_hash で認証
        // Session authenticator: セッション維持
        // Form authenticator: ログインフォーム処理
    }
}
```

**重要ポイント**:
- すべてのリクエストはここで定義されたMiddlewareを通過
- 認証の設定もここで行う

---

### 2. Controller（コントローラー）

**場所**: `src/Controller/`

#### AppController.php - ベースコントローラー

```php
class AppController extends Controller
{
    public function initialize(): void
    {
        parent::initialize();

        // すべてのコントローラーで使えるコンポーネントをロード
        $this->loadComponent('Flash');              // フラッシュメッセージ
        $this->loadComponent('Authentication.Authentication');  // 認証
    }
}
```

#### UsersController.php - ユーザーコントローラー

```php
class UsersController extends AppController
{
    // ログイン不要なアクションを設定
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login']);
    }

    // アクション: /users/login
    public function login()
    {
        // POSTリクエスト時
        if ($this->request->is('post')) {
            $result = $this->Authentication->getResult();

            if ($result && $result->isValid()) {
                // 認証成功 → リダイレクト
                return $this->redirect(['action' => 'index']);
            } else {
                // 認証失敗 → エラーメッセージ
                $this->Flash->error('Invalid username or password');
            }
        }
        // GETリクエスト時 → ログインフォーム表示
    }
}
```

**重要ポイント**:
- メソッド名 = アクション名 = URL
- `$this->request`: リクエスト情報
- `$this->Flash`: フラッシュメッセージ
- `$this->Authentication`: 認証処理
- `$this->redirect()`: リダイレクト

---

### 3. Model（モデル）

CakePHPのModelは2つのクラスで構成されます：

#### Entity（エンティティ） - データオブジェクト

**場所**: `src/Model/Entity/User.php`

```php
class User extends Entity
{
    // 隠しフィールド（JSONに含めない）
    protected array $_hidden = [
        'password',
        'password_hash',
    ];

    // Virtual field: password → password_hash に変換
    protected function _setPassword(string $password): ?string
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher())->hash($password);
        }
    }

    // アクセサー: $user->username でアクセス可能
}
```

**重要ポイント**:
- データの1行を表すオブジェクト
- `_set<Field>()`: セッター（データ保存時に自動実行）
- `_get<Field>()`: ゲッター（データ取得時に自動実行）
- `$_hidden`: JSONに含めないフィールド

#### Table（テーブルクラス） - DB操作

**場所**: `src/Model/Table/UsersTable.php`

```php
class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // テーブル設定
        $this->setTable('users');
        $this->setPrimaryKey('id');

        // ビヘイビア（自動処理）
        $this->addBehavior('Timestamp');  // created, modified自動更新

        // アソシエーション（リレーション）
        // $this->belongsTo('Clients');
        // $this->hasMany('Posts');
    }

    // バリデーションルール
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('username', 'Username is required')
            ->maxLength('username', 100)
            ->email('email');

        return $validator;
    }

    // データベースルール（ユニーク制約など）
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['username']));
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }

    // カスタムファインダー
    public function findByUsername(SelectQuery $query, string $username)
    {
        return $query->where(['username' => $username]);
    }
}
```

**重要ポイント**:
- `validationDefault()`: データのバリデーション
- `buildRules()`: データベースレベルの制約
- `find()`: データ検索の基本メソッド
- カスタムファインダー: `find('byUsername', ['username' => 'test'])`

---

### 4. View（ビュー）

**場所**: `templates/Users/login.php`

```php
<!-- フラッシュメッセージ表示 -->
<?= $this->Flash->render() ?>

<h3>Login</h3>

<!-- フォーム作成 -->
<?= $this->Form->create() ?>
<fieldset>
    <!-- 入力フィールド -->
    <?= $this->Form->control('username', ['required' => true]) ?>
    <?= $this->Form->control('password', ['required' => true]) ?>
</fieldset>
<?= $this->Form->submit(__('Login')); ?>
<?= $this->Form->end() ?>
```

**重要ポイント**:
- `$this->Form`: フォームヘルパー（自動CSRF対策）
- `$this->Flash`: フラッシュメッセージ表示
- `$this->Html`: HTMLヘルパー
- コントローラーで`$this->set('key', $value)`した値にアクセス可能

---

## このプロジェクトの実装

### 現在実装されている機能

#### 1. ユーザー認証

**関連ファイル**:
```
src/Application.php              ← 認証Middleware設定
src/Controller/UsersController.php  ← login(), logout()
src/Model/Entity/User.php        ← パスワードハッシング
src/Model/Table/UsersTable.php   ← ユーザー検索
templates/Users/login.php        ← ログインフォーム
```

**フロー**:
1. ユーザーが `/users/login` にアクセス
2. `UsersController::login()` が実行
3. POSTの場合、`AuthenticationMiddleware` が認証処理
4. `UsersTable` でユーザー検索
5. `User` Entity でパスワード検証
6. 成功時: セッション保存 → リダイレクト
7. 失敗時: エラーメッセージ表示

#### 2. クライアント管理（モデルのみ）

**関連ファイル**:
```
src/Model/Entity/Client.php      ← クライアント情報
src/Model/Table/ClientsTable.php ← クライアント検索
```

**機能**:
- クライアントID/シークレットの管理
- リダイレクトURI、グラントタイプの保存
- まだコントローラーは未実装

#### 3. スコープ管理（モデルのみ）

**関連ファイル**:
```
src/Model/Entity/Scope.php
src/Model/Table/ScopesTable.php
```

---

## テスト構造

### Fixture（テストデータ）

**場所**: `tests/Fixture/UsersFixture.php`

```php
class UsersFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'username' => 'testuser1',
                'email' => 'testuser1@example.com',
                'password_hash' => '$2y$12$...',  // password123
                'is_active' => true,
            ],
            // ...
        ];
        parent::init();
    }
}
```

**役割**: テスト用のダミーデータ

### TestCase（テストケース）

#### 統合テスト（コントローラー）

**場所**: `tests/TestCase/Controller/UsersControllerTest.php`

```php
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Users',  // UsersFixtureを使用
    ];

    public function testLoginWithValidCredentials(): void
    {
        // POSTリクエストを送信
        $this->post('/users/login', [
            'username' => 'testuser1',
            'password' => 'password123',
        ]);

        // アサーション
        $this->assertResponseSuccess();
        $this->assertRedirect(['controller' => 'Users', 'action' => 'index']);
        $this->assertSession('testuser1', 'Auth.username');
    }
}
```

#### ユニットテスト（モデル）

**場所**: `tests/TestCase/Model/Table/UsersTableTest.php`

```php
class UsersTableTest extends TestCase
{
    protected array $fixtures = ['app.Users'];

    public function testFindByUsername(): void
    {
        $user = $this->Users->find('byUsername', ['username' => 'testuser1'])
            ->first();

        $this->assertNotNull($user);
        $this->assertSame('testuser1', $user->username);
    }
}
```

---

## 便利な規約

### 1. 自動ロード

**規約**:
```
クラス名: App\Controller\UsersController
↓
ファイル: src/Controller/UsersController.php
```

命名規約に従えば、`require`不要で自動ロードされます。

### 2. アソシエーション（リレーション）

**規約に基づく自動検出**:

```php
// UsersTable に以下を追加すると...
$this->hasMany('Posts');

// こう使える
$user = $this->Users->get($id, contain: ['Posts']);
echo $user->posts[0]->title;  // 自動でJOIN
```

### 3. Inflection（語形変換）

CakePHPは自動で単数形⇔複数形を変換：

```
users (テーブル) → User (Entity) → UsersTable (Table)
clients → Client → ClientsTable
```

### 4. マジックメソッド

#### find<Type>()

```php
// 定義
public function findActive(SelectQuery $query) { ... }

// 使い方
$this->Users->find('active');
```

#### _set<Field>(), _get<Field>()

```php
// Entityに定義
protected function _setPassword($value) {
    return bcrypt($value);
}

// 自動実行される
$user->password = 'plain';  // _setPassword()が自動実行
// $user->password_hash に bcrypt された値が入る
```

---

## デバッグ方法

### 1. デバッグ関数

```php
// 変数の内容を表示して停止
debug($user);
die;

// または
dd($user);  // dump and die
```

### 2. ログ出力

```php
use Cake\Log\Log;

Log::debug('User found: ' . $user->username);
Log::error('Login failed for: ' . $username);
```

ログは `logs/debug.log`, `logs/error.log` に出力されます。

### 3. DebugKit

すでにインストール済み（開発環境）。
ブラウザ右下に表示されるツールバーで、以下を確認できます：
- 実行されたSQL
- リクエスト/レスポンス情報
- セッション情報
- 実行時間

---

## 次に読むべきファイル

コードリーディングの推奨順序：

1. **`src/Application.php`**
   → アプリケーション全体の構造を理解

2. **`src/Controller/AppController.php`**
   → すべてのコントローラーの基底クラス

3. **`src/Controller/UsersController.php`**
   → 実際の機能実装を理解

4. **`src/Model/Entity/User.php`**
   → データオブジェクトの構造

5. **`src/Model/Table/UsersTable.php`**
   → データベース操作の方法

6. **`tests/TestCase/Controller/UsersControllerTest.php`**
   → 機能のテスト方法と期待される動作

7. **`templates/Users/login.php`**
   → ビューの書き方

---

## 参考リンク

- [CakePHP 5.x 公式ドキュメント](https://book.cakephp.org/5/en/index.html)
- [CakePHP Cookbook 日本語版](https://book.cakephp.org/5/ja/index.html)
- [CakePHP Authentication Plugin](https://book.cakephp.org/authentication/3/en/index.html)

---

## 質問がある場合

各ファイルには詳細なコメントが記載されています。
不明点があれば、該当ファイルを開いて実装を確認してください。

特に以下のファイルは参考になります：
- `src/Controller/UsersController.php` - 認証フローの実装
- `src/Model/Entity/User.php` - パスワードハッシングの実装
- `tests/TestCase/Controller/UsersControllerTest.php` - 統合テストの書き方
