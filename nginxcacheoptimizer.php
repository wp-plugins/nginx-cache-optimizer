<?php
/**
 * NGINX Cache Optimizer
 *
 * @package           NGINX Cache Optimizer
 * @author            getclouder
 * @link              http://www.getclouder.com/
 *
 * @wordpress-plugin
 * Plugin Name:       NGINX Cache Optimizer
 * Description:       Through the settings of this plugin you can manage how your Wordpress interracts with Nginx cache. Before you can use this plugin you need to have Nginx installed and configured.
 * Version:           1.0
 * Author:            George Penkov
 * Text Domain:       nginxcacheoptimizer
 * Domain Path:       /languages
 */
 

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// @todo Consider an autoloader?
require plugin_dir_path( __FILE__ ) . 'class-nginxcacheoptimizer.php';
require plugin_dir_path( __FILE__ ) . 'class-nginxcacheoptimizer-options.php';
require plugin_dir_path( __FILE__ ) . 'class-nginxcacheoptimizer-environment.php';
require plugin_dir_path( __FILE__ ) . 'class-nginxcacheoptimizer-cacher.php';
require plugin_dir_path( __FILE__ ) . 'class-nginxcacheoptimizer-memcache.php';
require plugin_dir_path( __FILE__ ) . 'class-nginxcacheoptimizer-admin.php';


// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'NGINXCacheOptimizer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'NGINXCacheOptimizer', 'deactivate' ) );

add_action( 'plugins_loaded','nginxcacheoptimizer_start' );
/**
 * Initialise the classes in this plugin.
 *
 * @since 1.1.0
 *
 * @todo Consider moving this to a dependency injection container, so we can avoid globals?
 */
function nginxcacheoptimizer_start() {

 	global $nginxcacheoptimizer, $nginxcacheoptimizer_options, $nginxcacheoptimizer_environment, $nginxcacheoptimizer_memcache,
 	$nginxcacheoptimizer_admin, $nginxcacheoptimizer_cacher;

	$nginxcacheoptimizer_options        	= new NGINXCacheOptimizer_Options;
	$nginxcacheoptimizer               	= new NGINXCacheOptimizer( $nginxcacheoptimizer_options );
	$nginxcacheoptimizer_environment    	= new NGINXCacheOptimizer_Environment( $nginxcacheoptimizer_options );
	$nginxcacheoptimizer_admin    			= new NGINXCacheOptimizer_Admin( $nginxcacheoptimizer_options );
	$nginxcacheoptimizer_memcache       	= new NGINXCacheOptimizer_Memcache( $nginxcacheoptimizer_options, $nginxcacheoptimizer_environment );
	$nginxcacheoptimizer_cacher    		= new NGINXCacheOptimizer_Cacher( $nginxcacheoptimizer_options, $nginxcacheoptimizer_environment );

	$nginxcacheoptimizer->run();
	$nginxcacheoptimizer_admin->run();

	if ( $nginxcacheoptimizer_environment->cache_is_enabled() ){
		if ( $nginxcacheoptimizer_environment->autoflush_enabled() ){
			$nginxcacheoptimizer_cacher->run();
		}
	}

	if ( $nginxcacheoptimizer_environment->memcached_is_enabled() ){
		$nginxcacheoptimizer_memcache->run();
	}
}
