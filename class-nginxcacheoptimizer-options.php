<?php
/**
 * Super Cacher NGINX
 *
 * @package   Super Cacher NGINX
 * @author    George Penkov
 * @link      http://www.getclouder.com/
 * @copyright 2014 getClouder
 */

/** NGINXCacheOptimizer options class */

class NGINXCacheOptimizer_Options {

	/**
	 * Holds the options key, under which all NGINXCacheOptimizer settings are stored.
	 *
	 * @since 1.1.0
	 *
	 * @type string
	 */
	protected $options_key = 'nginxcacheoptimizer';

	/**
	 * Retrieve the whole array of settings, or one individual value.
	 *
	 * @since 1.1.0
	 *
	 * @todo Could implement an extra layer of caching here, to avoid calls to get_option().
	 *
	 * @todo Split get_option() out to get_all_options(), so return type is consistent?
	 *
	 * @param  string $key Optional. Setting field key.
	 *
	 * @return array|int
	 */
	public function get_option( $key = null ) {
		$options = get_option( $this->options_key );
		if ( $key && isset( $options[ $key ] ) )
			return !in_array($key,array('nginx_cache','memcached_ip')) ? (int) $options[ $key ] : $options[ $key ];
		return $options;
	}

	/**
	 * Enable a single boolean setting.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $key Setting field key.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function enable_option( $key ) {
		return $this->update_option( $key, 1 );
	}

	/**
	 * Disable a single boolean setting.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $key Setting field key.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function disable_option( $key ) {
		return $this->update_option( $key, 0 );
	}

	/**
	 * Update a single setting.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $key   Setting field key.
	 * @param  mixed  $value Setting field value.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function update_option( $key, $value ) {
		$options = $this->get_option();
		$options[ $key ] = $value;
		return update_option( $this->options_key, $options );
	}

	/**
	 * Check if a single boolean setting is enabled.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $key Setting field key.
	 *
	 * @return boolean True if the setting is enabled, false otherwise.
	 */
	public function is_enabled( $key ) {
		if ( 1 === $this->get_option( $key ) )
			return true;
		return false;
	}

	/**
	 * Initialize the values in the single setting array.
	 *
	 * @since 1.1.0
	 */
	public function get_defaults() {
		return array(
			'enable_cache'     	=> 0,
			'autoflush_cache'  	=> 1,
			'enable_memcached' 	=> 0,
			'nginx_cache'		=> '/var/run/nginx-cache',
			'memcached_ip'		=> '127.0.0.1',
			'memcached_port'	=> 11211,
		);
	}

	/**
	 * Initialize the values in the single setting array.
	 *
	 * @since 1.1.0
	 */
	public function init_options() {
		add_option( $this->options_key, $this->get_defaults() );
	}

	/**
	 * Gets the blacklisted urls
	 *
	 * @since 1.1.1
	 * @return string The blacklist
	 */
	public function get_blacklist()
	{
		$options = get_option( $this->options_key );

		if ( isset( $options[ 'blacklist' ] ) && strlen($options[ 'blacklist' ]) )
			return $options[ 'blacklist' ];

		return '';
	}
}
