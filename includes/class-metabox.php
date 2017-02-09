<?php
/**
 *

$metabox = new UberPress_Events_Metabox(
	'info',
	__('Event', 'uberpress-events'),
	'uberpress-events'
);
$metabox->add_field( 'date', __('Date', 'uberpress-events'), 'text' );

 *
 */
class UberPress_Events_Metabox {

	/**
	 * The ID of the metabox
	 * @var string
	 */
	protected $id;

	/**
	 * The ID of the metabox
	 * @var string
	 */
	protected $title;

	/**
	 * The context of the metabox
	 * @var string
	 */
	protected $context;

	/**
	 * The priority of the metabox
	 * @var string
	 */
	protected $priority;

	/**
	 * The post types for the metabox
	 * @var array
	 */
	protected $post_types = array();

	/**
	 * Holds all fields for the metabox
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Sets up the metabox
	 * @param string $id         Unique name of the metabox
	 * @param string $title      Metabox title
	 * @param mixed  $post_types One or multiple post types
	 * @param string $context    Metabox context
	 * @param string $priority   Metabox priority
	 */
	public function __construct( $id, $title, $post_types, $context = 'advanced', $priority = 'default' ) {
		if(!is_array($post_types)) {
			$post_types = array($post_types);
		}

		$this->id = $id;
		$this->title = $title;
		$this->post_types = $post_types;
		$this->context = $context;
		$this->priority = $priority;

		$this->init_hooks();
	}

	/**
	 * Sets up hooks
	 * @return void
	 */
	protected function init_hooks() {

		// add metabox and ready saving
		add_action('add_meta_boxes', array($this, 'add_meta_box'));
		add_action('save_post', array($this, 'save_meta_box'));

		// render metaboxes
		add_action('uberpress_events/metabox/text/render', array($this, 'render_text_field'), 1, 2);

		// save metaboxes
		add_action('uberpress_events/metabox/text/save', array($this, 'save_text_field'), 1, 3);

	}

	/**
	 * Add the meta box
	 */
	public function add_meta_box() {
		add_meta_box( $this->id, $this->title, array($this, 'render'), $this->post_types, $this->context, $this->priority );
	}

	/**
	 * Save meta box data
	 * @return void
	 */
	public function save_meta_box() {

		if(isset($_POST['uberpress_events_metabox_'. $this->id]) && wp_verify_nonce( $_POST['uberpress_events_metabox_'. $this->id], "uberpress_events/metabox/{$this->id}/save" )) {
			foreach($this->fields as $field) {
				do_action("uberpress_events/metabox/{$field['type']}/save", $_POST['uberpress_events_metabox'], $field, $this);
			}
		}

	}

	/**
	 * Renders the contents of the metabox
	 * @return void
	 */
	public function render() {
		wp_nonce_field( "uberpress_events/metabox/{$this->id}/save", "uberpress_events_metabox_{$this->id}" );
		?>
		<div class="uberpress-events-metabox-container">
			<?php foreach($this->fields as $field) : ?>
			<div class="uberpress-events-field-container">
				<div class="uberpress-events-field-label">
					<label for="metabox-<?php echo $this->id; ?>-field-<?php echo $field['name']; ?>"><?php echo $field['label']; ?></label>
				</div>
				<div class="uberpress-events-field-content">
					<?php do_action( "uberpress_events/metabox/{$field['type']}/render", $field, 'metabox-'.$this->id.'-field-'.$field['name'], $this ); ?>
				</div>
			</div>
			<?php endforeach ?>
		</div>
		<?php
	}

	/**
	 * Gets the classes for a field
	 * @param  string $field
	 * @return string
	 */
	public function get_field_classes($field) {
		return trim( apply_filters( 'uberpress_events/metabox/field_class', 'uberpress-events-field-control uberpress-events-field-'. $field['type'] . ' ' . $field['classes'], $field, $this) );
	}

	/**
	 * Gets the classes for a field
	 * @param  string $field
	 * @return string
	 */
	public function get_field_name($field) {
		return apply_filters( 'uberpress_events/metabox/field_class', 'uberpress_events_metabox['.$this->id.']['. $field['name'] . ']', $field, $this);
	}

	/**
	 * Adds a field to the metabox
	 * @param string $name
	 * @param string $label
	 * @param string $type
	 * @param string $default
	 * @param array  $args
	 */
	public function add_field($name, $label, $type = 'input', $default = '', $args = array()) {
		$type = sanitize_key( strtolower( $type ) );
		$name = sanitize_key( $name );
		$args = wp_parse_args( $args, array(
			'classes' => ''
		) );
		$params = compact('name', 'label', 'type', 'default');
		$field = wp_parse_args( $params, $args );

		$field = apply_filters( "uberpress_events/metabox/{$type}/field_data", $field, $this );
		$this->fields[$name] = $field;

		return $this;
	}

	/**
	 * Gets the saved value for a field
	 * @param  string $name
	 * @return mixed
	 */
	public function get_field_data($name) {
		if(isset($this->fields[$name])) {
			return $this->fields[$name];
		}
		return null;
	}

	/**
	 * Gets the saved value for a field
	 * @param  string $name
	 * @return mixed
	 */
	public function get_field_value($name, $post = null) {
		if(is_array($name)) {
			$field = $name;
			$name = $name['name'];
		} else {
			$field = $this->get_field($name);
		}
		$post = get_post($post);
		$value = get_post_meta($post->ID, $name, true);
		if($field) {
			switch($field['type']) {
				case 'input':
					$value = apply_filters( 'uberpress_events/metabox/input/field_value', $value, $field, $this );
					break;

				default:
					$value = apply_filters( "uberpress_events/metabox/{$field['type']}/field_value", $value, $field, $this );
					break;
			}
			return $value;
		} else {
			return apply_filters( 'uberpress_events/metabox/field_value', $value, $this );
		}
	}

	public function render_text_field($field, $id) {
		?>
		<input class="<?php echo $this->get_field_classes($field); ?>" type="<?php echo $field['type'] ?>" value="<?php echo $this->get_field_value($field); ?>" id="<?php echo $id; ?>" name="<?php echo $this->get_field_name($field); ?>" />
		<?php
	}


}