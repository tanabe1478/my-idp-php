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
echo -e "${YELLOW}停止: Ctrl+C${NC}"
echo ""
echo -e "${GREEN}================================${NC}"
echo ""

# サーバー起動
bin/cake server
