<?php
/**
 *

$metabox = new EventPresso_Metabox(
	'info',
	__('Event', 'eventpresso'),
	'eventpresso'
);
$metabox->add_field( 'date', __('Date', 'eventpresso'), 'text' );

 *
 */
class EventPresso_Metabox {

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

		// add scripts
		add_action('admin_enqueue_scripts', array($this, 'meta_box_scripts'));

		// render metaboxes
		add_action('eventpresso/metabox/text/render', array($this, 'render_text_field'), 1, 3);
		add_action('eventpresso/metabox/date/render', array($this, 'render_date_field'), 1, 3);

		// save metaboxes
		add_action('eventpresso/metabox/text/save', array($this, 'save_text_field'), 1, 3);
		add_action('eventpresso/metabox/date/save', array($this, 'save_date_field'), 1, 3);

		// set custom field settings
		add_filter('eventpresso/metabox/date/field_data', array($this, 'set_date_settings'), 1);

		// format the field value
		add_filter('eventpresso/metabox/date/field_value', array($this, 'get_date_value'), 1, 2);

	}

	/**
	 * Format the date from the database to match the given jQuery format
	 * @param  string $value
	 * @param  array $field
	 * @return value
	 */
	public function get_date_value($value, $field) {
		if($value) {
			$dateFormat = $this->dateformat($field['options']['dateFormat']);
			$date = date_create_from_format('Y-m-d', $value);
			if($date) {
				$value = $date->format($dateFormat);
			}
		}
		return $value;
	}

	/**
	 * Matches each symbol of jquery date format standard
	 * with PHP equivalent codeword
	 *
	 * @param string $date_format
	 * @return string
	 */
	function dateformat($date_format) {
		$replace = array(
			0 => array('dd','d','DD','o'),
			1 => array('MM','M','mm','m'),
			2 => array('yy','y')
		);
		$replacements = array(
			0 => array('d', 'j', 'l', 'z'),
			1 => array('F','M','m','n'),
			2 => array('Y','y')
		);
		$found = array();
		foreach($replace as $type => $format) {
			foreach($format as $index => $r) {
				$position = stripos($date_format, $r);
				if($position !== FALSE) {
					$date_format = str_replace($r, "[{$type}_{$index}]", $date_format);
				}
			}
		}
		$pattern = '/\[[^\]]*\]/';
		preg_match_all($pattern, $date_format, $matches);
		foreach($matches[0] as $match) {
			$find = substr($match, 1, strlen($match));
			$find = substr($find, 0, -1);
			$fragments = explode('_', $find);
			$fragments = array_map(function($i){
				return (int)$i;
			}, $fragments);
			$matched = $replacements[$fragments[0]][$fragments[1]];
			$date_format = str_replace($match, $matched, $date_format);
		}
		return $date_format;
	}

	/**
	 * Set default date options
	 * @param array $settings
	 * @return array
	 */
	public function set_date_settings($settings) {
		return wp_parse_args( $settings, array(
			'options' => array(
				'dateFormat' => 'yy-mm-dd'
			)
		) );
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
	public function save_meta_box($post_id) {
		if(isset($_POST['eventpresso_metabox_'. $this->id]) && wp_verify_nonce( $_POST['eventpresso_metabox_'. $this->id], "eventpresso/metabox/{$this->id}/save" )) {
			foreach($this->get_fields() as $id => $field) {
				do_action("eventpresso/metabox/{$field['type']}/save", $_POST['eventpresso_metabox'][$this->id], $field, $post_id, $this);
			}
		}
	}

	/**
	 * Saves text field
	 * @return void
	 */
	public function save_text_field($data, $field, $post_id) {
		$value = isset($data[$field['name']]) ? $data[$field['name']] : '';
		$value = apply_filters( "eventpresso/metabox/{$field['type']}/save/sanitize", sanitize_text_field( $value ) );
		$this->save_meta($field, $value, $post_id);
	}

	/**
	 * Saves date field
	 * @return void
	 */
	public function save_date_field($data, $field, $post_id) {
		$value = isset($data[$field['name']]) ? $data[$field['name']] : '';
		if($value) {
			$dateFormat = $this->dateformat($field['options']['dateFormat']);
			$date = date_create_from_format($dateFormat, $value);
			if($date) {
				$value = $date->format('Y-m-d');
			}
		}
		$value = apply_filters( "eventpresso/metabox/{$field['type']}/save/sanitize", sanitize_text_field( $value ) );
		$this->save_meta($field, $value, $post_id);
	}

	/**
	 * Saves meta data to a post
	 * @param  array $field
	 * @param  string $value
	 * @param  integer $post_id
	 * @return void
	 */
	public function save_meta( $field, $value, $post_id ) {
		update_post_meta( $post_id, $field['name'], $value );
	}

	/**
	 * Enqueue the admin metabox script
	 * @param  string $hook
	 * @return void
	 */
	public function meta_box_scripts($hook) {
		if( ( $hook === 'post-new.php' || $hook === 'post.php' ) && in_array( get_post_type(), $this->post_types ) ) {
			// enqueue the javascript
			wp_enqueue_script(
				'eventpresso/metabox/scripts/admin',
				plugins_url( '/assets/js/admin/metabox.js', EVENTPRESSO_PLUGIN_FILE ),
				array('jquery', 'underscore', 'jquery-ui-datepicker'),
				EVENTPRESSO_VERSION
			);

			// enqueue jquery ui from cdn
			wp_enqueue_style(
				'eventpresso/metabox/styles/jquery-ui',
				'//code.jquery.com/ui/1.12.0/themes/smoothness/jquery-ui.css',
				array(),
				'1.12.0'
			);

			// enqueue the stylesheet
			wp_enqueue_style(
				'eventpresso/metabox/styles/admin',
				plugins_url( '/assets/css/admin/metabox.css', EVENTPRESSO_PLUGIN_FILE ),
				array( 'eventpresso/metabox/styles/jquery-ui' ),
				EVENTPRESSO_VERSION
			);
		}
	}

	/**
	 * Adds a tab field to fields when applicable
	 * @return void
	 */
	protected function filter_fields() {
		if($this->fields) {
			$current_tab = '';
			foreach($this->fields as $index => &$field) {
				if($field['type'] === 'tab') {
					$current_tab = $index;
				} else {
					if($current_tab !== '') {
						$field['tab'] = md5('tab-'. $current_tab);
					}
				}
			}
		}
	}

	/**
	 * Gets all tabs for the metabox
	 * @return array
	 */
	public function get_tabs() {
		$tabs = array();
		foreach($this->fields as $index => $field) {
			if($field['type'] === 'tab') {
				$id = md5('tab-'. $index);
				$tabs[$id] = $field;
			}
		}
		return apply_filters( "eventpresso/metabox/{$this->id}/tabs", $tabs, $this );
	}

	/**
	 * Gets all fields for the metabox
	 * @return array
	 */
	public function get_fields() {
		$fields = array();
		foreach($this->fields as $index => $field) {
			if($field['type'] !== 'tab') {
				$id = md5('field-'. $index);
				$fields[$id] = $field;
			}
		}
		return apply_filters( "eventpresso/metabox/{$this->id}/fields", $fields, $this );
	}

	/**
	 * Renders the contents of the metabox
	 * @return void
	 */
	public function render() {
		wp_nonce_field( "eventpresso/metabox/{$this->id}/save", "eventpresso_metabox_{$this->id}" );

		do_action( "eventpresso/metabox/before", $this );
		$this->filter_fields();
		$tabs = $this->get_tabs();
		?>
		<div class="eventpresso-metabox-container <?php echo $tabs ? 'eventpresso-metabox-has-tabs' : '' ?>">
			<?php if($tabs) :  ?>
			<ul class="eventpresso-tabs-container">
				<?php foreach($tabs as $id => $tab) : ?>
				<li class="eventpresso-tab-container" data-tab="<?php echo $id ?>">
					<a href="#">
						<?php if($tab['icon']) : ?>
						<span class="dashicons <?php echo $tab['icon'] ?>"></span>
						<?php endif; ?>
						<?php echo $tab['label'] ?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
			<div class="eventpresso-fields-container">
				<?php foreach($this->get_fields() as $name => $field) : ?>
				<?php
					do_action( "eventpresso/metabox/{$field['type']}/render/before", $field, 'metabox-'.$this->id.'-field-'.$field['name'], $this );
				?>
				<div class="eventpresso-field-container eventpresso-field-id-<?php echo $name ?> eventpresso-field-container-<?php echo $field['type'] ?>" <?php echo isset($field['tab']) ? 'data-tab="'.$field['tab'].'"' : '' ?>">
					<div class="eventpresso-field-label">
						<label for="metabox-<?php echo $this->id; ?>-field-<?php echo $field['name']; ?>"><?php echo $field['label']; ?></label>
						<?php if( $field['description'] ) : ?>
						<p class="description"><?php echo $field['description'] ?></p>
						<?php endif; ?>
					</div>
					<div class="eventpresso-field-content">
						<?php
							do_action( "eventpresso/metabox/{$field['type']}/render", $field, 'metabox-'.$this->id.'-field-'.$field['name'], $this );
						?>
					</div>
				</div>
				<?php
					do_action( "eventpresso/metabox/{$field['type']}/render/after", $field, 'metabox-'.$this->id.'-field-'.$field['name'], $this );
				?>
				<?php endforeach ?>
			</div>
		</div>
		<?php

		do_action( "eventpresso/metabox/after", $this );
	}

	/**
	 * Gets the classes for a field
	 * @param  string $field
	 * @return string
	 */
	public function get_field_classes($field) {
		return trim( apply_filters( 'eventpresso/metabox/field_class', 'eventpresso-field-control eventpresso-field-'. $field['type'] . ' ' . $field['classes'], $field, $this) );
	}

	/**
	 * Gets the classes for a field
	 * @param  string $field
	 * @return string
	 */
	public function get_field_name($field) {
		return apply_filters( 'eventpresso/metabox/field_class', 'eventpresso_metabox['.$this->id.']['. $field['name'] . ']', $field, $this);
	}

	/**
	 * Adds a field to the metabox
	 * @param string $name
	 * @param string $label
	 * @param string $type
	 * @param string $default
	 * @param array  $args
	 */
	public function add_field($name, $label, $description = '', $type = 'input', $default = '', $args = array()) {
		$type = sanitize_key( strtolower( $type ) );
		$name = sanitize_key( $name );
		$args = wp_parse_args( $args, array(
			'classes' => ''
		) );
		$params = compact('name', 'label', 'description', 'type', 'default');
		$field = wp_parse_args( $params, $args );

		$field = apply_filters( "eventpresso/metabox/{$type}/field_data", $field, $this );
		$this->fields[] = $field;

		return $this;
	}

	/**
	 * Add a tab
	 * @param string $title
	 * @param string $description
	 */
	public function add_tab($title, $icon = null) {
		return $this->add_field('', $title, '', 'tab', '', array(
			'icon' => $icon
		));
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
			$value = apply_filters( "eventpresso/metabox/{$field['type']}/field_value", $value, $field, $this );
			return $value;
		} else {
			return apply_filters( 'eventpresso/metabox/field_value', $value, $this );
		}
	}

	/**
	 * Renders the text field
	 * @param  array $field
	 * @param  string $id
	 * @return void
	 */
	public function render_text_field($field, $id) {
		$attributes = array(
			'class' => $this->get_field_classes($field),
			'type'  => 'text',
			'value' => $this->get_field_value($field),
			'id'    => $id,
			'name'  => $this->get_field_name($field)
		);
		echo '<input '.join( ' ', array_map( function( $key ) use ( $attributes ) {
			if(is_bool($attributes[$key])){
				return $attributes[$key] ? $key : '';
			}
			return $key."='".$attributes[$key]."'";
		}, array_keys( $attributes ) ) ).' />';
	}

	/**
	 * Renders the date field
	 * @param  array $field
	 * @param  string $id
	 * @return void
	 */
	public function render_date_field($field, $id) {
		$attributes = array(
			'class' => $this->get_field_classes($field),
			'type'  => 'text',
			'value' => $this->get_field_value($field),
			'id'    => $id,
			'name'  => $this->get_field_name($field),
			'data-options' => json_encode($field['options'])
		);
		echo '<input '.join( ' ', array_map( function( $key ) use ( $attributes ) {
			if(is_bool($attributes[$key])){
				return $attributes[$key] ? $key : '';
			}
			return $key."='".$attributes[$key]."'";
		}, array_keys( $attributes ) ) ).' />';
	}


}