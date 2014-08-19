<?php
/**
 * NGINX Cache Optimizer
 *
 * @package   NGINX Cache Optimizer
 * @author    George Penkov
 * @link      http://www.getclouder.com/
 * @copyright 2014 getClouder
 */

/** NGINXCacheOptimizer cacher class */

class NGINXCacheOptimizer_Cacher {

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
	 * Assign dependencies.
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

	/**purge_cache
	 * Initialize the class by hooking and running methods.
	 *
	 * @since 1.1.0
	 *
	 * @return null Return early and avoid any further interaction if accessing the script via CLI.
	 */
	public function run() {
		if ( $this->environment->is_using_cli() )
			return;

		$this->assign_hooks_for_autoflush();
	}

    /**
     * Call the cache server to purge the cache.
     *
     * Since this method is also called via Ajax (where $this doesn't exist --it does not exist because the method is invoked as static--), we replace the usual $this with the global
     * variable that contains an instance of this class. Not ideal but apparently works.
     *
     *
     * @since Unknown
     *
     * @return null
     */
	public function purge_cache() {
		global $nginxcacheoptimizer_cacher,$nginxcacheoptimizer_options;
		if ( $nginxcacheoptimizer_cacher->environment->is_using_cli() )
			return;

		if ( ! $nginxcacheoptimizer_cacher->environment->cache_is_enabled() )
			return;

		if ( self::$_flushed )
			return;

		$nginxCachePath = $nginxcacheoptimizer_options->get_option('nginx_cache');
		$cacheFiles = scandir($nginxCachePath);

		foreach($cacheFiles as $file)
		{
			if (in_array($file,array('.','..')))
				continue;
			self::rrmdir($nginxCachePath.'/'.$file);
		}

		return true;
	}

	public static function rrmdir($dir) {
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file))
				self::rrmdir($file);
			else
				unlink($file);
		}
		rmdir($dir);
	}

	/**
	 * Print notification in the admin section, or via AJAX
	 *
	 * @since Unknown
	 *
	 * @param string|bool $message Message to be displayed if purge is successful. If this param is false no output would be done
	 * @return null
	 */
	public static function notify( $message ) {
		add_action( 'muplugins_loaded', NGINXCacheOptimizer_Memcache::notice( $message ) );
	}

	/**
	 * Add autoflush-related hooks.
	 *
	 * The check for autoflush disabled is down here, instead of within each callback - autoflush being enabled or not
	 * is unlikely to change between when plugin_loaded hook, and each of the listed hooks.
	 *
	 * @since Unknown
	 *
	 * @return null Return early if cache is not enabled, or autoflush is disabled.
	 */
	public function assign_hooks_for_autoflush() {

		add_action( 'save_post', array( $this,'hook_add_post' ) );
		add_action( 'edit_post', array( $this,'hook_add_post' ) );
		add_action( 'publish_phone', array( $this,'hook_add_post' ) );
		add_action( 'publish_future_post', array( $this,'hook_add_post' ) );
		add_action( 'xmlrpc_publish_post', array( $this,'hook_add_post' ) );
		add_action( 'before_delete_post', array( $this,'hook_delete_post' ) );
		add_action( 'trash_post', array( $this,'hook_delete_post' ) );
		add_action( 'add_category', array( $this,'hook_add_category' ) );
		add_action( 'edit_category', array( $this,'hook_edit_category' ) );
		add_action( 'delete_category', array( $this,'hook_delete_category' ) );
		add_action( 'add_link', array( $this,'hook_add_link' ) );
		add_action( 'edit_link', array( $this,'hook_edit_link' ) );
		add_action( 'delete_link', array( $this,'hook_delete_link' ) );
		add_action( 'comment_post', array( $this,'hook_add_comment' ),10,2 );
		add_action( 'comment_unapproved_to_approved', array( $this,'hook_approve_unapprove_comment' ) );
		add_action( 'comment_approved_to_unapproved', array( $this,'hook_approve_unapprove_comment' ) );
		add_action( 'delete_comment', array( $this,'hook_delete_comment' ) );
		add_action( 'trash_comment', array( $this,'hook_delete_comment' ) );
		add_action( 'switch_theme', array( $this,'hook_switch_theme' ) );
		add_action( 'customize_save', array( $this,'hook_switch_theme' ) );
		add_action( 'automatic_updates_complete', array( $this,'hook_atomatic_update' ) );
		add_action( 'future_to_publish', array( $this,'scheduled_goes_live' ) );

		// @todo Move the rest of this to a new method - and document what events it is capturing!

		if ( ! empty( $_POST ) ) {

			if (isset($_POST['save-header-options']) || isset($_POST['removeheader'])
					|| isset($_POST['skip-cropping']) || (isset($_POST['submit']) && $_POST['submit'] == 'Crop and Publish')
					|| isset($_POST['remove-background']) || (isset($_POST['submit']) && $_POST['submit'] == 'Upload')
					|| isset($_POST['save-background-options']))
				$this->purge_cache();

			if ( isset( $_POST['action'] ) ) {
				if ( in_array( $_POST['action'], array( 'widgets-order','save-widget','delete-selected' ) ) )
					$this->purge_cache();

				if ( isset( $_POST['submit'] ) && 'update' === $_POST['action'] ) {
					if ( in_array( $_POST['submit'], array( 'Update File', 'Save Changes' ) ) )
						$this->purge_cache();
				}

			}

			// Settings -> Permalinks
			if ( isset( $_POST['submit'] ) && 'Save Change' === $_POST['submit'] && isset( $_POST['_wp_http_referer'] ) ) {
				$ref = explode( '/', $_POST['_wp_http_referer'] );
				$ref = array_pop( $ref );

				if ( 'options-permalink.php' === $ref ) {
					if ( 'update' === $_POST['action'] && 'Save Changes' === $_POST['submit'] && 'permalinks' === $_POST['option_page'] )
						$this->purge_cache();
				}
			}

			if( isset( $_POST['save_menu'] ) ) {
				// Add Menu
				if( in_array( $_POST['save_menu'], array( 'Create Menu', 'Save Menu' ) ) )
					$this->purge_cache();

			}
		}

		if ( ! empty( $_GET ) && isset( $_GET['action'] ) ) {
			if ( isset( $_GET['menu'] ) && 'delete' === $_GET['action'] )
				$this->purge_cache();
			if ( isset( $_GET['plugin'] ) && 'activate' === $_GET['action'] )
				$this->purge_cache();
		}
	}

	/**
	 * Purge cache when a post is potentially made live.
	 *
	 * @since Unknown
	 *
	 * @param  int $post_id
	 */
	public function hook_add_post( $post_id ) {
		if ( $this->environment->post_data_is( 'Publish', 'publish' ) || $this->environment->action_data_is( 'editpost' ) || $this->environment->action_data_is( 'inline-save' ) )
			$this->purge_cache();
	}

	/**
	 * Purge cache when a post is deleted.
	 *
	 * @since Unknown
	 *
	 * @param  int $post_id
	 */
	public function hook_delete_post( $post_id ) {
		if( isset( $_GET['action'] ) && ( 'delete' === $_GET['action'] || 'trash' === $_GET['action'] ) )
			$this->purge_cache();
	}

	/**
	 * Purge cache when a category is added.
	 *
	 * @since Unknown
	 *
	 * @param  int $cat_id
	 */
	public function hook_add_category( $cat_id ) {
		if ( $this->environment->action_data_is( 'add-tag' ) )
			$this->purge_cache();
	}

	/**
	 * Purge cache when a category is edited.
	 *
	 * @since Unknown
	 *
	 * @param  int $cat_id
	 */
	public function hook_edit_category( $cat_id ) {
		if ( $this->environment->action_data_is( 'editedtag' ) )
			$this->purge_cache();
	}

	/**
	 * Purge cache when a category is deleted.
	 *
	 * @since Unknown
	 *
	 * @param  int $cat_id
	 */
	public function hook_delete_category( $cat_id ) {
		if ( $this->environment->action_data_is( 'delete-tag' ) )
			$this->purge_cache();
	}

	/**
	 * Purge cache when a link is added.
	 *
	 * @since Unknown
	 *
	 * @param  int $link_id
	 */
	public function hook_add_link( $link_id ) {
		if ( $this->environment->action_data_is( 'add' ) && $this->environment->post_data_is( 'Add Link', 'save' ) )
			$this->purge_cache();
	}

	/**
	 * Purge cache when a link is edited.
	 *
	 * @since Unknown
	 *
	 * @param  int $link_id
	 */
	public function hook_edit_link( $link_id ) {
		if ( $this->environment->action_data_is( 'editedtag' ) && $this->environment->post_data_is( 'Update', 'submit' ) )
			$this->purge_cache();
	}

	/**
	 * Purge cache when a link is deleted.
	 *
	 * @since Unknown
	 *
	 * @param  int $link_id
	 */
	public function hook_delete_link( $link_id ) {
		if ( $this->environment->action_data_is( 'delete-tag' ) && $this->environment->post_data_is( 'link_category', 'taxonomy' ) )
			$this->purge_cache();
	}

	/**
	 * Purge cache when a comment is added.
	 *
	 * @since Unknown
	 *
	 * @param  int    $comment_id
	 * @param  string $status
	 */
	public function hook_add_comment( $comment_id, $status = null ) {
		if ( isset( $_POST['comment_post_ID'] ) ) {
			$comment = get_comment( $comment_id );

			//  Purge post page
			if ( $comment )
				$this->purge_cache();
		}
	}

	/**
	 * Purge cache when a comment is approved or unapproved.
	 *
	 * @since Unknown
	 *
	 * @param  int    $comment_id
	 * @param  string $status
	 */
	public function hook_approve_unapprove_comment( $comment_id, $status = null ) {
		if ( isset( $_POST['id'] ) ) {
			$comment = get_comment( $comment_id );

			//  Purge post page
			if( $comment )
				$this->purge_cache();
		}
	}

	/**
	 * Purge cache when a comment is deleted.
	 *
	 * @since Unknown
	 *
	 * @param  int    $comment_id
	 */
	public function hook_delete_comment( $comment_id ) {
		$comment_actions = array( 'dim-comment','delete-comment' );

		if ( ( isset( $_POST['action'] ) && isset( $_POST['id'] ) && in_array( $_POST['action'], $comment_actions ) )
			|| ( isset( $_GET['action'] ) && isset( $_GET['c'] ) && 'trashcomment' === $_GET['action'] ) ) {
			$comment = get_comment( $_POST['id'] );
			if ( $comment )
				$this->purge_cache();
		}
	}

	/**
	 * Purge cache when the theme is switched.
	 *
	 * @since Unknown
	 */
	public function hook_switch_theme() {
		$this->purge_cache();
	}

	/**
	 * Purge cache when the Automatic Updates are completd.
	 *
	 * @since 3.8.1
	 */
	public function hook_atomatic_update() {
		$this->purge_cache();
	}

	/**
	 * Purge cache when Scheduled post becomes Published
	 *
	 * @since 3.8.1
	 */
	public function scheduled_goes_live() {
		$this->purge_cache();
	}
}