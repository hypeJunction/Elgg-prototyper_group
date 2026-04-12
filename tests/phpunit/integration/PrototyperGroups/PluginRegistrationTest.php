<?php

namespace PrototyperGroups;

use Elgg\IntegrationTestCase;

/**
 * Smoke tests for prototyper_group plugin registration
 * defined in elgg-plugin.php.
 */
class PluginRegistrationTest extends IntegrationTestCase {

	public function up() {
	}

	public function down() {
	}

	public function getPluginID(): string {
		return 'prototyper_group';
	}

	public function testElggPluginFileIsValid(): void {
		$file = dirname(__DIR__, 4) . '/elgg-plugin.php';
		$this->assertFileExists($file);
		$spec = require $file;
		$this->assertIsArray($spec);
		$this->assertArrayHasKey('actions', $spec);
		$this->assertArrayHasKey('hooks', $spec);
		$this->assertArrayHasKey('view_extensions', $spec);
	}

	public function testActionsRegistered(): void {
		$spec = require dirname(__DIR__, 4) . '/elgg-plugin.php';
		$this->assertArrayHasKey('groups/prototype', $spec['actions']);
		$this->assertSame('admin', $spec['actions']['groups/prototype']['access']);
		$this->assertArrayHasKey('groups/edit', $spec['actions']);
	}

	public function testHooksRegistered(): void {
		$spec = require dirname(__DIR__, 4) . '/elgg-plugin.php';
		$this->assertArrayHasKey('prototype', $spec['hooks']);
		$this->assertArrayHasKey('groups/edit', $spec['hooks']['prototype']);
		$this->assertArrayHasKey('fields', $spec['hooks']);
		$this->assertArrayHasKey('group:group', $spec['hooks']['fields']);
	}

	public function testViewExtensionRegistered(): void {
		$spec = require dirname(__DIR__, 4) . '/elgg-plugin.php';
		$this->assertArrayHasKey('prototyper/elements/submit', $spec['view_extensions']);
		$this->assertArrayHasKey('groups/delete', $spec['view_extensions']['prototyper/elements/submit']);
	}

	public function testHooksClassLoadable(): void {
		$this->assertTrue(class_exists(\hypeJunction\Prototyper\Groups\Hooks::class));
	}

	public function testFieldClassesLoadable(): void {
		$classes = [
			\hypeJunction\Prototyper\Groups\NameField::class,
			\hypeJunction\Prototyper\Groups\MembershipField::class,
			\hypeJunction\Prototyper\Groups\VisibilityField::class,
			\hypeJunction\Prototyper\Groups\ContentAccessModeField::class,
			\hypeJunction\Prototyper\Groups\OwnerField::class,
			\hypeJunction\Prototyper\Groups\ToolsField::class,
		];
		foreach ($classes as $c) {
			$this->assertTrue(class_exists($c), "Missing class: $c");
		}
	}
}
