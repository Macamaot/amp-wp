<?php
/**
 * Tests for AMPPlugins
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\AMPPlugins;
use AmpProject\AmpWP\Tests\TestCase;
use stdClass;

/**
 * Tests for AMPPlugins.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\AMPPlugins
 */
class AMPPluginsTest extends TestCase {

	/**
	 * Instance of AMPPlugins
	 *
	 * @var AMPPlugins
	 */
	public $instance;

	/**
	 * Flag for AMP-compatible plugins file initially exists or not.
	 *
	 * @var bool
	 */
	protected $is_file_exists = false;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {

		parent::setUp();

		global $wp_scripts, $wp_styles;
		$wp_scripts = null;
		$wp_styles  = null;

		$this->instance = new AMPPlugins();
	}

	/**
	 * @covers ::get_plugins()
	 */
	public function test_get_plugins() {

		$plugins = $this->instance->get_plugins();

		$expected_plugins = include TESTS_PLUGIN_DIR . '/includes/ecosystem-data/plugins.php';

		$expected = array_map(
			static function ( $theme ) {

				return AMPPlugins::normalize_plugin_data( $theme );
			},
			$expected_plugins
		);

		$this->assertEquals( $expected, $plugins );
	}

	/**
	 * @covers ::normalize_plugin_data()
	 */
	public function test_normalize_plugin_data() {

		$input = [
			'name'    => 'Plugin Name',
			'slug'    => 'plugin-name',
			'ratings' => [
				'1' => 10,
				2   => 45,
			],
			'icons'   => [
				'1x' => 'http://sample.test/plugin-icon.png',
			],
		];

		$expected = [
			'name'                     => 'Plugin Name',
			'slug'                     => 'plugin-name',
			'version'                  => '',
			'author'                   => '',
			'author_profile'           => '',
			'requires'                 => '',
			'tested'                   => '',
			'requires_php'             => '',
			'rating'                   => 0,
			'ratings'                  => [
				1 => 10,
				2 => 45,
				3 => 0,
				4 => 0,
				5 => 0,
			],
			'num_ratings'              => 0,
			'support_threads'          => 0,
			'support_threads_resolved' => 0,
			'active_installs'          => 0,
			'downloaded'               => 0,
			'last_updated'             => '',
			'added'                    => '',
			'homepage'                 => '',
			'short_description'        => '',
			'description'              => '',
			'download_link'            => '',
			'tags'                     => [],
			'donate_link'              => '',
			'icons'                    => [
				'1x'  => 'http://sample.test/plugin-icon.png',
				'2x'  => '',
				'svg' => '',
			],
			'wporg'                    => false,
		];

		$this->assertEquals(
			$expected,
			AMPPlugins::normalize_plugin_data( $input )
		);
	}

	/**
	 * @covers ::get_registration_action()
	 */
	public function test_get_registration_action() {

		$this->assertEquals( 'current_screen', AMPPlugins::get_registration_action() );
	}

	/**
	 * @covers ::is_needed()
	 */
	public function test_is_needed() {

		// Test 1: None admin request.
		$this->assertFalse( AMPPlugins::is_needed() );

		// Test 2: Admin request.
		set_current_screen( 'index.php' );
		$this->assertTrue( AMPPlugins::is_needed() );

		set_current_screen( 'front' );
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {

		$this->instance->register();
		$this->assertFalse( has_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_scripts' ] ) );

		$this->assertEquals(
			10,
			has_filter( 'install_plugins_tabs', [ $this->instance, 'add_tab' ] )
		);
		$this->assertEquals(
			10,
			has_filter(
				'install_plugins_table_api_args_px_enhancing',
				[ $this->instance, 'tab_args' ]
			)
		);
		$this->assertEquals(
			10,
			has_filter( 'plugins_api', [ $this->instance, 'plugins_api' ] )
		);
		$this->assertEquals(
			10,
			has_filter( 'plugin_install_action_links', [ $this->instance, 'action_links' ] )
		);
		$this->assertEquals(
			10,
			has_filter( 'plugin_row_meta', [ $this->instance, 'plugin_row_meta' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'install_plugins_px_enhancing', 'display_plugins_table' )
		);

		set_current_screen( 'plugins' );
		$this->instance->register();
		$this->assertEquals(
			10,
			has_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_scripts' ] )
		);
		set_current_screen( 'front' );
	}

	/**
	 * @covers ::enqueue_scripts()
	 */
	public function test_enqueue_scripts() {
		$this->instance->enqueue_scripts();
		$this->assertTrue( wp_script_is( AMPPlugins::ASSET_HANDLE ) );
		$this->assertTrue( wp_style_is( 'amp-admin' ) );
	}

	/**
	 * @covers ::add_tab()
	 */
	public function test_add_tab() {

		$this->assertArrayHasKey(
			'px_enhancing',
			$this->instance->add_tab( [] )
		);
	}

	/**
	 * @covers ::tab_args()
	 */
	public function test_tab_args() {

		$output = $this->instance->tab_args();

		$this->assertArrayHasKey( 'px_enhancing', $output );
		$this->assertArrayHasKey( 'per_page', $output );
		$this->assertArrayHasKey( 'page', $output );
	}

	/**
	 * @covers ::plugins_api()
	 */
	public function test_plugins_api() {
		$this->instance->register();
		$response = new stdClass();

		// Test 1: Normal request.
		$response = $this->instance->plugins_api( $response, 'query_themes', [ 'per_page' => 36 ] );
		$this->assertEmpty( (array) $response );

		// Test 2: Request for PX compatible data.
		$args = [
			'px_enhancing' => true,
			'per_page'     => 36,
		];

		$response = $this->instance->plugins_api( $response, 'query_themes', $args );

		$this->assertIsArray( $response->info );
		$this->assertArrayHasKey( 'page', $response->info );
		$this->assertArrayHasKey( 'pages', $response->info );
		$this->assertArrayHasKey( 'results', $response->info );
		$this->assertIsArray( $response->plugins );
	}

	/**
	 * @covers ::action_links()
	 */
	public function test_action_links() {

		// Test 1: wporg plugins
		$actions     = [
			'test action',
		];
		$plugin_data = [
			'wporg' => true,
		];
		$output      = $this->instance->action_links( $actions, $plugin_data );
		$this->assertEquals( $actions, $output );

		// Test 2: wporg plugin.
		$plugin_data = [
			'wporg'    => false,
			'name'     => 'Sample Plugin',
			'homepage' => 'https://sample-plugin.com',
		];
		$output      = $this->instance->action_links( $actions, $plugin_data );
		$this->assertIsArray( $output );
		$this->assertEquals(
			sprintf(
				'<a href="%s" target="_blank" aria-label="Site link of %s">%s</a>',
				esc_url( $plugin_data['homepage'] ),
				esc_html( $plugin_data['name'] ),
				esc_html__( 'Visit site', 'amp' )
			),
			$output[0]
		);
	}

	/**
	 * @covers ::plugin_row_meta()
	 */
	public function test_plugin_row_meta() {

		$this->instance->register();

		$plugin_meta = [
			'meta_1',
			'meta_2',
		];

		// Test 1: None AMP plugin.
		$output = $this->instance->plugin_row_meta( $plugin_meta, '', [ 'slug' => 'example' ] );
		$this->assertEquals( $plugin_meta, $output );

		// Test 2: None AMP plugin.
		$output = $this->instance->plugin_row_meta( $plugin_meta, '', [ 'slug' => 'akismet' ] );

		$this->assertContains(
			'<span><span class="amp-logo-icon small"></span>&nbsp;AMP Compatible</span>',
			$output
		);
	}
}
