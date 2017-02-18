<?php

/**
 *
 * Plugin Name:             EventPresso
 * Plugin URI:              http://uberpress.io/plugins/eventpresso
 *
 * Description:             An event management plugin.
 * Version:                 0.0.1
 *
 * Author:                  UberPress
 * Author URI:              http://uberpress.io
 * Author Email:            info@uberpress.io
 *
 * Text Domain:             eventpresso
 * Domain Path:             lang/
 *
 */

final class EventPresso {

	/**
	 * EventPresso version.
	 *
	 * @var string
	 */
	public $version = '0.0.1';

	/**
	 * The single instance of the class.
	 *
	 * @var EventPresso
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Holds all modules
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Main EventPresso Instance.
	 *
	 * Ensures only one instance of EventPresso is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see EventPresso()
	 * @return EventPresso - Main instance.
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
	 * EventPresso Constructor.
	 */
	public function __construct() {
		if ( $this->dependencies_met() ) {
			$this->define_constants();
			$this->includes();
			$this->init_modules();
			$this->init_hooks();
			do_action( 'eventpresso/loaded' );
		} else {
			add_action( 'admin_notices', function() { ?>
				<div class="notice notice-error">
					<p><?php _e( 'Your install does not match all requirements for EventPresso to work properly.', 'eventpresso' ); ?></p>
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
	 * Define EventPresso Constants.
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

		// Include internationalization
		require $this->get_dir() . '/includes/class-i18n.php';

		// Include the post type class
		include_once $this->get_dir() . 'includes/class-post-type.php';

		// Include the metabox class
		include_once $this->get_dir() . 'includes/class-metabox.php';

		// The addon class
		include_once $this->get_dir() . '/includes/class-addon.php';

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
		$this->modules['cpt'] = new EventPresso_Post_Type;
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
 * Main instance of EventPresso.
 *
 * Returns the main instance of EventPresso to prevent the need to use globals.
 *
 * @since  2.1
 * @return EventPresso
 */
function EventPresso() {
	return EventPresso::instance();
}

$GLOBALS['eventpresso'] = EventPresso();
