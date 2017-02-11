<?php

/**
 *
 * Plugin Name:             Eventpresso
 * Plugin URI:              http://uberpress.io/plugins/eventpresso
 *
 * Description:
 * Version:                 0.0.1
 *
 * Author:                  UberPress
 * Author URI:              http://uberpress.io
 * Author Email:            info@uberpress.io
 *
 * Text Domain:             eventpresso
 * Domain Path:             lang/
 *
 * Bitbucket Plugin URI:    uberpress/eventpresso
 *
 */

final class Eventpresso {

	/**
	 * Eventpresso version.
	 *
	 * @var string
	 */
	public $version = '0.0.1';

	/**
	 * The single instance of the class.
	 *
	 * @var Eventpresso
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Holds all modules
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Main Eventpresso Instance.
	 *
	 * Ensures only one instance of Eventpresso is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see Eventpresso()
	 * @return Eventpresso - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'eventpresso' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'eventpresso' ), '2.1' );
	}

	/**
	 * Auto-load in-accessible properties on demand.
	 *
	 * @param mixed   $key
	 * @return mixed
	 */
	public function __get( $key ) {
		if(isset($this->modules[$key])) {
			return $this->modules[$key];
		}
	}

	/**
	 * Eventpresso Constructor.
	 */
	public function __construct() {
		if ( $this->dependencies_met() ) {
			$this->define_constants();
			$this->includes();
			$this->init_modules();
			$this->init_hooks();

			do_action( 'eventpresso_loaded' );
		} else {
			add_action( 'admin_notices', function() { ?>
				<div class="notice notice-error">
					<p><?php _e( 'Your install does not match all requirements for Eventpresso to work properly.', 'eventpresso' ); ?></p>
				</div>
			<?php } );
		}
	}

	/**
	 * Check if dependencies are loaded
	 *
	 * @return boolean
	 */
	protected function dependencies_met() {
		return true;
	}

	/**
	 * Define Eventpresso Constants.
	 */
	protected function define_constants() {
		$this->define( 'EVENTPRESSO_PLUGIN_FILE', __FILE__ );
		$this->define( 'EVENTPRESSO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'EVENTPRESSO_VERSION', $this->version );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		// Include the composer autoloader
		require $this->get_dir() . '/vendor/autoload.php';

		// Include rest endpoints
		require $this->get_dir() . '/includes/api/class-events.php'; // For events

		// Include the post type class
		include_once $this->get_dir() . 'includes/class-post-type.php';

		// Include the invited abstraction layer
		include_once $this->get_dir() . 'includes/models/class-invited.php';

		// Include the metabox class
		include_once $this->get_dir() . 'includes/class-metabox.php';

	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since  1.0
	 */
	protected function init_hooks() {
	}

	/**
	 * Initiate all modules.
	 *
	 * @since  1.0
	 */
	protected function init_modules() {
		// create a new instance of the cpt class
		$this->modules['cpt'] = new Eventpresso_Post_Type;

		// create a new instance of the cpt class
		$this->modules['invited'] = new Eventpresso_Invited;
	}

	/**
	 * Get the path to the plugin
	 * @return string
	 */
	public function get_dir() {
		return plugin_dir_path( EVENTPRESSO_PLUGIN_FILE );
	}

	/**
	 * Get the url to the plugin
	 * @return string
	 */
	public function get_url() {
		return plugin_dir_url( EVENTPRESSO_PLUGIN_FILE );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string  $name
	 * @param string|bool $value
	 */
	protected function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param string  $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	protected function is_request( $type ) {
		switch ( $type ) {
		case 'admin' :
			return is_admin();
		case 'ajax' :
			return defined( 'DOING_AJAX' );
		case 'cron' :
			return defined( 'DOING_CRON' );
		case 'frontend' :
			return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

}

/**
 * Main instance of Eventpresso.
 *
 * Returns the main instance of Eventpresso to prevent the need to use globals.
 *
 * @since  2.1
 * @return Eventpresso
 */
function Eventpresso() {
	return Eventpresso::instance();
}

add_action( 'plugins_loaded', function() {
	// Global for backwards compatibility.
	$GLOBALS['eventpresso'] = Eventpresso();
} );

register_activation_hook(__FILE__, function() {
    require __DIR__ . '/includes/class-database.php';
    Eventpresso_Database::setup_database();
});
