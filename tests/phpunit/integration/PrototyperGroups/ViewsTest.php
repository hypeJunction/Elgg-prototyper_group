<?php

namespace PrototyperGroups;

use Elgg\IntegrationTestCase;

/**
 * Tests that core plugin views render (or at least resolve) without
 * fatal errors. Some views require hypePrototyper to be active and
 * are skipped otherwise.
 */
class ViewsTest extends IntegrationTestCase {

	public function up() {
	}

	public function down() {
	}

	public function getPluginID(): string {
		return 'prototyper_group';
	}

	public function testPluginViewFilesExist(): void {
		$base = dirname(__DIR__, 4) . '/views/default';
		$views = [
			'forms/groups/edit.php',
			'resources/groups/add.php',
			'resources/groups/edit.php',
			'resources/groups/edit/profile.php',
			'resources/groups/edit/settings.php',
			'groups/edit.php',
			'groups/delete.php',
			'groups/profile/fields.php',
			'admin/appearance/group_fields.php',
			'admin/appearance/group_fields/filter.php',
			'filters/groups/edit.php',
			'input/groups/content_access_mode.php',
			'input/groups/membership.php',
			'input/groups/owner.php',
			'input/groups/tools.php',
			'input/groups/visibility.php',
		];
		foreach ($views as $v) {
			$this->assertFileExists($base . '/' . $v, "Missing view: $v");
		}
	}

	public function testGroupDeleteViewRendersWithoutError(): void {
		if (!\elgg_view_exists('groups/delete')) {
			$this->markTestSkipped('groups/delete view not registered (plugin not active in test DB).');
			return;
		}
		$output = \elgg_view('groups/delete', ['entity' => $this->createGroup()]);
		$this->assertIsString($output);
	}

	public function testInputMembershipViewRenders(): void {
		if (!\elgg_view_exists('input/groups/membership')) {
			$this->markTestSkipped('input/groups/membership view not registered.');
			return;
		}
		$output = \elgg_view('input/groups/membership', ['name' => 'membership', 'value' => ACCESS_PUBLIC]);
		$this->assertIsString($output);
	}

	public function testInputVisibilityViewRenders(): void {
		if (!\elgg_view_exists('input/groups/visibility')) {
			$this->markTestSkipped('input/groups/visibility view not registered.');
			return;
		}
		$output = \elgg_view('input/groups/visibility', ['name' => 'vis', 'value' => ACCESS_PUBLIC]);
		$this->assertIsString($output);
	}

	public function testInputToolsViewRenders(): void {
		if (!\elgg_view_exists('input/groups/tools')) {
			$this->markTestSkipped('input/groups/tools view not registered.');
			return;
		}
		$output = \elgg_view('input/groups/tools', ['name' => 'tools', 'value' => []]);
		$this->assertIsString($output);
	}

	public function testLanguagesFileLoadable(): void {
		$langFile = dirname(__DIR__, 4) . '/languages/en.php';
		$this->assertFileExists($langFile);
		$strings = require $langFile;
		$this->assertIsArray($strings);
		$this->assertNotEmpty($strings);
	}
}
