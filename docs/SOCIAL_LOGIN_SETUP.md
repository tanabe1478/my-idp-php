# ソーシャルログイン設定ガイド

このガイドでは、Google および GitHub を使ったソーシャルログイン機能をローカル環境で有効にする手順を説明します。

## 前提条件

- ローカル開発サーバーが `http://localhost:8765` で動作していること
- Google アカウント（Google OAuth用）
- GitHub アカウント（GitHub OAuth用）

---

## Google OAuth の設定

### 1. Google Cloud Console でプロジェクトを作成

1. [Google Cloud Console](https://console.cloud.google.com/) にアクセス
2. 新しいプロジェクトを作成、または既存のプロジェクトを選択
3. プロジェクト名: `IDP PHP Development` (任意の名前)

### 2. OAuth 同意画面の設定

1. 左メニューから **APIとサービス > OAuth 同意画面** を選択
2. ユーザータイプ: **外部** を選択して「作成」
3. アプリ情報を入力:
   - **アプリ名**: `IDP PHP Development`
   - **ユーザーサポートメール**: あなたのGmailアドレス
   - **デベロッパーの連絡先情報**: あなたのGmailアドレス
4. **保存して次へ** をクリック
5. スコープ画面: デフォルトのまま **保存して次へ**
6. テストユーザー画面:
   - **ADD USERS** をクリック
   - ログインに使用するGmailアドレスを追加（自分のアドレス）
   - **保存して次へ**
7. **ダッシュボードに戻る** をクリック

### 3. OAuth クライアント ID の作成

1. 左メニューから **APIとサービス > 認証情報** を選択
2. **認証情報を作成 > OAuth 2.0 クライアント ID** をクリック
3. アプリケーションの種類: **ウェブアプリケーション** を選択
4. 名前: `IDP PHP Local Development`
5. **承認済みの JavaScript 生成元** (任意):
   ```
   http://localhost:8765
   ```
6. **承認済みのリダイレクト URI** (必須):
   ```
   http://localhost:8765/users/callback/google
   ```
7. **作成** をクリック
8. 表示される **クライアント ID** と **クライアント シークレット** をコピーして保存

---

## GitHub OAuth の設定

### 1. OAuth App の作成

1. [GitHub Settings > Developer settings > OAuth Apps](https://github.com/settings/developers) にアクセス
2. **New OAuth App** をクリック
3. アプリケーション情報を入力:
   - **Application name**: `IDP PHP Development`
   - **Homepage URL**: `http://localhost:8765`
   - **Application description**: (任意) `Local development OAuth app`
   - **Authorization callback URL**: `http://localhost:8765/users/callback/github`
4. **Register application** をクリック

### 2. Client Secret の生成

1. 作成したアプリの詳細ページで **Client ID** が表示されているのでコピー
2. **Generate a new client secret** をクリック
3. 表示される **Client Secret** をコピーして保存（この画面を閉じると二度と表示されません）

---

## ローカル環境への認証情報の設定

取得した認証情報を `config/app_local.php` に設定します。

### config/app_local.php の編集

```php
<?php

use function Cake\Core\env;

return [
    // ... 既存の設定 ...

    /*
     * Social Authentication configuration
     */
    'SocialAuth' => [
        'google' => [
            'clientId' => '【ここにGoogle Client IDを貼り付け】',
            'clientSecret' => '【ここにGoogle Client Secretを貼り付け】',
        ],
        'github' => [
            'clientId' => '【ここにGitHub Client IDを貼り付け】',
            'clientSecret' => '【ここにGitHub Client Secretを貼り付け】',
        ],
    ],
];
```

### 例:

```php
'SocialAuth' => [
    'google' => [
        'clientId' => '123456789-abc123def456.apps.googleusercontent.com',
        'clientSecret' => 'GOCSPX-aBcDeFgHiJkLmNoPqRsTuVwXyZ',
    ],
    'github' => [
        'clientId' => 'Iv1.a1b2c3d4e5f6g7h8',
        'clientSecret' => '1234567890abcdef1234567890abcdef12345678',
    ],
],
```

---

## 動作確認

### 1. 開発サーバーの起動

```bash
./bin/start-dev.sh
```

起動時に以下のように表示されれば設定成功:

```
[4/4] ソーシャルログイン設定確認中...
✓ Google OAuth設定済み
✓ GitHub OAuth設定済み

================================
サーバーを起動します
================================

URL:         http://localhost:8765/
ログイン:     http://localhost:8765/users/login

ソーシャルログイン:
  - Google OAuth (利用可能)
  - GitHub OAuth (利用可能)
```

### 2. ブラウザでテスト

1. ブラウザで http://localhost:8765/users/login にアクセス
2. **Sign in with Google** または **Sign in with GitHub** ボタンをクリック
3. 認証プロバイダのログイン画面が表示される
4. ログインして同意画面で「許可」をクリック
5. アプリケーションにリダイレクトされ、ログイン完了

---

## トラブルシューティング

### Google: "このアプリは確認されていません" エラー

開発中のアプリでは「確認されていません」と表示されますが、これは正常です。

**対処法:**
1. 「詳細」をクリック
2. 「{アプリ名}（安全ではないページ）に移動」をクリック
3. 同意画面に進む

### Google: "リダイレクト URI のミスマッチ" エラー

**原因:** Redirect URI が Google Cloud Console の設定と一致していない

**対処法:**
1. Google Cloud Console で設定した Redirect URI を確認
2. 完全一致する必要があります: `http://localhost:8765/users/callback/google`
3. プロトコル (`http://`)、ポート番号 (`:8765`)、パス (`/users/callback/google`) すべて一致させる

### GitHub: "リダイレクト URI のミスマッチ" エラー

**対処法:**
1. GitHub の OAuth App 設定で Authorization callback URL を確認
2. `http://localhost:8765/users/callback/github` と完全一致させる

### "Authentication failed: Unsupported provider" エラー

**原因:** config/app_local.php の設定が正しくない、またはサーバーを再起動していない

**対処法:**
1. config/app_local.php を確認（シングルクォート、カンマ、括弧など）
2. サーバーを再起動（Ctrl+C で停止 → `./bin/start-dev.sh` で起動）

### ログインできたがユーザー情報が表示されない

**原因:** データベースにユーザーレコードが作成されていない

**対処法:**
1. データベースを確認:
   ```bash
   psql -d idp_development -c "SELECT * FROM users;"
   psql -d idp_development -c "SELECT * FROM social_accounts;"
   ```
2. ログを確認:
   ```bash
   tail -f logs/error.log
   ```

---

## セキュリティ上の注意

⚠️ **重要**: 本番環境では絶対に以下を守ってください:

1. **Client Secret を公開リポジトリにコミットしない**
   - `config/app_local.php` は `.gitignore` に含まれています
   - 環境変数で管理することを推奨

2. **HTTPS を使用する**
   - 本番環境では必ず HTTPS を使用
   - Redirect URI も `https://` で設定

3. **本番用の OAuth App を別途作成**
   - 開発用と本番用で OAuth App を分ける
   - 本番用の Redirect URI は本番ドメインを使用

4. **OAuth 同意画面を本番用に設定**
   - Google: 公開ステータスを「本番」に変更
   - アプリの検証を受ける（必要に応じて）

---

## 統合テストの実行

実際のブラウザでソーシャルログインをテストする手順は、`docs/INTEGRATION_TEST.md` を参照してください。

---

## 参考リンク

- [Google OAuth 2.0 for Web Server Applications](https://developers.google.com/identity/protocols/oauth2/web-server)
- [GitHub OAuth Apps Documentation](https://docs.github.com/en/developers/apps/building-oauth-apps/authorizing-oauth-apps)
- [CakePHP 5.x Authentication Documentation](https://book.cakephp.org/authentication/3/en/index.html)
