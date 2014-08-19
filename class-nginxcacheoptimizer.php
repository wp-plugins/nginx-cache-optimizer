<?php
/**
 * NGINX Cache Optimizer
 *
 * @package   NGINX Cache Optimizer
 * @author    George Penkov
 * @link      http://www.getclouder.com/
 * @copyright 2014 getClouder
 */

/** NGINXCacheOptimizer main plugin class */

class NGINXCacheOptimizer {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.1.0
	 *
	 * @type string
	 */
	const VERSION = '1.1.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since 1.1.0
	 *
	 * @type string
	 */
	const PLUGIN_SLUG = 'nginxcacheoptimizer';

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
	 */
	public function __construct( $options_handler ) {
		$this->options_handler = $options_handler;
	}

	/**
	 * Initialize the class by hooking and running methods.
	 *
	 * @since 1.1.0
	 *
	 * @uses NGINXCacheOptimizer::load_plugin_textdomain() Allow localised language files to be applied.
	 * @uses NGINXCacheOptimizer::activate_new_site()      Handle activation on multisite.
	 * @uses NGINXCacheOptimizer::set_headers_cookies()    Set headers and cookies.
	 */
	public function run() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		$this->set_headers_cookies();
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since 1.1.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is
	 *                              disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide  ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_activate();
				}
				restore_current_blog();
			} else {
				self::single_activate();
			}
		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since 1.1.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is
	 *                              disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $network_wide ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_deactivate();
				}
				restore_current_blog();
			} else {
				self::single_deactivate();
			}
		} else {
			self::single_deactivate();
		}
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since 1.1.0
	 *
	 * @param int $blog_id ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) )
			return;

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 *  * not archived
	 *  * not spam
	 *  * not deleted
	 *
	 * @since 1.1.0
	 *
	 * @return array|false The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {
		global $wpdb;

		$sql = "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'";

		return $wpdb->get_col( $sql );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since 1.1.0
	 */
	private static function single_activate() {
		$nginxcacheoptimizer_options  = new NGINXCacheOptimizer_Options;
		$nginxcacheoptimizer          = new NGINXCacheOptimizer( $nginxcacheoptimizer_options );
		if ( ! $nginxcacheoptimizer_options->get_option() )
			$nginxcacheoptimizer_options->init_options();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since 1.1.0
	 */
	private static function single_deactivate() {
		// TODO: Define deactivation functionality here?
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.1.0
	 */
	public function load_plugin_textdomain() {
		$domain = self::PLUGIN_SLUG;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
     * Check if url is in caching blacklist
	 *
	 * @since 1.1.1
     * @return bool
     */
    protected function is_url_blacklisted() {
    	global $nginxcacheoptimizer_environment;
        $blacklistArray = explode("\n",$this->options_handler->get_blacklist());

        $blacklistRegexArray = array();
        $indexIsBlacklisted = false;
        foreach($blacklistArray as $key=>$row)
        {
        	$row = trim($row);

        	if ($row != '/' && $quoted = preg_quote($row,'/'))
        		$blacklistRegexArray[$key] = $quoted;

        	if ($row == '/')
        		$indexIsBlacklisted = true;
        }

        if ($indexIsBlacklisted && $_SERVER['REQUEST_URI'] == $nginxcacheoptimizer_environment->get_application_path())
        	return true;

        if (empty($blacklistRegexArray))
        	return false;

        $blacklistRegex = '/('.implode('|',$blacklistRegexArray) . ')/i';

        return preg_match($blacklistRegex, $_SERVER['REQUEST_URI']);
    }

	/**
	 * Set headers and cookies.
	 *
	 * @since 1.1.0
	 */
	protected function set_headers_cookies() {
		if ( ! $this->options_handler->is_enabled( 'enable_cache' ) || $this->is_url_blacklisted()) {
			header( 'X-Cache-Enabled: False' );
			return;
		}

		header( 'X-Cache-Enabled: True' );

		// Logged In Users
		if ( is_user_logged_in() || ( ! empty( $_POST['wp-submit'] ) && 'Log In' === $_POST['wp-submit'] ) ) {
			// Enable the cache bypass for logged users by setting a cache bypass cookie
 			setcookie( 'nCacheBypass', 1, time() + 100 * MINUTE_IN_SECONDS, '/' );
		} elseif ( ! is_user_logged_in() || 'logout' === $_GET['action'] ) {
			setcookie( 'nCacheBypass', 0, time() - HOUR_IN_SECONDS, '/' );
		}
	}

}