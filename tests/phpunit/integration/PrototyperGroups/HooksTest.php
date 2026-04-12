<?php

namespace PrototyperGroups;

use Elgg\Hook;
use Elgg\IntegrationTestCase;
use hypeJunction\Prototyper\Groups\Hooks;

/**
 * Tests for hypeJunction\Prototyper\Groups\Hooks.
 *
 * These tests mock \Elgg\Hook (interface) and verify the handlers
 * assemble the expected field specification arrays. They do not
 * exercise hypePrototyper() runtime calls — getConfigFields is
 * covered separately with a skip when hypePrototyper is unavailable.
 */
class HooksTest extends IntegrationTestCase {

	public function up() {
	}

	public function down() {
	}

	public function getPluginID(): string {
		return 'prototyper_group';
	}

	/**
	 * Build a mock \Elgg\Hook with the given value and params.
	 */
	protected function makeHook(array $value, array $params = []): Hook {
		$hook = $this->getMockBuilder(Hook::class)->getMock();
		$hook->method('getValue')->willReturn($value);
		$hook->method('getParams')->willReturn($params);
		$hook->method('getParam')->willReturnCallback(function ($key, $default = null) use ($params) {
			return array_key_exists($key, $params) ? $params[$key] : $default;
		});
		$hook->method('getEntityParam')->willReturn($params['entity'] ?? null);
		$hook->method('getName')->willReturn('prototype');
		$hook->method('getType')->willReturn('groups/edit');
		return $hook;
	}

	public function testGetPrototypeFieldsAddsCoreGroupFields(): void {
		$group = new \ElggGroup();
		$hook = $this->makeHook([], ['entity' => $group]);

		$result = Hooks::getPrototypeFields($hook);

		$this->assertIsArray($result);
		// Core fields the handler always appends
		$this->assertArrayHasKey('name', $result);
		$this->assertArrayHasKey('membership', $result);
		$this->assertArrayHasKey('vis', $result);
		$this->assertArrayHasKey('content_access_mode', $result);
		$this->assertArrayHasKey('owner_guid', $result);
		$this->assertArrayHasKey('tools', $result);
	}

	public function testGetPrototypeFieldsAssignsExpectedClassNames(): void {
		$group = new \ElggGroup();
		$hook = $this->makeHook([], ['entity' => $group]);

		$result = Hooks::getPrototypeFields($hook);

		$this->assertSame(
			\hypeJunction\Prototyper\Groups\NameField::class,
			$result['name']['class_name']
		);
		$this->assertSame(
			\hypeJunction\Prototyper\Groups\MembershipField::class,
			$result['membership']['class_name']
		);
		$this->assertSame(
			\hypeJunction\Prototyper\Groups\VisibilityField::class,
			$result['vis']['class_name']
		);
		$this->assertSame(
			\hypeJunction\Prototyper\Groups\ContentAccessModeField::class,
			$result['content_access_mode']['class_name']
		);
		$this->assertSame(
			\hypeJunction\Prototyper\Groups\OwnerField::class,
			$result['owner_guid']['class_name']
		);
	}

	public function testGetPrototypeFieldsIncludesIconAndDescriptionWhenNoPrototypeStored(): void {
		// Ensure no stored prototype setting for this subtype
		$plugin = \elgg_get_plugin_from_id('prototyper_group');
		if ($plugin) {
			$plugin->unsetSetting('prototype:default');
		}

		$group = new \ElggGroup();
		$hook = $this->makeHook([], ['entity' => $group]);

		$result = Hooks::getPrototypeFields($hook);

		$this->assertArrayHasKey('icon', $result);
		$this->assertSame('icon', $result['icon']['type']);
		$this->assertSame('file', $result['icon']['data_type']);

		$this->assertArrayHasKey('description', $result);
		$this->assertSame('description', $result['description']['type']);
		$this->assertSame('attribute', $result['description']['data_type']);
	}

	public function testGetPrototypeFieldsUsesStoredPrototypeWhenPresent(): void {
		$plugin = \elgg_get_plugin_from_id('prototyper_group');
		if (!$plugin) {
			$this->markTestSkipped('prototyper_group plugin not installed in test DB.');
			return;
		}

		$stored = [
			'custom_field' => [
				'type' => 'text',
				'data_type' => 'metadata',
			],
		];
		$plugin->setSetting('prototype:default', serialize($stored));

		try {
			$group = new \ElggGroup();
			$hook = $this->makeHook([], ['entity' => $group]);
			$result = Hooks::getPrototypeFields($hook);

			$this->assertArrayHasKey('custom_field', $result);
			$this->assertSame('text', $result['custom_field']['type']);
			// When stored prototype is used, icon/description are NOT auto-added
			$this->assertArrayNotHasKey('icon', $result);
		} finally {
			$plugin->unsetSetting('prototype:default');
		}
	}

	public function testGetPrototypeFieldsMergesExistingReturnValue(): void {
		$group = new \ElggGroup();
		$hook = $this->makeHook(['preexisting' => ['type' => 'text']], ['entity' => $group]);

		$result = Hooks::getPrototypeFields($hook);

		$this->assertArrayHasKey('preexisting', $result);
		$this->assertArrayHasKey('name', $result);
	}

	public function testGetConfigFieldsReturnsArray(): void {
		if (!function_exists('hypePrototyper')) {
			$this->markTestSkipped('hypePrototyper plugin not active.');
			return;
		}
		$hook = $this->makeHook(['title' => 'text'], ['subtype' => null]);

		$result = Hooks::getConfigFields($hook);
		$this->assertIsArray($result);
		// Pre-existing keys must be preserved
		$this->assertArrayHasKey('title', $result);
	}
}
