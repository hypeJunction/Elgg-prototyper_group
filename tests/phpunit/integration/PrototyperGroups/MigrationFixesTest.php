<?php

namespace PrototyperGroups;

use Elgg\Event;
use Elgg\IntegrationTestCase;
use hypeJunction\Prototyper\Groups\Hooks;
use hypeJunction\Prototyper\Groups\MembershipField;

/**
 * Regression tests pinning the 6.x -> 7.x migration fixes for prototyper_group.
 *
 * Each test corresponds to a specific migration commit and asserts the FIXED
 * behavior (removed symbol replaced, surviving core ESM imported, current-language
 * label key, JSON storage, 'guid' route param, PHP 8 null-safety). Tests that
 * require the hypePrototyper runtime markTestSkipped when the dep is not active.
 */
class MigrationFixesTest extends IntegrationTestCase {

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

	private function pluginRoot(): string {
		return dirname(__DIR__, 4);
	}

	private function makeHook(array $value, array $params = []): Event {
		return new Event(elgg(), 'prototype', 'groups/edit', $value, $params);
	}

	/**
	 * Regression: 2d8646e — the 'elgg/groups/edit' AMD module was removed in 7.x.
	 * The form view must import the surviving core ESM 'groups/edit/access' and
	 * never reference the removed module id.
	 *
	 * @return void
	 */
	public function testFormImportsSurvivingCoreEsmNotRemovedAmd(): void {
		$src = file_get_contents($this->pluginRoot() . '/views/default/forms/groups/edit.php');

		$this->assertStringContainsString('groups/edit/access', $src, 'form must import the surviving 7.x core ESM id');
		$this->assertStringNotContainsString('elgg/groups/edit', $src, 'removed 7.x AMD module id must not be imported');
	}

	/**
	 * Regression: 089706c — register_error() was removed in 6.x. The edit resource
	 * views for a non-editable/absent group must call elgg_register_error_message()
	 * instead of the removed helper.
	 *
	 * @return void
	 */
	public function testEditResourceViewsUseErrorMessageHelperNotRemovedRegisterError(): void {
		foreach (['resources/groups/edit/profile.php', 'resources/groups/edit/settings.php'] as $view) {
			$src = file_get_contents($this->pluginRoot() . '/views/default/' . $view);
			$this->assertStringContainsString('elgg_register_error_message(', $src, "$view must use elgg_register_error_message()");
			$this->assertStringNotContainsString('register_error(', $src, "$view must not call removed register_error()");
		}
	}

	/**
	 * Regression: 7ad191f — field labels are keyed by elgg_get_current_language()
	 * (the 7.x helper), not a hardcoded/removed language accessor. The label array
	 * for a core field must be keyed by exactly the current language string.
	 *
	 * @return void
	 */
	public function testPrototypeFieldLabelsKeyedByCurrentLanguage(): void {
		$group = new \ElggGroup();
		$result = Hooks::getPrototypeFields($this->makeHook([], ['entity' => $group]));

		$this->assertIsArray($result['name']['label']);
		$this->assertSame(
			[\elgg_get_current_language()],
			array_keys($result['name']['label']),
			'field label must be keyed by the current language string'
		);
	}

	/**
	 * Regression: 590627c — stored prototypes moved from serialize() to json_encode()
	 * in 5.x, but getPrototypeFields keeps a backward-compat @unserialize path for
	 * rows written before the MigratePrototypesToJson batch ran. A legacy serialized
	 * blob must still decode (and, being a stored prototype, suppress icon/description).
	 *
	 * @return void
	 */
	public function testGetPrototypeFieldsFallsBackToUnserializeForLegacyBlob(): void {
		$plugin = \elgg_get_plugin_from_id('prototyper_group');
		if (!$plugin || !$plugin->isActive()) {
			$this->markTestSkipped('prototyper_group plugin not active (deps may not be migrated to 7.x yet).');
			return;
		}

		// Pre-5.x storage format: a PHP-serialized array (not valid JSON).
		$legacy = ['legacy_field' => ['type' => 'text', 'data_type' => 'metadata']];
		$plugin->setSetting('prototype:default', serialize($legacy));

		try {
			$group = new \ElggGroup();
			$result = Hooks::getPrototypeFields($this->makeHook([], ['entity' => $group]));

			$this->assertArrayHasKey('legacy_field', $result, 'legacy serialized prototype must decode via unserialize fallback');
			$this->assertSame('text', $result['legacy_field']['type']);
			// A stored prototype suppresses the auto-added icon/description fields.
			$this->assertArrayNotHasKey('icon', $result);
		} finally {
			$plugin->unsetSetting('prototype:default');
		}
	}

	/**
	 * Regression: 590627c — the groups/prototype action persists the built prototype
	 * as json_encode(), never serialize().
	 *
	 * @return void
	 */
	public function testPrototypeActionJsonEncodesStoredPrototype(): void {
		$src = file_get_contents($this->pluginRoot() . '/actions/groups/prototype.php');

		$this->assertMatchesRegularExpression('/setSetting\([^)]*json_encode\(/', $src, 'prototype action must store JSON');
		$this->assertDoesNotMatchRegularExpression('/(?<![\w])serialize\s*\(/', $src, 'prototype action must not serialize() stored prototypes');
	}

	/**
	 * Regression: 93124e4 — groups/add + groups/edit load the edited group by the
	 * 'guid' route param (not 'container_guid'). The action must resolve the group
	 * via get_input('guid') -> get_entity().
	 *
	 * @return void
	 */
	public function testGroupsEditActionResolvesGroupByGuidParam(): void {
		$src = file_get_contents($this->pluginRoot() . '/actions/groups/edit.php');

		$this->assertMatchesRegularExpression("/get_input\(\s*['\"]guid['\"]/", $src, "action must read the 'guid' route param");
		$this->assertMatchesRegularExpression('/get_entity\(\s*\$guid\s*\)/', $src, "action must load the edited group by guid");
	}

	/**
	 * Regression: 8c16a5c — PHP 8.x null-safety. MembershipField::handle guards on
	 * isset($value); driving it with NO submitted input must not raise a TypeError
	 * and must default a fresh group to ACCESS_PRIVATE.
	 *
	 * @return void
	 */
	public function testMembershipFieldHandlesUnsetInputWithoutTypeError(): void {
		if (!class_exists('hypeJunction\\Prototyper\\Elements\\MetadataField')) {
			$this->markTestSkipped('hypePrototyper parent classes not available.');
			return;
		}

		$group = new \ElggGroup();
		$field = $this->getMockBuilder(MembershipField::class)
			->disableOriginalConstructor()
			->onlyMethods(['getShortname'])
			->getMock();
		$field->method('getShortname')->willReturn('membership');

		// No set_input() call -> get_input('membership') is null.
		$result = $field->handle($group);

		$this->assertSame($group, $result);
		$this->assertEquals(ACCESS_PRIVATE, $group->membership);
	}
}
