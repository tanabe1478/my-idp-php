# ソーシャルログイン統合テスト手順書

このドキュメントでは、実際のブラウザを使用してソーシャルログイン機能を統合テストする手順を説明します。

## 前提条件

- [x] Google および GitHub の OAuth アプリケーションが設定済み（`docs/SOCIAL_LOGIN_SETUP.md` 参照）
- [x] `config/app_local.php` に認証情報が設定済み
- [x] データベースマイグレーションが実行済み
- [x] 開発サーバーが起動中（`./bin/start-dev.sh`）

---

## テストケース一覧

### 1. 基本動作テスト
- [ ] 1-1. Google でログイン（新規ユーザー）
- [ ] 1-2. Google でログイン（既存ユーザー）
- [ ] 1-3. GitHub でログイン（新規ユーザー）
- [ ] 1-4. GitHub でログイン（既存ユーザー）

### 2. エラーハンドリングテスト
- [ ] 2-1. ユーザーが認証をキャンセル
- [ ] 2-2. 無効なプロバイダでアクセス
- [ ] 2-3. State パラメータの不一致（CSRF保護）

### 3. ユーザー管理テスト
- [ ] 3-1. 同じメールアドレスで異なるプロバイダからログイン
- [ ] 3-2. トークン更新の確認
- [ ] 3-3. ユーザー情報の取得

---

## テストケース詳細

## 1. 基本動作テスト

### 1-1. Google でログイン（新規ユーザー）

**目的:** Google アカウントで初めてログインする際、新規ユーザーとソーシャルアカウントが作成されることを確認

**手順:**

1. **準備:** ブラウザのシークレットモードを開く

2. **ログインページにアクセス**
   ```
   http://localhost:8765/users/login
   ```

3. **Google ログインボタンをクリック**
   - 青い「Sign in with Google」ボタンをクリック

4. **Google 認証画面で操作**
   - Google アカウントでログイン（テストユーザーとして登録したアカウント）
   - 「このアプリは確認されていません」が表示されたら:
     - 「詳細」→「{アプリ名}（安全ではないページ）に移動」をクリック
   - 同意画面で「許可」をクリック

5. **リダイレクトを確認**
   - `http://localhost:8765/users/callback/google?code=...&state=...` にリダイレクト
   - その後 `http://localhost:8765/users/index` にリダイレクト
   - Flash メッセージ: "Welcome back!" が表示される

6. **データベースを確認**
   ```bash
   # 新規ユーザーが作成されているか確認
   psql -d idp_development -c "SELECT id, username, email, password_hash FROM users ORDER BY created DESC LIMIT 1;"
   ```

   **期待結果:**
   - 新しいユーザーレコードが作成されている
   - `username`: Google のメールアドレスから生成（例: `john.doe123`）
   - `email`: Google アカウントのメールアドレス
   - `password_hash`: NULL（ソーシャルログインユーザー）

7. **ソーシャルアカウントを確認**
   ```bash
   psql -d idp_development -c "SELECT provider, provider_user_id, email, name FROM social_accounts ORDER BY created DESC LIMIT 1;"
   ```

   **期待結果:**
   - 新しいソーシャルアカウントレコードが作成されている
   - `provider`: `google`
   - `provider_user_id`: Google のユーザーID
   - `email`: Google のメールアドレス
   - `name`: Google のアカウント名

**成功条件:**
- [x] ログインが成功し、ユーザーインデックスページが表示される
- [x] users テーブルに新規ユーザーが作成される（password_hash は NULL）
- [x] social_accounts テーブルに Google アカウント情報が保存される
- [x] access_token と refresh_token が暗号化されて保存される

---

### 1-2. Google でログイン（既存ユーザー）

**目的:** 既に登録済みの Google アカウントで再度ログインできることを確認

**手順:**

1. **準備:** テストケース 1-1 を完了し、ユーザーが作成されている状態

2. **ログアウト**
   ```
   http://localhost:8765/users/logout
   ```

3. **再度ログインページにアクセス**
   ```
   http://localhost:8765/users/login
   ```

4. **Google ログインボタンをクリック**

5. **Google 認証画面で操作**
   - 同じ Google アカウントを選択（既にログイン済みの場合、自動的に進む可能性あり）

6. **リダイレクトを確認**
   - ログインに成功し、ユーザーインデックスページが表示される

7. **データベースを確認**
   ```bash
   # ユーザー数が増えていないことを確認
   psql -d idp_development -c "SELECT COUNT(*) FROM users;"

   # ソーシャルアカウントのトークンが更新されていることを確認
   psql -d idp_development -c "SELECT provider, email, modified FROM social_accounts WHERE provider = 'google' ORDER BY modified DESC LIMIT 1;"
   ```

**期待結果:**
- 新しいユーザーは作成されない（既存ユーザーでログイン）
- ソーシャルアカウントの `modified` タイムスタンプが更新されている
- トークンが新しいものに更新されている

**成功条件:**
- [x] ログインが成功する
- [x] 新規ユーザーが作成されない
- [x] access_token と refresh_token が更新される

---

### 1-3. GitHub でログイン（新規ユーザー）

**目的:** GitHub アカウントで初めてログインする際、新規ユーザーとソーシャルアカウントが作成されることを確認

**手順:**

1. **準備:** 新しいシークレットウィンドウを開く

2. **ログインページにアクセス**
   ```
   http://localhost:8765/users/login
   ```

3. **GitHub ログインボタンをクリック**
   - 黒い「Sign in with GitHub」ボタンをクリック

4. **GitHub 認証画面で操作**
   - GitHub アカウントでログイン
   - 初回アクセス時は「Authorize {アプリ名}」ボタンをクリック

5. **リダイレクトを確認**
   - `http://localhost:8765/users/callback/github?code=...&state=...` にリダイレクト
   - その後 `http://localhost:8765/users/index` にリダイレクト
   - Flash メッセージ: "Welcome back!" が表示される

6. **データベースを確認**
   ```bash
   # 新規ユーザーが作成されているか確認
   psql -d idp_development -c "SELECT id, username, email, password_hash FROM users ORDER BY created DESC LIMIT 1;"

   # ソーシャルアカウントを確認
   psql -d idp_development -c "SELECT provider, provider_user_id, email, name FROM social_accounts WHERE provider = 'github' ORDER BY created DESC LIMIT 1;"
   ```

**期待結果:**
- 新しいユーザーレコードが作成されている
- `provider`: `github`
- トークンが暗号化されて保存されている

**成功条件:**
- [x] ログインが成功する
- [x] users テーブルに新規ユーザーが作成される
- [x] social_accounts テーブルに GitHub アカウント情報が保存される

---

### 1-4. GitHub でログイン（既存ユーザー）

**手順は 1-2 と同様、プロバイダを GitHub に置き換えて実施**

**成功条件:**
- [x] ログインが成功する
- [x] 新規ユーザーが作成されない
- [x] トークンが更新される

---

## 2. エラーハンドリングテスト

### 2-1. ユーザーが認証をキャンセル

**目的:** ユーザーが OAuth 認証画面でキャンセルした場合、適切にハンドリングされることを確認

**手順:**

1. **ログインページにアクセス**
   ```
   http://localhost:8765/users/login
   ```

2. **Google または GitHub ログインボタンをクリック**

3. **認証画面でキャンセル**
   - Google: 「キャンセル」をクリック
   - GitHub: ブラウザの「戻る」ボタンをクリック

4. **リダイレクトを確認**
   - ログインページにリダイレクトされる
   - Flash メッセージ: "Authentication cancelled" が表示される

**期待結果:**
- ログインページに戻る
- エラーメッセージが表示される
- データベースに何も作成されない

**成功条件:**
- [x] エラーメッセージが表示される
- [x] アプリケーションがクラッシュしない
- [x] ユーザーが再度ログインを試行できる

---

### 2-2. 無効なプロバイダでアクセス

**目的:** サポートされていないプロバイダでアクセスした場合、適切にエラーが表示されることを確認

**手順:**

1. **無効な URL に直接アクセス**
   ```
   http://localhost:8765/users/login/facebook
   ```

2. **エラー画面を確認**
   - 500 エラー画面が表示される（開発環境）
   - エラーメッセージ: "Unsupported provider: facebook"

**期待結果:**
- エラーが適切にハンドリングされる
- 詳細なエラー情報が表示される（開発環境のみ）

**成功条件:**
- [x] エラーが表示される
- [x] セキュリティ情報が漏洩しない

---

### 2-3. State パラメータの不一致（CSRF保護）

**目的:** CSRF 攻撃を防ぐために、state パラメータの検証が機能することを確認

**手順:**

1. **ログインページにアクセス**
   ```
   http://localhost:8765/users/login
   ```

2. **Google ログインボタンをクリック**

3. **認証 URL をコピー**
   - ブラウザのアドレスバーから Google の認証 URL をコピー
   - 例: `https://accounts.google.com/o/oauth2/auth?client_id=...&state=abc123...`

4. **別のブラウザ/シークレットウィンドウで同じ URL にアクセス**
   - 認証を完了させる

5. **リダイレクト後の動作を確認**
   - ログインページにリダイレクトされる
   - Flash メッセージ: "Invalid state parameter" が表示される

**期待結果:**
- state パラメータが一致しないため、ログインが拒否される
- CSRF 攻撃が防がれる

**成功条件:**
- [x] エラーメッセージが表示される
- [x] ログインが拒否される
- [x] セッション state が検証される

---

## 3. ユーザー管理テスト

### 3-1. 同じメールアドレスで異なるプロバイダからログイン

**目的:** 同じメールアドレスでも、異なるプロバイダからログインした場合、別々のアカウントとして扱われることを確認

**注意:** 現在の実装では、プロバイダごとに別ユーザーが作成されます。将来的にメールアドレスでマージする機能を追加する場合は、この動作が変わります。

**手順:**

1. **Google でログイン**
   - メールアドレス: `test@example.com` の Google アカウントでログイン

2. **ログアウト**

3. **GitHub でログイン**
   - 同じメールアドレス: `test@example.com` の GitHub アカウントでログイン

4. **データベースを確認**
   ```bash
   # 同じメールアドレスのユーザーが複数存在するか確認
   psql -d idp_development -c "SELECT id, username, email FROM users WHERE email = 'test@example.com';"

   # ソーシャルアカウントを確認
   psql -d idp_development -c "SELECT user_id, provider, email FROM social_accounts WHERE email = 'test@example.com';"
   ```

**期待結果:**
- 2つの別々のユーザーレコードが作成される
- 各ユーザーに対応するソーシャルアカウントが作成される

**成功条件:**
- [x] 両方のプロバイダでログインできる
- [x] それぞれ別のユーザーとして扱われる

---

### 3-2. トークン更新の確認

**目的:** 再ログイン時に access_token と refresh_token が更新されることを確認

**手順:**

1. **初回ログイン**
   - Google でログイン

2. **トークンを記録**
   ```bash
   psql -d idp_development -c "SELECT access_token_encrypted, modified FROM social_accounts WHERE provider = 'google' LIMIT 1;"
   ```
   - `access_token_encrypted` の値をメモ
   - `modified` のタイムスタンプをメモ

3. **ログアウトして再ログイン**
   - ログアウト
   - 同じ Google アカウントで再度ログイン

4. **トークンを再確認**
   ```bash
   psql -d idp_development -c "SELECT access_token_encrypted, modified FROM social_accounts WHERE provider = 'google' LIMIT 1;"
   ```

**期待結果:**
- `access_token_encrypted` が変更されている（トークンが更新された）
- `modified` タイムスタンプが更新されている

**成功条件:**
- [x] トークンが更新される
- [x] タイムスタンプが更新される

---

### 3-3. ユーザー情報の取得

**目的:** ソーシャルアカウントからユーザー情報が正しく取得され、保存されることを確認

**手順:**

1. **Google でログイン**

2. **ソーシャルアカウント情報を確認**
   ```bash
   psql -d idp_development -c "SELECT provider, provider_user_id, email, name, avatar_url, raw_data FROM social_accounts WHERE provider = 'google' LIMIT 1;"
   ```

**期待結果:**
- `provider_user_id`: Google のユーザーID（数字の文字列）
- `email`: Google アカウントのメールアドレス
- `name`: Google アカウントの表示名
- `avatar_url`: プロフィール画像の URL
- `raw_data`: プロバイダから返された生データ（JSON形式）

**成功条件:**
- [x] すべての情報が正しく保存されている
- [x] `raw_data` に完全なプロフィール情報が含まれている

---

## テスト実行チェックリスト

### 実行前チェック
- [ ] Google OAuth アプリが設定済み
- [ ] GitHub OAuth アプリが設定済み
- [ ] `config/app_local.php` に認証情報が設定済み
- [ ] データベースが起動している
- [ ] マイグレーションが実行済み
- [ ] 開発サーバーが起動中

### 実行後チェック
- [ ] すべてのテストケースが成功
- [ ] データベースの状態が期待通り
- [ ] ログにエラーがない

---

## トラブルシューティング

### ログの確認

```bash
# エラーログ
tail -f logs/error.log

# デバッグログ
tail -f logs/debug.log

# Apacheログ（該当する場合）
tail -f /var/log/apache2/error.log
```

### データベースのリセット

テストをやり直す場合:

```bash
# すべてのソーシャルアカウントを削除
psql -d idp_development -c "DELETE FROM social_accounts;"

# パスワードなしユーザー（ソーシャルログインユーザー）を削除
psql -d idp_development -c "DELETE FROM users WHERE password_hash IS NULL;"
```

---

## 次のステップ

統合テストが成功したら:

1. **本番環境の準備**
   - HTTPS の設定
   - 本番用 OAuth アプリの作成
   - 環境変数での認証情報管理

2. **追加機能の実装**
   - プロフィール画像の表示
   - アカウント連携機能
   - メールアドレスでのアカウントマージ

3. **自動化テストの実装**
   - Selenium/Puppeteer を使った E2E テスト
   - モック API を使った単体テスト
