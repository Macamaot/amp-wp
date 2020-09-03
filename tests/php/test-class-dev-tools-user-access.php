<?php
/**
 * Tests for DevToolsUserAccess class.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\DevToolsUserAccess;
use AmpProject\AmpWP\Option;

/**
 * Tests for DevToolsUserAccess class.
 *
 * @group dev-tools-user-access
 *
 * @since 2.0
 *
 * @covers DevToolsUserAccess
 */
class Test_DevToolsUserAccess extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var DevToolsUserAccess
	 */
	private $dev_tools_user_access;

	public function setUp() {
		parent::setUp();

		$this->dev_tools_user_access = new DevToolsUserAccess();
	}

	/**
	 * Tests DevToolsUserAccess::register
	 *
	 * @covers DevToolsUserAccess::register
	 */
	public function test_register() {
		$this->dev_tools_user_access->register();
		$this->assertEquals( 10, has_action( 'rest_api_init', [ $this->dev_tools_user_access, 'register_rest_field' ] ) );
		$this->assertEquals( 10, has_action( 'personal_options', [ $this->dev_tools_user_access, 'print_personal_options' ] ) );
		$this->assertEquals( 10, has_action( 'personal_options_update', [ $this->dev_tools_user_access, 'update_user_setting' ] ) );
		$this->assertEquals( 10, has_action( 'edit_user_profile_update', [ $this->dev_tools_user_access, 'update_user_setting' ] ) );
	}

	/**
	 * Tests DevToolsUserAccess::is_user_enabled
	 *
	 * @covers DevToolsUserAccess::is_user_enabled
	 */
	public function test_is_user_enabled() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$admin_user  = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		$editor_user = self::factory()->user->create_and_get( [ 'role' => 'editor' ] );

		$this->assertTrue( $this->dev_tools_user_access->is_user_enabled( $admin_user ) );
		$this->assertTrue( $this->dev_tools_user_access->is_user_enabled( $admin_user->ID ) );
		$this->assertFalse( $this->dev_tools_user_access->is_user_enabled( $editor_user ) );
		$this->assertFalse( $this->dev_tools_user_access->is_user_enabled( $editor_user->ID ) );
		wp_set_current_user( $admin_user->ID );
		$this->assertTrue( $this->dev_tools_user_access->is_user_enabled() );
		$this->dev_tools_user_access->set_user_enabled( $admin_user, false );
		$this->assertFalse( $this->dev_tools_user_access->is_user_enabled() );
		wp_set_current_user( $editor_user->ID );
		$this->assertFalse( $this->dev_tools_user_access->is_user_enabled() );
	}

	/**
	 * Tests DevToolsUserAccess::get_user_enabled
	 *
	 * @covers DevToolsUserAccess::get_user_enabled
	 */
	public function test_get_user_enabled() {
		$admin_user = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );

		// Enabled by default in Transitional mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->assertTrue( $this->dev_tools_user_access->get_user_enabled( $admin_user ) );

		// Enabled by default in Standard mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->assertTrue( $this->dev_tools_user_access->get_user_enabled( $admin_user->ID ) );

		// Disabled by default in Reader mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertFalse( $this->dev_tools_user_access->get_user_enabled( $admin_user->ID ) );

		// Check filter overriding default to be true in Reader mode, but then user forcing it off via user pref.
		delete_user_meta( $admin_user->ID, DevToolsUserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED );
		remove_all_filters( 'amp_dev_tools_user_default_enabled' );
		add_filter(
			'amp_dev_tools_user_default_enabled',
			function ( $enabled, $user_id ) use ( $admin_user ) {
				unset( $enabled );
				$this->assertSame( $user_id, $admin_user->ID );
				return true;
			},
			10,
			2
		);
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertTrue( $this->dev_tools_user_access->get_user_enabled( $admin_user ) );
		update_user_meta( $admin_user->ID, DevToolsUserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED, 'false' );
		$this->assertFalse( $this->dev_tools_user_access->get_user_enabled( $admin_user ) );

		// Check filter overriding default to be false in Standard mode, but then user forcing it on via user pref.
		delete_user_meta( $admin_user->ID, DevToolsUserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED );
		remove_all_filters( 'amp_dev_tools_user_default_enabled' );
		add_filter(
			'amp_dev_tools_user_default_enabled',
			function ( $enabled, $user_id ) use ( $admin_user ) {
				unset( $enabled );
				$this->assertSame( $user_id, $admin_user->ID );
				return false;
			},
			10,
			2
		);
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->assertFalse( $this->dev_tools_user_access->get_user_enabled( $admin_user ) );
		update_user_meta( $admin_user->ID, DevToolsUserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED, 'true' );
		$this->assertTrue( $this->dev_tools_user_access->get_user_enabled( $admin_user ) );
	}

	/**
	 * Tests DevToolsUserAccess::set_user_enabled
	 *
	 * @covers DevToolsUserAccess::set_user_enabled
	 */
	public function test_set_user_enabled() {
		$admin_user = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		$this->assertTrue( $this->dev_tools_user_access->set_user_enabled( $admin_user, false ) );
		$this->assertFalse( $this->dev_tools_user_access->get_user_enabled( $admin_user ) );
		$this->assertTrue( $this->dev_tools_user_access->set_user_enabled( $admin_user->ID, true ) );
		$this->assertTrue( $this->dev_tools_user_access->get_user_enabled( $admin_user ) );
	}

	/**
	 * Tests DevToolsUserAccess::register_rest_field
	 *
	 * @covers DevToolsUserAccess::register_rest_field
	 */
	public function test_register_rest_field() {
		global $wp_rest_additional_fields;

		$this->dev_tools_user_access->register_rest_field();

		$this->assertArrayHasKey( 'amp_dev_tools_enabled', $wp_rest_additional_fields['user'] );
	}

	/**
	 * Tests DevToolsUserAccess::print_personal_options
	 *
	 * @covers DevToolsUserAccess::print_personal_options
	 */
	public function test_print_personal_options() {
		$admin_user  = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		$editor_user = self::factory()->user->create_and_get( [ 'role' => 'editor' ] );

		ob_start();
		$this->dev_tools_user_access->print_personal_options( $editor_user );
		$this->assertEmpty( ob_get_clean() );

		wp_set_current_user( $admin_user->ID );
		ob_start();
		$this->dev_tools_user_access->print_personal_options( $editor_user );
		$this->assertEmpty( ob_get_clean() );

		ob_start();
		$this->dev_tools_user_access->print_personal_options( $admin_user );
		$this->assertContains( 'checkbox', ob_get_clean() );
	}

	/**
	 * Tests DevToolsUserAccess::update_user_setting
	 *
	 * @covers DevToolsUserAccess::update_user_setting
	 */
	public function test_update_user_setting() {
		$admin_user  = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		$editor_user = self::factory()->user->create_and_get( [ 'role' => 'editor' ] );

		$_POST[ DevToolsUserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED ] = 'true';

		$this->assertFalse( $this->dev_tools_user_access->update_user_setting( $admin_user->ID ) );

		wp_set_current_user( $admin_user->ID );
		$this->assertFalse( $this->dev_tools_user_access->update_user_setting( $editor_user->ID ) );

		$this->assertTrue( $this->dev_tools_user_access->update_user_setting( $admin_user->ID ) );
		$this->assertTrue( $this->dev_tools_user_access->get_user_enabled( $admin_user ) );
		$_POST[ DevToolsUserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED ] = null;
		$this->assertTrue( $this->dev_tools_user_access->update_user_setting( $admin_user->ID ) );
		$this->assertFalse( $this->dev_tools_user_access->get_user_enabled( $admin_user ) );
	}

	/**
	 * Tests DevToolsUserAccess::rest_get_dev_tools_enabled
	 *
	 * @covers DevToolsUserAccess::rest_get_dev_tools_enabled
	 */
	public function test_rest_get_dev_tools_enabled() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$user = self::factory()->user->create_and_get( [ 'role' => 'author' ] );

		$this->assertFalse( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user->ID ] ) );

		$user->set_role( 'administrator' );
		$this->assertTrue( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user->ID ] ) );

		update_user_meta( $user->ID, 'amp_dev_tools_enabled', 'false' );
		$this->assertFalse( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user->ID ] ) );

		update_user_meta( $user->ID, 'amp_dev_tools_enabled', 'true' );
		$this->assertTrue( $this->dev_tools_user_access->rest_get_dev_tools_enabled( [ 'id' => $user->ID ] ) );
	}

	/**
	 * Tests DevToolsUserAccess::rest_update_dev_tools_enabled
	 *
	 * @covers DevToolsUserAccess::rest_update_dev_tools_enabled
	 */
	public function test_rest_update_dev_tools_enabled() {
		$author_user = self::factory()->user->create_and_get( [ 'role' => 'author' ] );

		wp_set_current_user( $author_user->ID );

		$update = $this->dev_tools_user_access->rest_update_dev_tools_enabled( true, $author_user );
		$this->assertInstanceOf( WP_Error::class, $update );

		$administrator_user = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		wp_set_current_user( $administrator_user->ID );
		$this->assertInstanceOf( WP_Error::class, $this->dev_tools_user_access->rest_update_dev_tools_enabled( true, $author_user ) );

		$this->assertTrue( $this->dev_tools_user_access->rest_update_dev_tools_enabled( true, $administrator_user ) );
		$this->assertEquals( 'true', get_user_meta( $administrator_user->ID, 'amp_dev_tools_enabled', true ) );

		$this->assertTrue( $this->dev_tools_user_access->rest_update_dev_tools_enabled( false, $administrator_user ) );
		$this->assertEquals( 'false', get_user_meta( $administrator_user->ID, 'amp_dev_tools_enabled', true ) );
	}
}
