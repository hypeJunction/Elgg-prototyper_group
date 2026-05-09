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

	/**
     * @return string
     */
    public function getPluginID(): string {
		return 'prototyper_group';
	}

	/**
     * @return void
     */
    public function testElggPluginFileIsValid(): void {
		$file = dirname(__DIR__, 4) . '/elgg-plugin.php';
		$this->assertFileExists($file);
		$spec = require $file;
		$this->assertIsArray($spec);
		$this->assertArrayHasKey('actions', $spec);
		$this->assertArrayHasKey('events', $spec);
		$this->assertArrayHasKey('view_extensions', $spec);
	}

	/**
     * @return void
     */
    public function testActionsRegistered(): void {
		$spec = require dirname(__DIR__, 4) . '/elgg-plugin.php';
		$this->assertArrayHasKey('groups/prototype', $spec['actions']);
		$this->assertSame('admin', $spec['actions']['groups/prototype']['access']);
		$this->assertArrayHasKey('groups/edit', $spec['actions']);
	}

	/**
     * @return void
     */
    public function testEventsRegistered(): void {
		$spec = require dirname(__DIR__, 4) . '/elgg-plugin.php';
		$this->assertArrayHasKey('prototype', $spec['events']);
		$this->assertArrayHasKey('groups/edit', $spec['events']['prototype']);
		$this->assertArrayHasKey('fields', $spec['events']);
		$this->assertArrayHasKey('group:group', $spec['events']['fields']);
	}

	/**
     * @return void
     */
    public function testViewExtensionRegistered(): void {
		$spec = require dirname(__DIR__, 4) . '/elgg-plugin.php';
		$this->assertArrayHasKey('prototyper/elements/submit', $spec['view_extensions']);
		$this->assertArrayHasKey('groups/delete', $spec['view_extensions']['prototyper/elements/submit']);
	}

	/**
     * @return void
     */
    public function testHooksClassLoadable(): void {
		$this->assertTrue(class_exists(\hypeJunction\Prototyper\Groups\Hooks::class));
	}

	/**
     * @return void
     */
    public function testFieldClassesLoadable(): void {
		// These classes extend hypeJunction\Prototyper\Elements\AttributeField from
		// hypeprototyper. Skip if hypeprototyper is not loaded (not yet migrated to 7.x).
		if (!class_exists(\hypeJunction\Prototyper\Elements\AttributeField::class)) {
			$this->markTestSkipped('hypeprototyper dep not migrated to 7.x — AttributeField unavailable');
			return;
		}
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
