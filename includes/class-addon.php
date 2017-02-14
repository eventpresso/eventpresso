<?php

class EventPresso_Addon {

	/**
	 * File of the addon
	 * @var string
	 */
	public $file;

	/**
	 * Name of the addon
	 * @var string
	 */
	public $id = 'eventpresso_addon';

	/**
	 * Name of the addon
	 * @var string
	 */
	public $name = 'EventPresso Addon';

	/**
	 * Addon version.
	 *
	 * @var string
	 */
	public $version = '0.0.1';

	/**
	 * The single instance of the class.
	 *
	 * @var EventPresso_Addon
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Holds all modules
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Main Addon Instance.
	 *
	 * Ensures only one instance of the EventPresso addon is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return EventPresso_Addon
	 */
	public static function instance() {
		if ( is_null( static::$_instance ) ) {
			static::$_instance = new static();
		}
		return static::$_instance;
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
	 * EventPresso Addon Constructor.
	 */
	public function __construct() {
		if ( $this->dependencies_met() ) {
			$this->define_constants();
			$this->includes();
			$this->init_modules();
			$this->init_hooks();

			do_action( $this->name .'/loaded' );
		} else {
			add_action( 'admin_notices', function() { ?>
				<div class="notice notice-error">
					<p><?php echo sprintf( __( 'Your install does not match all requirements for %s to work properly.', 'eventpresso' ), $this->name ); ?></p>
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
	}

	/**
	 * Get the path to the plugin
	 * @return string
	 */
	public function get_dir() {
		return plugin_dir_path( $this->get_file() );
	}

	/**
	 * Get the url to the plugin
	 * @return string
	 */
	public function get_url() {
		return plugin_dir_url( $this->get_file() );
	}

	/**
	 * Get the file for the addon
	 * @return string
	 */
	public function get_file() {
		return $this->file;
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