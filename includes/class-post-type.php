<?php

use PostTypes\PostType;

class EventPresso_Post_Type {

	protected $post_types;

	/**
	 * Hook into actions and filters.
	 *
	 * @since  1.0
	 */
	public function __construct() {
		add_action('plugins_loaded', function() {
			$this->create_events_post_type();
			$this->create_events_post_type_columns();
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
		$this->post_types['events']->columns()->add(array(
			'title' => __( 'Name of the event', 'eventpresso' ),
			'actions' => __( 'Actions', 'eventpresso' )
		));

		$this->post_types['events']->columns()->populate('actions', function($column, $event_id) {
			do_action('eventpresso/post_type/events/columns/actions', $column, $event_id);
		});

		$this->post_types['events'] = apply_filters( 'eventpresso/post_type/events', $this->post_types['events'] );

	}

	public function create_events_metabox() {

		// create metabox
		$metabox = new EventPresso_Metabox(
			'info',
			__('Event', 'eventpresso'),
			'eventpresso'
		);

		do_action('eventpresso/metabox/before', $metabox, $this);

		// date and time tab
		$metabox->add_tab( __( 'Date & Time', 'eventpresso' ), 'dashicons-hammer' );
		$metabox->add_field(
			'date',
			__( 'Date', 'eventpresso' ),
			__( 'The date for the event', 'eventpresso' ),
			'date'
		);
		do_action('eventpresso/metabox/tab/datetime', $metabox, $this);

		// Location tab
		$metabox->add_tab( __( 'Location', 'eventpresso' ), 'dashicons-location' );
		$metabox->add_field(
			'venue',
			__( 'Venue', 'eventpresso' ),
			__( 'The name of the venue', 'eventpresso' ),
			'text'
		);
		$metabox->add_field(
			'country',
			__( 'Country', 'eventpresso' ),
			__( 'The name of the country', 'eventpresso' ),
			'text'
		);
		do_action('eventpresso/metabox/tab/location', $metabox, $this);

		do_action('eventpresso/metabox/after', $metabox, $this);
	}

}