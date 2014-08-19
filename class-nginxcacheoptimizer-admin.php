<?php
/**
 * NGINX Cache Optimizer
 *
 * @package   NGINX Cache Optimizer
 * @author    George Penkov
 * @link      http://www.getclouder.com/
 * @copyright 2014 getClouder
 */

/** NGINX Cache Optimizer purge cache admin class */

class NGINXCacheOptimizer_Admin {

	/**
	 * Slug of the plugin screen.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	protected $page_hook = null;

	/**
	 * Holds the options object
	 *
	 * @since 1.1.0
	 *
	 * @type NGINXCacheOptimizer_Options
	 */
	protected $options_handler;

	/**
	 * Assign dependencies.
	 *
	 * @since 1.1.0
	 *
	 * @param NGINXCacheOptimizer_Options $options_handler
	 */
	public function __construct( $options_handler ) {
		$this->options_handler = $options_handler;
	}

	/**
	 * Initialize the administration functions.
	 *
	 * @since 1.1.0
	 */
	public function run() {
		// Add the admin page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Load admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Ajax callback
		add_action( 'wp_ajax_nginxcacheoptimizer-purge', array( 'NGINXCacheOptimizer_Cacher', 'purge_cache' ) );
		add_action( 'wp_ajax_nginxcacheoptimizer-blacklist-update', array( $this, 'update_blacklist' ) );
		add_action( 'wp_ajax_nginxcacheoptimizer-memcached-update', array( $this, 'update_memcached' ) );
		add_action( 'wp_ajax_nginxcacheoptimizer-nginx-update', array( $this, 'update_nginx_dir' ) );
		add_action( 'wp_ajax_nginxcacheoptimizer-parameter-update', array( $this, 'update_parameter' ) );
	}

	/**
	 * Updates the nginx cache dir from ajax request
	 *
	 * @since 1.1.0
	 */
	public function update_nginx_dir() {
		$path = realpath($_POST['dir']);
		if (!$path)
			die('Invalid path');
		if (!is_writable($path))
			die('Path is not writeable');

		if ($this->options_handler->get_option('nginx_cache') == $path)
			die('1');
		if ($this->options_handler->update_option('nginx_cache',$path))
			die('1');

		die('Failed to update path');
	}

	/**
	 * Updates the memcached details from ajax request
	 *
	 * @since 1.1.0
	 */
	public function update_memcached() {
		if (!filter_var( $_POST['ip'], FILTER_VALIDATE_IP ))
			die('Invalid IP address');
		if (preg_match('/[^0-9]/', $_POST['port']) || !absint($_POST['port']))
			die('Invalid Port');


		$this->options_handler->update_option('memcached_ip',$_POST['ip']);
		$this->options_handler->update_option('memcached_port',absint($_POST['port']));

		die('1');
	}

	/**
	 * Updates a param from ajax request
	 *
	 * @since 1.1.0
	 */
	public function update_parameter() {
		$paramTranslator = array(
			'dynamic-cache' 	=> 'enable_cache',
			'memcached'			=> 'enable_memcached',
			'autoflush-cache'	=> 'autoflush_cache',
		);

		$paramName = $paramTranslator[$_POST['parameterName']];
		$currentValue = (int)$this->options_handler->get_option($paramName);
		$toggledValue = (int)!$currentValue;

		//if cache is turned on or off it's a good idea to flush it on right away
		if ($paramName == 'enable_cache') {
			global $nginxcacheoptimizer_cacher;
			$nginxcacheoptimizer_cacher->purge_cache();
		}

		if ($paramName == 'enable_memcached') {
			global $nginxcacheoptimizer_memcache;
			//check if we can actually enable memcached and display error if not
			if ($toggledValue == 1) {
				if (!$nginxcacheoptimizer_memcache->check_and_create_memcached_dropin())
					die( "Incorrect memcache settings, please check server's IP and memcache port" );
			}
			else {
				if (!$nginxcacheoptimizer_memcache->remove_memcached_dropin())
					die( "Could not disable memcache!" );
			}
		}

		if ($this->options_handler->update_option($paramName,$toggledValue))
			die((string)$toggledValue);
		else
			die((string)$currentValue);
	}
	/**
	 * Updates the blacklist from ajax request
	 *
	 * @since 1.1.0
	 */
	public function update_blacklist() {
		die((int)$this->options_handler->update_option('blacklist',$_POST['blacklist']));
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since 1.1.0
	 */
	public function enqueue_admin_styles() {
		if ( ! isset( $this->page_hook ) )
			return;

		$screen = get_current_screen();
		if ( $screen->id == $this->page_hook )
			wp_enqueue_style( NGINXCacheOptimizer::PLUGIN_SLUG . '-admin', plugins_url( 'css/admin.css', __FILE__ ), array(), NGINXCacheOptimizer::VERSION );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since 1.1.0
	 *
	 * @return null Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		if ( ! isset( $this->page_hook ) )
			return;

		$screen = get_current_screen();
		if ( $screen->id == $this->page_hook ) {
			wp_enqueue_script( NGINXCacheOptimizer::PLUGIN_SLUG . '-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), NGINXCacheOptimizer::VERSION, true );
			$strings = array(
				'savechanges'  => __( 'Save changes', 'nginxcacheoptimizer' ),
				'purge'   => __( 'Purge the Cache', 'nginxcacheoptimizer' ),
				'purging' => __( 'Purging, please wait...', 'nginxcacheoptimizer' ),
				'updating' => __( 'Updating, please wait...', 'nginxcacheoptimizer' ),
				'updated'  => __( 'Update the Exclude List' ),
				'purged'  => __( 'Successfully Purged', 'nginxcacheoptimizer' )
			);
			wp_localize_script( NGINXCacheOptimizer::PLUGIN_SLUG . '-admin', 'nginxcacheoptimizerL10n', $strings );
		}
	}

	/**
	 * Register the top level page into the WordPress admin menu.
	 *
	 * @since 1.1.0
	 */
	public function add_plugin_admin_menu() {
		$this->page_hook = add_menu_page(
			__( 'NGINX Cache Optimizer', 'nginxcacheoptimizer' ), // Page title
			__( 'NGINX Cache Optimizer', 'nginxcacheoptimizer' ),    // Menu item title
			'manage_options',
			NGINXCacheOptimizer::PLUGIN_SLUG,   // Page slug
			array( $this, 'display_plugin_admin_page' ),
			plugins_url('nginx-cache-optimizer/css/cacher-white-1.svg')
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since 1.1.0
	 */
	public function display_plugin_admin_page() {
		include 'views/nginxcacheoptimizer.php';
	}
}
