# OAuth2認可コードフロー テストガイド

このガイドでは、実装したOAuth2/OpenID Connect認可サーバーを実際にテストする方法を説明します。

## 前提条件

- 開発サーバーが起動していること（http://localhost:8765）
- テストユーザーが作成されていること

## ステップ1: テストユーザーでログイン

1. ブラウザで http://localhost:8765/users/login を開く
2. 以下の認証情報でログイン：
   - **ユーザー名**: `testuser1`
   - **パスワード**: `password123`

## ステップ2: OAuth2クライアントを登録

1. ログイン後、http://localhost:8765/clients/add にアクセス
2. 以下の情報でクライアントを登録：
   - **Client Name**: `My Test App`
   - **Redirect URIs** (1行に1つ):
     ```
     http://localhost:3000/callback
     ```
   - **Grant Types**:
     - ✓ Authorization Code
     - ✓ Refresh Token

3. **Register Client** ボタンをクリック

4. 登録完了画面で **client_id** と **client_secret** をコピーして保存してください！

   例：
   ```
   client_id: a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
   client_secret: x1y2z3a4b5c6d7e8f9g0h1i2j3k4l5m6n7o8p9q0r1s2t3u4v5w6x7y8z9a0b1c2
   ```

## ステップ3: OAuth2認可フローをテスト

### 方法A: ブラウザで手動テスト

1. 別のブラウザタブまたはプライベートウィンドウで、以下のURLにアクセス：

   ```
   http://localhost:8765/oauth/authorize?response_type=code&client_id=YOUR_CLIENT_ID&redirect_uri=http://localhost:3000/callback&scope=openid%20profile%20email&state=random_state_string
   ```

   **注意**: `YOUR_CLIENT_ID` を実際のclient_idに置き換えてください

2. まだログインしていない場合、ログインページにリダイレクトされます
   - testuser1 / password123 でログイン

3. **同意画面**が表示されます：
   - 要求されたスコープ（openid, profile, email）を確認
   - **Authorize** ボタンをクリック

4. リダイレクトされます（エラーになりますが正常です）：
   ```
   http://localhost:3000/callback?code=AUTHORIZATION_CODE&state=random_state_string
   ```

5. URLから **authorization code** をコピーします

### 方法B: curlコマンドでトークン取得

認可コードを取得したら、以下のコマンドでアクセストークンと交換します：

```bash
curl -X POST http://localhost:8765/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=authorization_code" \
  -d "code=YOUR_AUTHORIZATION_CODE" \
  -d "redirect_uri=http://localhost:3000/callback" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET"
```

**注意**: 以下を実際の値に置き換えてください：
- `YOUR_AUTHORIZATION_CODE`: ステップ3-4で取得した認可コード
- `YOUR_CLIENT_ID`: ステップ2で取得したclient_id
- `YOUR_CLIENT_SECRET`: ステップ2で取得したclient_secret

### 期待されるレスポンス

成功すると、以下のようなJSONレスポンスが返されます：

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "scope": "openid profile email",
  "id_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

## ステップ4: JWTトークンをデコードして確認

### オンラインツールを使用

1. https://jwt.io にアクセス
2. `access_token` の値を "Encoded" フィールドに貼り付け
3. デコードされた内容を確認：

   ```json
   {
     "iss": "http://localhost:8765",
     "sub": "aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa",
     "aud": "YOUR_CLIENT_ID",
     "iat": 1234567890,
     "exp": 1234571490,
     "scope": "openid profile email"
   }
   ```

4. `id_token` も同様にデコード：

   ```json
   {
     "iss": "http://localhost:8765",
     "sub": "aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa",
     "aud": "YOUR_CLIENT_ID",
     "iat": 1234567890,
     "exp": 1234571490,
     "auth_time": 1234567890,
     "preferred_username": "testuser1",
     "email": "testuser1@example.com",
     "email_verified": true
   }
   ```

## トラブルシューティング

### エラー: "Invalid client_id"
- client_idが正しいか確認してください
- クライアントが有効（is_active=true）か確認してください

### エラー: "Invalid redirect_uri"
- redirect_uriがクライアント登録時に設定したものと完全に一致しているか確認
- URLエンコーディングに注意

### エラー: "Authorization code is expired or already used"
- 認可コードは10分間のみ有効です
- 認可コードは1回しか使用できません
- 新しい認可コードを取得してください

### エラー: "Client authentication failed"
- client_secretが正しいか確認してください
- client_secretはクライアント登録時に一度だけ表示されます

## 完全なフローの例

### 1. クライアント登録

```bash
# ブラウザで実行（ログイン後）
http://localhost:8765/clients/add
```

### 2. 認可リクエスト

```bash
# ブラウザのアドレスバーに貼り付け
http://localhost:8765/oauth/authorize?response_type=code&client_id=abc123&redirect_uri=http://localhost:3000/callback&scope=openid%20profile%20email&state=xyz789
```

### 3. トークン取得

```bash
# ターミナルで実行
curl -X POST http://localhost:8765/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=authorization_code" \
  -d "code=認可コード" \
  -d "redirect_uri=http://localhost:3000/callback" \
  -d "client_id=abc123" \
  -d "client_secret=secret123"
```

## 次のステップ

- [ ] リフレッシュトークンを実装してトークンを更新
- [ ] UserInfoエンドポイントを実装してユーザー情報を取得
- [ ] PKCE (Proof Key for Code Exchange) を実装してセキュリティを強化

---

**注意**: このテストガイドは開発環境でのテスト用です。本番環境では、適切なセキュリティ対策（HTTPS、CSRF保護、レート制限など）を実装してください。
