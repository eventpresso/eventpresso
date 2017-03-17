<?php
/**
 * EventPresso Post Types
 *
 * A class that defines all post types and metaboxes used in EventPresso
 *
 * @author 		Tor Morten Jensen
 * @category 	Class
 * @package 	EventPresso/Admin
 * @version     0.0.1
 */

use PostTypes\PostType;

class EventPresso_Post_Type {

	/**
	 * Holds all registered post types
	 * @var array
	 */
	protected $post_types;

	/**
	 * Holds the metabox instance
	 * @var EventPresso_Metabox
	 */
	protected $events_metabox;

	/**
	 * Holds the permalinks
	 * @var array
	 */
	protected $permalinks;

	/**
	 * Hook into actions and filters.
	 *
	 * @since  1.0
	 */
	public function __construct() {
		add_action('plugins_loaded', function() {
			$this->permalinks = get_option( 'eventpresso_permalinks', array() );
			$this->create_events_post_type();
			$this->create_events_post_type_columns();
			$this->create_events_taxonomies();
			$this->create_events_metabox();
		});
	}

	/**
	 * Automagically resolve post types.
	 *
	 * @param mixed   $key
	 * @return mixed
	 */
	public function __get( $key ) {
		if(isset($this->post_types[$key])) {
			return $this->post_types[$key];
		}
	}

	/**
	 * Create the post for events
	 * @return void
	 */
	public function create_events_post_type() {
		$this->post_types['events'] = new PostType( 'eventpresso', array(
			// wp-admin
			'supports'  => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon' => 'dashicons-calendar',
			'labels'    => array(
				'name'               => _x( 'Events', 'post type general name', 'eventpresso' ),
				'singular_name'      => _x( 'Event', 'post type singular name', 'eventpresso' ),
				'menu_name'          => _x( 'Events', 'admin menu', 'eventpresso' ),
				'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'eventpresso' ),
				'add_new'            => _x( 'Create Event', 'book', 'eventpresso' ),
				'add_new_item'       => __( 'Add New Event', 'eventpresso' ),
				'new_item'           => __( 'New Event', 'eventpresso' ),
				'edit_item'          => __( 'Edit Event', 'eventpresso' ),
				'view_item'          => __( 'View Event', 'eventpresso' ),
				'all_items'          => __( 'All Events', 'eventpresso' ),
				'search_items'       => __( 'Search Events', 'eventpresso' ),
				'parent_item_colon'  => __( 'Parent Events:', 'eventpresso' ),
				'not_found'          => __( 'No books found.', 'eventpresso' ),
				'not_found_in_trash' => __( 'No books found in Trash.', 'eventpresso' )
			),

			// REST API
			'show_in_rest'          => true,
			'rest_base'             => 'events',
			'rest_controller_class' => 'EventPresso_API_Events',
		) );
	}

	public function create_events_post_type_columns() {

		// Define columns
		$this->events->columns()->add(array(
			'title' => __( 'Name of the event', 'eventpresso' ),
			'event_date' => __('Event Date', 'eventpresso'),
			'actions' => __( 'Actions', 'eventpresso' )
		));

		// Hide default columns
		$this->events->columns()->hide(['author', 'date']);

		// Populate event date column
		$this->events->columns()->populate('event_date', function($column, $post_id) {
			echo $this->events_metabox->get_field_value('event_date', $post_id);
		});

		// Populate actions column
		$this->events->columns()->populate('actions', function($column, $event_id) {
			do_action('eventpresso/post_type/events/columns/actions', $column, $event_id);
		});

		// Sortable columns
		$this->events->columns()->sortable(array(
			'event_date' => ['event_date', false]
		));

		// Allow filtering the post type
		$this->events = apply_filters( 'eventpresso/post_type/events', $this->post_types['events'] );

	}

	public function create_events_taxonomies() {

		// register the event category taxonomy
		$this->events->taxonomy('eventpresso_cat', apply_filters('eventpresso_category_args', array(
			'label'                 => __( 'Event Categories', 'eventpresso' ),
			'labels' => array(
				'name'					=> _x( 'Event Categories', 'Category plural name', 'eventpresso' ),
				'singular_name'			=> _x( 'Event Category', 'Event Category singular name', 'eventpresso' ),
				'search_items'			=> __( 'Search Event Categories', 'eventpresso' ),
				'popular_items'			=> __( 'Popular Event Categories', 'eventpresso' ),
				'all_items'				=> __( 'All Event Categories', 'eventpresso' ),
				'parent_item'			=> __( 'Parent Event Category', 'eventpresso' ),
				'parent_item_colon'		=> __( 'Parent Event Category', 'eventpresso' ),
				'edit_item'				=> __( 'Edit Event Category', 'eventpresso' ),
				'update_item'			=> __( 'Update Event Category', 'eventpresso' ),
				'add_new_item'			=> __( 'Add New Event Category', 'eventpresso' ),
				'new_item_name'			=> __( 'New Event Category Name', 'eventpresso' ),
				'add_or_remove_items'	=> __( 'Add or remove Event Categories', 'eventpresso' ),
				'choose_from_most_used'	=> __( 'Choose from most used Event Categories', 'eventpresso' ),
				'menu_name'				=> __( 'Categories', 'eventpresso' ),
			),
			'hierarchical' => true,
			'show_ui'               => true,
			'query_var'             => true,
			// 'capabilities'          => array(
			// 	'manage_terms' => 'manage_eventpresso_terms',
			// 	'edit_terms'   => 'edit_eventpresso_terms',
			// 	'delete_terms' => 'delete_eventpresso_terms',
			// 	'assign_terms' => 'assign_eventpresso_terms',
			// ),
			'rewrite'          => array(
				'slug'         => empty( $this->permalinks['category_base'] ) ? _x( 'event-category', 'slug', 'eventpresso' ) : $permalinks['category_base'],
				'with_front'   => false,
				'hierarchical' => true,
			),
		)));

		// register the event tag taxonomy
		$this->events->taxonomy('eventpresso_tag', apply_filters('eventpresso_tag_args', array(
			'label'                 => __( 'Event Tags', 'eventpresso' ),
			'labels' => array(
				'name'					=> _x( 'Event Tags', 'Category plural name', 'eventpresso' ),
				'singular_name'			=> _x( 'Event Tag', 'Event Tag singular name', 'eventpresso' ),
				'search_items'			=> __( 'Search Event Tags', 'eventpresso' ),
				'popular_items'			=> __( 'Popular Event Tags', 'eventpresso' ),
				'all_items'				=> __( 'All Event Tags', 'eventpresso' ),
				'parent_item'			=> __( 'Parent Event Tag', 'eventpresso' ),
				'parent_item_colon'		=> __( 'Parent Event Tag', 'eventpresso' ),
				'edit_item'				=> __( 'Edit Event Tag', 'eventpresso' ),
				'update_item'			=> __( 'Update Event Tag', 'eventpresso' ),
				'add_new_item'			=> __( 'Add New Event Tag', 'eventpresso' ),
				'new_item_name'			=> __( 'New Event Tag Name', 'eventpresso' ),
				'add_or_remove_items'	=> __( 'Add or remove Event Tags', 'eventpresso' ),
				'choose_from_most_used'	=> __( 'Choose from most used Event Tags', 'eventpresso' ),
				'menu_name'				=> __( 'Tags', 'eventpresso' ),
			),
			'hierarchical' => false,
			'show_ui'               => true,
			'query_var'             => true,
			// 'capabilities'          => array(
			// 	'manage_terms' => 'manage_eventpresso_terms',
			// 	'edit_terms'   => 'edit_eventpresso_terms',
			// 	'delete_terms' => 'delete_eventpresso_terms',
			// 	'assign_terms' => 'assign_eventpresso_terms',
			// ),
			'rewrite'               => array(
				'slug'       => empty( $permalinks['tag_base'] ) ? _x( 'event-tag', 'slug', 'eventpresso' ) : $permalinks['tag_base'],
				'with_front' => false
			),
		)));
	}

	public function create_events_metabox() {

		// create metabox
		$this->events_metabox = new EventPresso_Metabox(
			'info',
			__('Event', 'eventpresso'),
			'eventpresso'
		);

		// Hook into this to add stuff before EventPresso has added its fields
		do_action('eventpresso/metabox/before', $this->events_metabox, $this);

		// date and time tab
		$this->events_metabox->add_tab( __( 'Date & Time', 'eventpresso' ), 'dashicons-hammer' );

		// add a field for date
		$this->events_metabox->add_field(
			'event_date',
			__( 'Date', 'eventpresso' ),
			__( 'The date for the event', 'eventpresso' ),
			'date'
		);

		// Hook into this to add stuff to the date/time tab
		do_action('eventpresso/metabox/tab/datetime', $this->events_metabox, $this);

		// Location tab
		$this->events_metabox->add_tab( __( 'Location', 'eventpresso' ), 'dashicons-location' );

		// add a field for the venue
		$this->events_metabox->add_field(
			'venue',
			__( 'Venue', 'eventpresso' ),
			__( 'The name of the venue', 'eventpresso' ),
			'text'
		);

		// add a field for the country
		$this->events_metabox->add_field(
			'country',
			__( 'Country', 'eventpresso' ),
			__( 'The name of the country', 'eventpresso' ),
			'text'
		);

		// Hook into this to add stuff to the location tab
		do_action('eventpresso/metabox/tab/location', $this->events_metabox, $this);

		// Hook into this to add stuff after EventPresso has added its fields
		do_action('eventpresso/metabox/after', $this->events_metabox, $this);
	}

}