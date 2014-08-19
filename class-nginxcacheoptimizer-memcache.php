<?php
/**
 * NGINX Cache Optimizer
 *
 * @package   NGINX Cache Optimizer
 * @author    George Penkov
 * @link      http://www.getclouder.com/
 * @copyright 2014 getClouder
 */

/** NGINXCacheOptimizer memcached class  */

class NGINXCacheOptimizer_Memcache {

	/**
	 * Flag which raises when we already flushed on this page exec.
	 *
	 * @since Unknown
	 *
	 * @type bool
	 */
	private static $_flushed = false;

	/**
	 * Holds the options object.
	 *
	 * @since 1.1.0
	 *
	 * @type NGINXCacheOptimizer_Options
	 */
	protected $options_handler;

	/**
	 * Holds the environment object.
	 *
	 * @since 1.1.0
	 *
	 * @type NGINXCacheOptimizer_Environment
	 */
	protected $environment;

	/**
	 * Assign depdndencies.
	 *
	 * @since 1.1.0
	 *
	 * @param NGINXCacheOptimizer_Options     $options_handler
	 * @param NGINXCacheOptimizer_Environment $environment
	 */
	public function __construct( $options_handler, $environment ) {
		$this->options_handler = $options_handler;
		$this->environment     = $environment;
	}

	/**
	 * Initialize the class by hooking and running methods.
	 *
	 * @since 1.1.0
	 *
	 * @return null Return early and avoid any further interaction if accessing the script via CLI.
	 */
	public function run() {
		if ( $this->options_handler->is_enabled( 'enable_memcached' ) && !$this->check_if_dropin_exists()){
			if (!$this->check_and_create_memcached_dropin()){
				$this->options_handler->disable_option('enable_memcached');
			}
		}

		if ( !$this->options_handler->is_enabled( 'enable_memcached' ) && $this->check_if_dropin_exists())
		{
			if (!$this->remove_memcached_dropin()){
				$this->options_handler->enable_option('enable_memcached');
			}
		}
	}

	/**
	 * Check if the object-cache.php dropin file exists (is readable).
	 *
	 * @since 1.2.0
	 *
 	 * @return bool
	 */
	protected function check_if_dropin_exists(){
		return is_readable( $this->get_object_cache_file() );
	}

	/**
	 * Get the path to where the object cache dropin should be.
	 *
	 * @since 1.1.0
	 */
	protected function get_object_cache_file() {
		return trailingslashit( WP_CONTENT_DIR ) . 'object-cache.php';
	}

	/**
	 * Check and create a Memcached dropin.
	 *
	 *
	 * @since Unknown
	 *
	 * @return bool True on dropin creation, false otherwise.
	 */
	public function check_and_create_memcached_dropin() {
		$ip = $this->options_handler->get_option('memcached_ip');
		$port = $this->options_handler->get_option('memcached_port');

		$object_cache_file = $this->get_object_cache_file();

		$memcache = new Memcache;
 		@$memcache->connect( $ip, $port );

		if ( $this->memcached_connection_is_working( $memcache ) )
			return $this->create_memcached_dropin( $ip, $port );

		$this->remove_memcached_dropin();

		return false;
	}

	/**
	 * Check if a Memcached connection is working by setting and immediately getting a value.
	 *
	 * @since 1.1.0
	 *
	 * @param  object $connection Memcache object.
	 *
	 * @return bool True on retrieving exactly the value set, false otherwise.
	 */
	protected function memcached_connection_is_working( $connection ) {
		if ( ! $connection )
			return false;
		@$connection->set( 'NGINXCacheOptimizer_Memcached_Test', 'Test!1', MEMCACHE_COMPRESSED, 50 );
		if ( @$connection->get( 'NGINXCacheOptimizer_Memcached_Test' ) === 'Test!1' )
		{
			$connection->flush();
 			return true;
		}
 		return false;
	}

	/**
	 * Copy the Memcache template contents into object-cache.php, replacing IP and Port where needed.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $ip   Memcached IP.
	 * @param  string $port Memcached Port.
	 *
	 * @return bool True if the template was successfully copied, false otherwise.
	 */
	protected function create_memcached_dropin( $ip, $port ) {
		$object_cache_file = $this->get_object_cache_file();
		$template = file_get_contents( dirname( __FILE__ ) . '/memcache.tpl' );
		$find = '@changedefaults@';
 		$replace = "{$ip}:{$port}";
 		$new_object_cache = str_replace( $find, $replace, $template );
 		if ( file_put_contents( $object_cache_file, $new_object_cache ) )
 			return true;
 		return false;
	}

	/**
	 * Remove the object-cache.php file.
	 *
	 * @since Unknown
	 *
	 * @return bool True on successful removal, false otherwise.
	 */
	public function remove_memcached_dropin() {
		$object_cache_file = $this->get_object_cache_file();

		if ( is_readable( $object_cache_file ) ) {
			unlink( $object_cache_file );
			return true;
		}
		return false;
	}
}