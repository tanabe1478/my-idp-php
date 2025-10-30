import { test, expect } from '@playwright/test';
import * as dotenv from 'dotenv';
import * as path from 'path';

// Load environment variables
dotenv.config({ path: path.join(__dirname, '..', '.env') });

/**
 * Social Login Integration Tests
 *
 * These tests perform actual OAuth login flows with Google and GitHub
 * using real OAuth credentials configured in config/app_local.php
 */

test.describe('Social Login Integration', () => {
  test.beforeEach(async ({ page, context }) => {
    // Clear all cookies and storage to ensure clean state
    await context.clearCookies();
    await page.goto('/');
    await page.evaluate(() => {
      localStorage.clear();
      sessionStorage.clear();
    });

    // Navigate to login page
    await page.goto('/users/login');

    // Verify login page loaded by checking for login form
    await expect(page.locator('h3:has-text("Login")')).toBeVisible();
  });

  test.describe('Google OAuth Login', () => {
    test('should successfully login with Google account (new user)', async ({ page }) => {
      // Skip if credentials not provided
      const email = process.env.GOOGLE_TEST_EMAIL;
      const password = process.env.GOOGLE_TEST_PASSWORD;

      if (!email || !password) {
        test.skip();
        return;
      }

      // Click "Sign in with Google" button
      await page.click('text=Sign in with Google');

      // Wait for Google login page
      await page.waitForURL(/accounts\.google\.com/);

      // Fill in Google credentials
      // Email input
      await page.fill('input[type="email"]', email);
      await page.click('button:has-text("Next"), #identifierNext');

      // Wait for password page
      await page.waitForTimeout(2000);

      // Password input
      await page.fill('input[type="password"]', password);
      await page.click('button:has-text("Next"), #passwordNext');

      // Handle consent screen if it appears
      try {
        await page.waitForSelector('button:has-text("Continue"), button:has-text("Allow")', { timeout: 5000 });
        await page.click('button:has-text("Continue"), button:has-text("Allow")');
      } catch (e) {
        // Consent screen might not appear if already granted
        console.log('No consent screen or already consented');
      }

      // Should redirect back to application
      await expect(page).toHaveURL(/\/users\/index/, { timeout: 30000 });

      // Verify success message
      await expect(page.locator('text=Welcome back!')).toBeVisible();

      // Verify we're logged in
      await expect(page.locator('text=logout', { ignoreCase: true })).toBeVisible();
    });

    test('should successfully login with Google account (existing user)', async ({ page }) => {
      const email = process.env.GOOGLE_TEST_EMAIL;
      const password = process.env.GOOGLE_TEST_PASSWORD;

      if (!email || !password) {
        test.skip();
        return;
      }

      // First login to create user
      await page.click('text=Sign in with Google');
      await page.waitForURL(/accounts\.google\.com/);

      await page.fill('input[type="email"]', email);
      await page.click('button:has-text("Next"), #identifierNext');
      await page.waitForTimeout(2000);

      await page.fill('input[type="password"]', password);
      await page.click('button:has-text("Next"), #passwordNext');

      try {
        await page.waitForSelector('button:has-text("Continue"), button:has-text("Allow")', { timeout: 5000 });
        await page.click('button:has-text("Continue"), button:has-text("Allow")');
      } catch (e) {
        console.log('No consent screen');
      }

      await expect(page).toHaveURL(/\/users\/index/, { timeout: 30000 });

      // Logout
      await page.goto('/users/logout');
      await expect(page).toHaveURL(/\/users\/login/);

      // Login again (existing user)
      await page.click('text=Sign in with Google');

      // Should redirect back faster (already authenticated with Google)
      await expect(page).toHaveURL(/\/users\/index/, { timeout: 30000 });
      await expect(page.locator('text=Welcome back!')).toBeVisible();
    });

    test('should handle user cancelling Google OAuth', async ({ page }) => {
      // Click "Sign in with Google" button
      await page.click('text=Sign in with Google');

      // Wait for Google login page
      await page.waitForURL(/accounts\.google\.com/);

      // Go back (simulate cancel)
      await page.goBack();

      // Should return to login page
      await expect(page).toHaveURL(/\/users\/login/);
    });
  });

  test.describe('GitHub OAuth Login', () => {
    test('should successfully login with GitHub account (new user)', async ({ page }) => {
      // Skip if credentials not provided
      const username = process.env.GITHUB_TEST_USERNAME;
      const password = process.env.GITHUB_TEST_PASSWORD;

      if (!username || !password) {
        test.skip();
        return;
      }

      // Click "Sign in with GitHub" button
      await page.click('text=Sign in with GitHub');

      // Wait for GitHub login page
      await page.waitForURL(/github\.com/);

      // Fill in GitHub credentials
      try {
        // Try login form
        await page.fill('input[name="login"]', username, { timeout: 5000 });
        await page.fill('input[name="password"]', password);
        await page.click('input[type="submit"][value="Sign in"]');
      } catch (e) {
        // Already logged in, go to authorize
        console.log('Already logged in to GitHub');
      }

      // Handle authorization screen
      try {
        await page.waitForSelector('button:has-text("Authorize"), button[type="submit"]:has-text("Authorize")', { timeout: 10000 });
        await page.click('button:has-text("Authorize"), button[type="submit"]:has-text("Authorize")');
      } catch (e) {
        // Already authorized
        console.log('Already authorized or no authorization needed');
      }

      // Should redirect back to application
      await expect(page).toHaveURL(/\/users\/index/, { timeout: 30000 });

      // Verify success message
      await expect(page.locator('text=Welcome back!')).toBeVisible();

      // Verify we're logged in
      await expect(page.locator('text=logout', { ignoreCase: true })).toBeVisible();
    });

    test('should successfully login with GitHub account (existing user)', async ({ page }) => {
      const username = process.env.GITHUB_TEST_USERNAME;
      const password = process.env.GITHUB_TEST_PASSWORD;

      if (!username || !password) {
        test.skip();
        return;
      }

      // First login to create user
      await page.click('text=Sign in with GitHub');
      await page.waitForURL(/github\.com/);

      try {
        await page.fill('input[name="login"]', username, { timeout: 5000 });
        await page.fill('input[name="password"]', password);
        await page.click('input[type="submit"][value="Sign in"]');
      } catch (e) {
        console.log('Already logged in to GitHub');
      }

      try {
        await page.waitForSelector('button:has-text("Authorize"), button[type="submit"]:has-text("Authorize")', { timeout: 10000 });
        await page.click('button:has-text("Authorize"), button[type="submit"]:has-text("Authorize")');
      } catch (e) {
        console.log('Already authorized');
      }

      await expect(page).toHaveURL(/\/users\/index/, { timeout: 30000 });

      // Logout
      await page.goto('/users/logout');
      await expect(page).toHaveURL(/\/users\/login/);

      // Login again (existing user)
      await page.click('text=Sign in with GitHub');

      // Should redirect back faster
      await expect(page).toHaveURL(/\/users\/index/, { timeout: 30000 });
      await expect(page.locator('text=Welcome back!')).toBeVisible();
    });

    test('should handle user cancelling GitHub OAuth', async ({ page }) => {
      // Click "Sign in with GitHub" button
      await page.click('text=Sign in with GitHub');

      // Wait for GitHub page
      await page.waitForURL(/github\.com/);

      // Go back (simulate cancel)
      await page.goBack();

      // Should return to login page
      await expect(page).toHaveURL(/\/users\/login/);
    });
  });

  test.describe('Error Handling', () => {
    test('should show error for invalid provider', async ({ page }) => {
      // Try to access invalid provider directly
      await page.goto('/users/login/invalid-provider');

      // Should show error (500 page in development)
      await expect(page.locator('text=Unsupported provider')).toBeVisible();
    });

    test('should verify redirect URI matches', async ({ page, context }) => {
      // This test verifies that the redirect_uri is stored correctly in session

      // Start Google login
      await page.click('text=Sign in with Google');
      await page.waitForURL(/accounts\.google\.com/);

      // Get the authorization URL
      const url = page.url();

      // Extract redirect_uri from URL
      const urlParams = new URLSearchParams(url.split('?')[1]);
      const redirectUri = urlParams.get('redirect_uri');

      // Verify redirect_uri is correct
      expect(redirectUri).toBe('http://localhost:8765/users/callback/google');

      // Go back
      await page.goBack();
    });
  });

  test.describe('Database Verification', () => {
    test('should create user and social account in database after Google login', async ({ page }) => {
      const email = process.env.GOOGLE_TEST_EMAIL;
      const password = process.env.GOOGLE_TEST_PASSWORD;

      if (!email || !password) {
        test.skip();
        return;
      }

      // Note: This test requires database access
      // In a real scenario, you would query the database to verify:
      // 1. User record created with NULL password_hash
      // 2. Social account record created with provider='google'
      // 3. Tokens are encrypted and stored

      // For now, we just verify successful login
      await page.click('text=Sign in with Google');
      await page.waitForURL(/accounts\.google\.com/);

      await page.fill('input[type="email"]', email);
      await page.click('button:has-text("Next"), #identifierNext');
      await page.waitForTimeout(2000);

      await page.fill('input[type="password"]', password);
      await page.click('button:has-text("Next"), #passwordNext');

      try {
        await page.waitForSelector('button:has-text("Continue"), button:has-text("Allow")', { timeout: 5000 });
        await page.click('button:has-text("Continue"), button:has-text("Allow")');
      } catch (e) {
        console.log('No consent screen');
      }

      await expect(page).toHaveURL(/\/users\/index/, { timeout: 30000 });

      // TODO: Add database query to verify user and social_account records
      // const { exec } = require('child_process');
      // exec('psql -d idp_development -c "SELECT * FROM users WHERE email = ..."', ...);
    });
  });
});
