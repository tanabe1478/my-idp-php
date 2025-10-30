# E2E Tests for IDP PHP

このディレクトリには、Playwright を使用した End-to-End (E2E) 統合テストが含まれています。

## 概要

実際のブラウザを使用して、Google および GitHub OAuth ログインフローをテストします。

**テスト対象:**
- Google OAuth ログイン（新規ユーザー・既存ユーザー）
- GitHub OAuth ログイン（新規ユーザー・既存ユーザー）
- エラーハンドリング
- セッション管理
- CSRF 保護

## セットアップ

### 1. 依存関係のインストール

```bash
cd e2e-tests
npm install
npx playwright install
```

### 2. 環境変数の設定

`.env` ファイルを作成し、テスト用の Google/GitHub アカウント情報を設定:

```bash
cp .env.example .env
```

`.env` を編集:

```bash
# Google OAuth Test Account
GOOGLE_TEST_EMAIL=your-actual-google-email@gmail.com
GOOGLE_TEST_PASSWORD=your-actual-google-password

# GitHub OAuth Test Account
GITHUB_TEST_USERNAME=your-github-username
GITHUB_TEST_PASSWORD=your-github-password
```

**⚠️ 重要:**
- `.env` ファイルは `.gitignore` に含まれています（コミットされません）
- テスト専用のアカウントを使用することを推奨
- 本番環境の認証情報は絶対に使用しないこと

### 3. 開発サーバーの準備

テスト実行前に、OAuth アプリケーションが設定されていることを確認:

- Google OAuth アプリ: `http://localhost:8765/users/callback/google`
- GitHub OAuth アプリ: `http://localhost:8765/users/callback/github`
- `../config/app_local.php` に認証情報が設定済み

## テストの実行

### すべてのテストを実行

```bash
npm test
```

### ブラウザを表示して実行（デバッグ用）

```bash
npm run test:headed
```

### デバッグモードで実行

```bash
npm run test:debug
```

### Google ログインのみテスト

```bash
npm run test:google
```

### GitHub ログインのみテスト

```bash
npm run test:github
```

### レポートの表示

```bash
npm run report
```

## テスト構成

### `playwright.config.ts`

Playwright の設定ファイル:
- テストディレクトリ: `./tests`
- ベースURL: `http://localhost:8765`
- 開発サーバー自動起動: `../bin/start-dev.sh`
- レポート: HTML + リスト形式
- スクリーンショット: 失敗時のみ
- ビデオ: 失敗時のみ保持

### `tests/social-login.spec.ts`

ソーシャルログインの統合テスト:

**Google OAuth テスト:**
- ✅ 新規ユーザーのログイン
- ✅ 既存ユーザーのログイン
- ✅ ユーザーがキャンセル

**GitHub OAuth テスト:**
- ✅ 新規ユーザーのログイン
- ✅ 既存ユーザーのログイン
- ✅ ユーザーがキャンセル

**エラーハンドリング:**
- ✅ 無効なプロバイダ
- ✅ Redirect URI の検証

## トラブルシューティング

### テストがスキップされる

**原因:** `.env` ファイルに認証情報が設定されていない

**対処:**
```bash
cd e2e-tests
cat .env  # 確認
```

### Google ログインで "このアプリは確認されていません" エラー

**対処:** テスト内で自動的に「詳細」→「安全ではないページに移動」をクリックする必要がある場合があります。

手動で一度ログインして「許可」を与えておくと、以降のテストがスムーズになります。

### GitHub ログインで認証画面が表示されない

**原因:** 既に GitHub にログイン済みで、アプリも承認済み

**対処:** これは正常です。テストは自動的に処理します。

### タイムアウトエラー

**原因:** OAuth プロバイダの応答が遅い、またはネットワークの問題

**対処:**
```typescript
// playwright.config.ts で timeout を増やす
use: {
  actionTimeout: 10000, // 10秒
  navigationTimeout: 30000, // 30秒
}
```

### セッションが残っている

**対処:**
```bash
# データベースをリセット
cd ..
psql -d idp_development -c "DELETE FROM social_accounts;"
psql -d idp_development -c "DELETE FROM users WHERE password_hash IS NULL;"
```

## CI/CD での実行

GitHub Actions などの CI 環境でテストを実行する場合:

```yaml
- name: Run E2E Tests
  env:
    GOOGLE_TEST_EMAIL: ${{ secrets.GOOGLE_TEST_EMAIL }}
    GOOGLE_TEST_PASSWORD: ${{ secrets.GOOGLE_TEST_PASSWORD }}
    GITHUB_TEST_USERNAME: ${{ secrets.GITHUB_TEST_USERNAME }}
    GITHUB_TEST_PASSWORD: ${{ secrets.GITHUB_TEST_PASSWORD }}
  run: |
    cd e2e-tests
    npm ci
    npx playwright install --with-deps
    npm test
```

**注意:** CI 環境で実際の OAuth ログインを行うには、Google/GitHub の「ボット検出」を回避する必要がある場合があります。

## ベストプラクティス

1. **テスト専用アカウント**: 本番アカウントは使わない
2. **環境変数**: 認証情報はコミットしない
3. **並列実行を避ける**: OAuth テストは順次実行（`fullyParallel: false`）
4. **タイムアウト**: OAuth フローには時間がかかるため、十分な timeout を設定
5. **エラーハンドリング**: 外部APIに依存するため、リトライ機構を使用

## 参考リンク

- [Playwright Documentation](https://playwright.dev/)
- [Playwright TypeScript Guide](https://playwright.dev/docs/test-typescript)
- [Google OAuth Testing Best Practices](https://developers.google.com/identity/protocols/oauth2/web-server#testing)
- [GitHub OAuth Testing](https://docs.github.com/en/developers/apps/building-oauth-apps/testing-oauth-apps)
