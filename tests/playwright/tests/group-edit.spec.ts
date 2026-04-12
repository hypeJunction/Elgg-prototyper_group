import { test, expect } from '@playwright/test';
import { loginAs, getGroupByName, getMetadata } from '../helpers/elgg';

/**
 * Tests for the group create/edit form which is driven
 * by prototyper_group via the `prototype`/`groups/edit` hook.
 */
test.describe('prototyper_group — group form', () => {
  test('group add page renders form', async ({ page }) => {
    await loginAs(page, 'admin');
    const response = await page.goto('/groups/add');
    expect(response?.status()).toBeLessThan(400);

    // The prototyper-driven form should be on the page
    await expect(page.locator('form')).toBeVisible();
    // Core fields rendered by the Hooks handler
    await expect(page.locator('input[name="name"]')).toBeVisible();
  });

  test('admin creates a group via prototyper form and DB reflects it', async ({ page }) => {
    const uniqueName = 'PG Test Group ' + Date.now();

    await loginAs(page, 'admin');
    await page.goto('/groups/add');

    await page.fill('input[name="name"]', uniqueName);

    // Description field is a rich textarea — fall back to any matching input
    const description = page.locator(
      'textarea[name="description"], input[name="description"]'
    ).first();
    if (await description.count() > 0) {
      await description.fill('Automated test group for prototyper_group.');
    }

    await page.click('form button[type="submit"], form input[type="submit"]');

    // Allow redirect
    await page.waitForLoadState('networkidle');

    // Assert DB: group exists with our name
    const group = await getGroupByName(uniqueName);
    expect(group).toBeTruthy();
    expect(group.type).toBe('group');
  });

  test('non-admin cannot access admin prototype action', async ({ page }) => {
    await loginAs(page, 'testuser');
    const response = await page.goto('/admin/appearance/group_fields');
    // Non-admin should be denied or redirected away from admin
    expect([200, 302, 403]).toContain(response?.status() ?? 0);
    // Should not be on admin page content
    const onAdmin = page.url().includes('/admin/');
    if (onAdmin) {
      // If Elgg renders 200 with a denied message, verify we are not shown the form
      await expect(page.locator('form[action*="groups/prototype"]')).toHaveCount(0);
    }
  });
});
