# プロジェクト進捗状況

## プロジェクト情報

- **プロジェクト名**: OAuth2/OpenID Connect 認可サーバー
- **開始日**: 2024-10-24
- **現在のフェーズ**: フェーズ2完了 - OAuth2認可コードフロー実装完了
- **最終更新**: 2025-10-27

---

## 実装フェーズ

### フェーズ1: 基盤 ✅

**ステータス**: 完了

**目標**:
- クライアント管理（登録、保存、検証）
- ユーザー認証（基本ログイン）
- スコープ管理

**タスク**:
- [x] データベーススキーマ設計
- [x] 初期マイグレーション実装（clients, scopes, clients_scopes, users）
- [x] Clientエンティティ作成（11テスト、全成功）
- [x] ClientsTable作成（11テスト、全成功）
- [x] Scopeエンティティ作成
- [x] ScopesTable作成
- [x] Userエンティティ作成（14テスト、全成功）
- [x] UsersTable作成（14テスト、全成功）
- [x] クライアント登録機能（8テスト、全成功）
- [x] クライアント認証機能（8テスト、全成功）
- [x] ユーザー認証機能（7テスト、全成功）

---

### フェーズ2: 認可コードフロー ✅

**ステータス**: 完了

**目標**:
- 認可エンドポイント実装
- トークンエンドポイント実装
- 認可コード生成と保存
- アクセストークン生成（JWT）
- トークン検証

**タスク**:
- [x] 認可エンドポイント設計
- [x] 認可コード生成ロジック
- [x] 認可コード保存
- [x] トークンエンドポイント設計
- [x] JWTエンコーダー実装
- [x] アクセストークン生成
- [x] トークン検証ロジック
- [x] 基本実装完了（手動テスト可能）
  - 統合テストは将来の改善として保留

---

### フェーズ3: トークン管理 ⏳

**ステータス**: 未開始

**目標**:
- リフレッシュトークン実装
- トークン取り消し
- トークンイントロスペクション

**タスク**:
- [ ] リフレッシュトークン生成
- [ ] リフレッシュトークン保存
- [ ] トークンローテーション
- [ ] トークン取り消しエンドポイント
- [ ] イントロスペクションエンドポイント

---

### フェーズ4: OpenID Connect 🔄

**ステータス**: 進行中（2025-10-27開始）

**目標**:
- IDトークン生成
- UserInfoエンドポイント
- ディスカバリードキュメント
- JWKSエンドポイント

**タスク**:
- [x] IDトークン生成ロジック（既に実装済み）
- [x] UserInfoエンドポイント実装（コード完成、テスト調整中）
- [ ] UserInfoテスト修正
- [ ] Discovery document生成
- [ ] JWKSエンドポイント実装

---

### フェーズ5: 追加フロー ⏳

**ステータス**: 未開始

**目標**:
- Client Credentialsグラント
- PKCE拡張
- 動的クライアント登録

**タスク**:
- [ ] Client Credentialsフロー実装
- [ ] PKCE検証ロジック
- [ ] 動的クライアント登録エンドポイント

---

## セットアップ進捗

### 環境構築

- [x] プロジェクトディレクトリ作成
- [x] 開発ガイドライン作成
- [x] プロジェクト固有CLAUDE.md作成
- [x] ドキュメント構造作成
- [x] libsodiumインストール
- [x] bisonインストール
- [x] PHP 8.3.27インストール
- [x] Composerインストール
- [x] CakePHP 5.xインストール
- [x] .gitignoreセットアップ
- [x] テスト環境セットアップ（PHPUnit動作確認済み）
- [x] PostgreSQLセットアップ
- [x] データベース作成（idp_development, idp_test）
- [x] データベース接続確認
- [x] 初期マイグレーション（clients, scopes, clients_scopes, users）

---

## 完了したマイルストーン

### 2024-10-24

#### ドキュメント整備
- ✅ `docs/DEVELOPMENT_GUIDE.md` 作成
- ✅ `.claude/CLAUDE.md` 作成
- ✅ `docs/PROGRESS.md` 作成（このファイル）

#### 開発環境
- ✅ asdfにPHPプラグイン追加
- ✅ 必要な依存関係インストール（libsodium, bison, imagemagick等）

### 2025-10-26

#### CakePHPセットアップ完了
- ✅ PHP 8.3.27のビルドとインストール完了
- ✅ 複数の依存関係解決（libsodium, bison, re2c, pkg-config, gd, libiconv）
- ✅ Composer 2.8.12インストール
- ✅ CakePHP 5.2.9インストール
- ✅ .gitignoreファイル作成
- ✅ PHPUnit 12.4.1動作確認（9テスト成功）

#### インストールされた主要コンポーネント
- PHP 8.3.27（主要な拡張機能付き）
  - bcmath, calendar, curl, gd, intl, pdo_pgsql, pgsql など
- Composer 2.8.12
- CakePHP 5.2.9
- PHPUnit 12.4.1
- PostgreSQL 14

#### PostgreSQLセットアップ完了
- ✅ PostgreSQL 14サービス起動
- ✅ 開発用データベース作成（idp_development）
- ✅ テスト用データベース作成（idp_test）
- ✅ CakePHP設定ファイル更新（config/app_local.php）
- ✅ データベース接続確認

#### フェーズ1実装開始（2025-10-27）
- ✅ データベーススキーマ設計完了
- ✅ 初期マイグレーション実装
  - clients テーブル（UUID主キー、JSON型フィールド）
  - scopes テーブル
  - clients_scopes 中間テーブル
  - users テーブル
- ✅ Client Entity実装（TDD: Red-Green-Refactor）
  - bcryptパスワードハッシング（cost=12）
  - JSON配列変換（redirect_uris, grant_types）
  - hidden fieldsサポート
  - 11テスト全成功
- ✅ ClientsTable実装（TDD: Red-Green-Refactor）
  - バリデーションルール（required, maxLength, unique）
  - belongsToMany Scopesアソシエーション
  - findByClientIdカスタムファインダー
  - JSON型設定（redirect_uris, grant_types）
  - 11テスト全成功
- ✅ Scope Entity実装
- ✅ ScopesTable実装
- ✅ テストフィクスチャ作成（Clients, Scopes, ClientsScopes）

#### User実装完了（2025-10-27）
- ✅ User Entity実装（TDD: Red-Green-Refactor）
  - bcryptパスワードハッシング（cost=12）
  - password → password_hashフィールドマッピング
  - virtualフィールドとhidden fieldsサポート
  - 14テスト全成功
- ✅ UsersTable実装（TDD: Red-Green-Refactor）
  - バリデーションルール（required, maxLength, unique, email形式）
  - buildRules（username, emailユニーク制約）
  - findByUsername()カスタムファインダー
  - findByEmail()カスタムファインダー
  - Timestampビヘイビア
  - 14テスト全成功
- ✅ テストフィクスチャ作成（Users）

**フェーズ1モデル層実装完了！**
- 合計テスト数: 36テスト、91アサーション、全成功 ✅

#### ユーザー認証機能実装完了（2025-10-27）
- ✅ CakePHP Authentication プラグインインストール（v3.3.2）
- ✅ Application.php に Authentication middleware 設定
  - Password identifier（password_hashフィールド使用）
  - Form authenticator
  - Session authenticator
- ✅ UsersController実装（TDD: Red-Green-Refactor）
  - login()アクション（ユーザー名/パスワード認証）
  - logout()アクション（セッションクリア）
  - 非アクティブユーザーチェック
  - リダイレクトパラメータ処理
  - 7統合テスト全成功
- ✅ ログインビュー作成（templates/Users/login.php）
- ✅ ユーザー一覧ビュー作成（templates/Users/index.php）
- ✅ PagesController 公開アクセス設定
- ✅ UsersFixture パスワードハッシュ修正

**全テスト成功！**
- 合計テスト数: 53テスト、131アサーション、全成功（1スキップ）✅

#### クライアント登録機能実装完了（2025-10-27）
- ✅ ClientsController実装（TDD: Red-Green-Refactor）
  - add()アクション（クライアント登録）
  - view()アクション（クライアント詳細表示）
  - index()アクション（クライアント一覧）
  - 認証必須（ログインユーザーのみアクセス可能）
  - 8統合テスト全成功
- ✅ client_id自動生成（cryptographically secure）
  - `bin2hex(random_bytes(16))` - 32文字hex文字列
- ✅ client_secret自動生成（cryptographically secure）
  - `bin2hex(random_bytes(32))` - 64文字hex文字列
  - bcryptでハッシュ化（cost=12）
- ✅ クライアント登録フォームビュー作成（templates/Clients/add.php）
  - 名前、リダイレクトURI、グラントタイプ入力
  - テキストエリアからJSON配列への自動変換
- ✅ クライアント詳細ビュー作成（templates/Clients/view.php）
- ✅ クライアント一覧ビュー作成（templates/Clients/index.php）
- ✅ Client Entityのゲッター修正
  - JSON文字列→配列の自動変換対応（Fixtureとの互換性）
- ✅ バリデーション
  - 名前必須
  - リダイレクトURI配列必須
  - グラントタイプ配列必須

**全テスト成功！**
- 合計テスト数: 61テスト、164アサーション、全成功（1スキップ）✅

#### クライアント認証機能実装完了（2025-10-27）
- ✅ ClientAuthenticationService実装（TDD: Red-Green-Refactor）
  - authenticate()メソッド（client_id/client_secret認証）
  - password_verify()によるsecret検証
  - Confidentialクライアント認証（secret必須）
  - Publicクライアント認証（secretなし）
  - is_activeチェック
  - Scopesとの関連も含めて取得
  - 8テスト全成功
- ✅ ClientsFixture修正
  - 正しいbcryptハッシュ生成（'secret'に対応）
  - test_client_1, test_client_3のハッシュ更新

**全テスト成功！**
- 合計テスト数: 69テスト、183アサーション、全成功（1スキップ）✅

**フェーズ1完了！** 🎉
- クライアント管理 ✅
- ユーザー認証 ✅
- スコープ管理 ✅

#### フェーズ2: OAuth2認可コードフロー実装（2025-10-27）
- ✅ authorization_codesテーブル作成
  - マイグレーション実行成功
  - UUID主キー、外部キー（clients, users）
  - JSON型フィールド（scopes）
  - 有効期限管理（expires_at）
  - 使用済みフラグ（is_used）
- ✅ AuthorizationCode Entity実装
  - scopesゲッター（JSON→配列変換）
  - isExpired()メソッド
  - isValid()メソッド（有効性チェック）
- ✅ AuthorizationCodesTable実装
  - バリデーションルール
  - findByCode()カスタムファインダー
  - cleanupExpired()メソッド
- ✅ OauthController実装
  - GET /oauth/authorize - 認可リクエスト処理
  - POST /oauth/authorize (consent) - ユーザー承認処理
  - POST /oauth/token - トークンエンドポイント
  - クライアント認証統合
  - 認可コード生成（cryptographically secure）
  - redirect_uri検証
- ✅ JwtService実装（firebase/php-jwt v6.11.1）
  - generateAccessToken() - アクセストークン生成
  - generateIdToken() - OpenID Connect IDトークン生成
  - verifyToken() - トークン検証
  - HS256アルゴリズム使用
- ✅ 同意画面ビュー作成（templates/Oauth/authorize.php）
  - クライアント情報表示
  - 要求スコープ表示
  - 承認/拒否ボタン

**統合テスト完成！**
- ✅ OauthControllerTest実装（6統合テスト）
  - testAuthorizeShowsConsentScreen - 同意画面表示確認
  - testAuthorizeIsAccessible - エンドポイントアクセス確認
  - testAuthorizeWithInvalidClientId - エラーハンドリング確認
  - testTokenEndpointRequiresPost - POST必須確認
  - testTokenEndpointWithMissingParameters - パラメータ検証
  - testTokenEndpointWithInvalidGrantType - グラントタイプ検証
- ✅ 主要な修正
  - loadModel() → fetchTable()への移行（CakePHP 5対応）
  - CSRF保護のOAuthエンドポイント除外設定
  - ClientsFixtureにテスト用リダイレクトURI追加

**全テスト成功！**
- 合計テスト数: 75テスト、198アサーション、全成功（1スキップ）✅
- リグレッションなし

**実装完了したOAuth2フロー**:
1. ユーザーがクライアントアプリから認可リクエスト
2. 認可サーバーがユーザーをログインページにリダイレクト
3. ユーザーログイン後、同意画面表示
4. ユーザーが承認すると認可コード生成
5. クライアントアプリにリダイレクト（認可コード付き）
6. クライアントがトークンエンドポイントで認可コード交換
7. アクセストークン（JWT）とIDトークン発行

**フェーズ2完了！** 🎉

#### フェーズ3: トークン管理実装完了（2025-10-27）
- ✅ refresh_tokensテーブルマイグレーション実行
  - UUID主キー、外部キー（clients, users）
  - JSON型フィールド（scopes）
  - 有効期限管理（expires_at）
  - 失効フラグ（is_revoked）
- ✅ RefreshToken Entity実装（TDD: Red-Green-Refactor）
  - scopesゲッター（JSON→配列変換）
  - isExpired()メソッド
  - isValid()メソッド（有効性チェック）
  - 8テスト全成功
- ✅ RefreshTokensTable実装（TDD: Red-Green-Refactor）
  - バリデーションルール
  - findByToken()カスタムファインダー
  - cleanupExpired()メソッド
  - revokeByToken()メソッド
  - 7テスト全成功
- ✅ authorization_codeフローにリフレッシュトークン発行追加
  - 30日間の有効期限
  - cryptographically secure（64文字hex文字列）
  - アクセストークンとともに返却
- ✅ refresh_tokenグラントタイプ実装
  - リフレッシュトークンでアクセストークン再取得
  - クライアント認証
  - トークン有効性検証（期限切れ、失効済み）
  - スコープ維持
  - 4統合テスト全成功
- ✅ トークンローテーション実装（セキュリティ機能）
  - リフレッシュトークン使用時に新トークン発行
  - 旧トークン自動失効
  - リプレイ攻撃防止

**全テスト成功！**
- 合計テスト数: 95テスト、257アサーション、全成功（1スキップ）✅
- リグレッションなし

**フェーズ3完了！** 🎉

#### フェーズ4: OpenID Connect実装（進行中 - 2025-10-27）
- ✅ IDトークン生成確認
  - JwtService::generateIdToken()は既に実装済み
  - openidスコープでIDトークンを自動発行
- ✅ UserInfoエンドポイント実装（部分的）
  - GET /oauth/userinfo エンドポイント作成
  - アクセストークン検証（JWT）
  - スコープベースのクレーム返却（sub, preferred_username, email）
  - クエリパラメータとAuthorizationヘッダーの両方をサポート
- ✅ JwtService改善
  - Security.saltを使用した一貫性のある秘密鍵管理
  - コントローラーでの共通インスタンス化
- 🔄 UserInfoエンドポイントテスト（作業中）
  - 3つのテストケース追加（1つ成功、2つ失敗）
  - 統合テストでのヘッダー設定方法を調査中
  - **課題**: 複数リクエスト間でのJWT検証

**現在の状況**:
- 実装: UserInfoエンドポイントのコード完成
- テスト: 98テスト中3つ失敗（全てUserInfo関連の新規テスト）
- 問題: 統合テストでの複数リクエスト処理とJWT秘密鍵の一貫性

**次のアクション**:
1. UserInfoテストの修正（Authorizationヘッダー設定）
2. 全テスト通過確認
3. Discoveryドキュメントエンドポイント実装
4. JWKSエンドポイント実装

---

### フェーズ3: トークン管理 ✅

**ステータス**: 完了（2025-10-27）

**目標**:
- リフレッシュトークン実装
- トークン取り消し
- トークンイントロスペクション

**タスク**:
- [x] refresh_tokensテーブル作成
- [x] RefreshToken Entity/Table実装（15テスト、全成功）
- [x] リフレッシュトークン生成・保存
- [x] refresh_tokenグラントタイプ実装
- [x] トークンローテーション実装
- [ ] トークン取り消しエンドポイント（将来の拡張）
- [ ] イントロスペクションエンドポイント（将来の拡張）

---

## 次のステップ

1. ~~クライアント登録機能実装~~ ✅ **完了**
2. ~~クライアント認証機能実装~~ ✅ **完了**
3. ~~ユーザー認証機能実装~~ ✅ **完了**
4. ~~フェーズ2（認可コードフロー）~~ ✅ **完了**
5. ~~フェーズ3（トークン管理）~~ ✅ **完了**
6. フェーズ4（OpenID Connect - IDトークン、UserInfo、Discovery）に移行 🚀
7. 統合テストの改善（将来のタスク）

---

## ブロッカーと課題

### 現在のブロッカー
- なし

### 技術的課題
- OAuth2/OpenID Connectの仕様理解
- セキュリティベストプラクティスの習得
- JWT実装の詳細

---

## 学習メモ

### 学んだこと
- asdf-phpのインストールにはlibsodium、bison等の依存関係が必要
- macOSのデフォルトbisonは古いバージョン（2.3）でPHP 8.3のビルドには不適
- PHPビルド時には複数のHomebrew keg-onlyパッケージへのパスを設定する必要がある
- PKG_CONFIG_PATHに明示的にライブラリパスを追加することで、configureが依存関係を見つけられる
- CakePHP 5.xはPHPUnit 12を使用し、デフォルトで9個のテストを含む
- asdfでのPHPビルドは時間がかかるが（約40分）、完全な制御と学習の機会を提供

### 参考にしたリソース
- [asdf-php GitHub Issues](https://github.com/asdf-community/asdf-php/issues)
- PHP 8.3リリースノート

---

## 凡例

- ✅ 完了
- 🔄 進行中
- ⏳ 未開始
- ❌ ブロック中
- 📝 レビュー待ち
