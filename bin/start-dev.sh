#!/bin/bash

# OAuth2/OpenID Connect 認可サーバー - 開発サーバー起動スクリプト

set -e

# カラー定義
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}OAuth2/OIDC 認可サーバー${NC}"
echo -e "${BLUE}================================${NC}"
echo ""

# プロジェクトルートに移動
cd "$(dirname "$0")/.."

# データベース接続チェック
echo -e "${YELLOW}[1/3] データベース接続確認中...${NC}"
if ! psql -d idp_development -c "SELECT 1" > /dev/null 2>&1; then
    echo -e "${RED}エラー: データベース 'idp_development' に接続できません${NC}"
    echo -e "${YELLOW}ヒント: 以下のコマンドでデータベースを作成してください:${NC}"
    echo "  createdb idp_development"
    echo "  bin/cake migrations migrate"
    exit 1
fi
echo -e "${GREEN}✓ データベース接続OK${NC}"
echo ""

# テーブル存在チェック
echo -e "${YELLOW}[2/3] テーブル確認中...${NC}"
if ! psql -d idp_development -c "SELECT 1 FROM users LIMIT 1" > /dev/null 2>&1; then
    echo -e "${YELLOW}警告: usersテーブルが見つかりません${NC}"
    echo -e "${YELLOW}マイグレーションを実行しますか? (y/n)${NC}"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        bin/cake migrations migrate
        echo -e "${GREEN}✓ マイグレーション完了${NC}"
    else
        echo -e "${RED}中止しました${NC}"
        exit 1
    fi
fi
echo -e "${GREEN}✓ テーブル確認OK${NC}"
echo ""

# ユーザー存在チェック
echo -e "${YELLOW}[3/3] テストユーザー確認中...${NC}"
USER_COUNT=$(psql -d idp_development -t -c "SELECT COUNT(*) FROM users" | xargs)
if [ "$USER_COUNT" -eq 0 ]; then
    echo -e "${YELLOW}テストユーザーが存在しません。作成しますか? (y/n)${NC}"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        psql -d idp_development -c "
        INSERT INTO users (id, username, email, password_hash, is_active, created, modified) VALUES
        ('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'testuser1', 'testuser1@example.com',
         '\$2y\$12\$FL.ZlFBAJkwwuIU2Bdtf8OQ9tiSVAUVhXsDYh4CBkFec.4VUlbeJ2', true, NOW(), NOW()),
        ('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'testuser2', 'testuser2@example.com',
         '\$2y\$12\$4GHmwGbFxPwnsO9xCy4r/.2PTg/1qcC/LiYuDIIy9jKdmfL0i4iDW', true, NOW(), NOW()),
        ('cccccccc-cccc-cccc-cccc-cccccccccccc', 'inactiveuser', 'inactive@example.com',
         '\$2y\$12\$yzzUX7Ob69IYFBW9DS.YnOn1rPVu6c6frBznCz2HHSwRSZFRgXbZG', false, NOW(), NOW())
        ON CONFLICT (id) DO NOTHING;
        " > /dev/null
        echo -e "${GREEN}✓ テストユーザー作成完了${NC}"
    fi
else
    echo -e "${GREEN}✓ テストユーザー確認OK (${USER_COUNT}件)${NC}"
fi
echo ""

# ソーシャルログイン設定チェック
echo -e "${YELLOW}[4/4] ソーシャルログイン設定確認中...${NC}"
GOOGLE_CLIENT_ID=$(grep -A 3 "'google'" config/app_local.php | grep "clientId" | cut -d "'" -f 4 2>/dev/null || echo "")
GITHUB_CLIENT_ID=$(grep -A 3 "'github'" config/app_local.php | grep "clientId" | cut -d "'" -f 4 2>/dev/null || echo "")

if [ "$GOOGLE_CLIENT_ID" = "test_google_client_id" ] || [ -z "$GOOGLE_CLIENT_ID" ]; then
    echo -e "${YELLOW}⚠ Google OAuth未設定${NC}"
    GOOGLE_CONFIGURED=false
else
    echo -e "${GREEN}✓ Google OAuth設定済み${NC}"
    GOOGLE_CONFIGURED=true
fi

if [ "$GITHUB_CLIENT_ID" = "test_github_client_id" ] || [ -z "$GITHUB_CLIENT_ID" ]; then
    echo -e "${YELLOW}⚠ GitHub OAuth未設定${NC}"
    GITHUB_CONFIGURED=false
else
    echo -e "${GREEN}✓ GitHub OAuth設定済み${NC}"
    GITHUB_CONFIGURED=true
fi
echo ""

# サーバー起動情報表示
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}サーバーを起動します${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo -e "${BLUE}URL:${NC}         http://localhost:8765/"
echo -e "${BLUE}ログイン:${NC}     http://localhost:8765/users/login"
echo ""
echo -e "${YELLOW}テストユーザー:${NC}"
echo "  - testuser1 / password123 (有効)"
echo "  - testuser2 / password456 (有効)"
echo "  - inactiveuser / password789 (無効)"
echo ""
echo -e "${YELLOW}ソーシャルログイン:${NC}"
if [ "$GOOGLE_CONFIGURED" = true ]; then
    echo -e "  - ${GREEN}Google OAuth (利用可能)${NC}"
else
    echo -e "  - ${YELLOW}Google OAuth (未設定)${NC}"
fi
if [ "$GITHUB_CONFIGURED" = true ]; then
    echo -e "  - ${GREEN}GitHub OAuth (利用可能)${NC}"
else
    echo -e "  - ${YELLOW}GitHub OAuth (未設定)${NC}"
fi
echo ""
if [ "$GOOGLE_CONFIGURED" = false ] || [ "$GITHUB_CONFIGURED" = false ]; then
    echo -e "${YELLOW}📝 ソーシャルログインを有効にするには:${NC}"
    echo "   1. Google/GitHub Developer Consoleでアプリ登録"
    echo "   2. Redirect URI: http://localhost:8765/users/callback/{provider}"
    echo "   3. config/app_local.php に認証情報を設定"
    echo "   詳細: docs/SOCIAL_LOGIN_SETUP.md を参照"
    echo ""
fi
echo -e "${YELLOW}停止: Ctrl+C${NC}"
echo ""
echo -e "${GREEN}================================${NC}"
echo ""

# サーバー起動
bin/cake server
