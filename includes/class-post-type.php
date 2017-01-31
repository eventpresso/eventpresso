<?php

use PostTypes\PostType;

class UberPress_Events_Post_Type {

	protected $post_types;

	/**
	 * Hook into actions and filters.
	 *
	 * @since  1.0
	 */
	public function __construct() {
		$this->create_events_post_type();
	}

	/**
	 * Automagically resolve post types.
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
	 * Create the post for events
	 * @return void
	 */
	public function create_events_post_type() {
		$this->post_types['events'] = new PostType( 'uberpress-events', array(
			// wp-admin
			'supports'  => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon' => 'dashicons-calendar',
			'labels'    => array(
				'name'               => _x( 'Events', 'post type general name', 'uberpress-events' ),
				'singular_name'      => _x( 'Event', 'post type singular name', 'uberpress-events' ),
				'menu_name'          => _x( 'Events', 'admin menu', 'uberpress-events' ),
				'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'uberpress-events' ),
				'add_new'            => _x( 'Create Event', 'book', 'uberpress-events' ),
				'add_new_item'       => __( 'Add New Event', 'uberpress-events' ),
				'new_item'           => __( 'New Event', 'uberpress-events' ),
				'edit_item'          => __( 'Edit Event', 'uberpress-events' ),
				'view_item'          => __( 'View Event', 'uberpress-events' ),
				'all_items'          => __( 'All Events', 'uberpress-events' ),
				'search_items'       => __( 'Search Events', 'uberpress-events' ),
				'parent_item_colon'  => __( 'Parent Events:', 'uberpress-events' ),
				'not_found'          => __( 'No books found.', 'uberpress-events' ),
				'not_found_in_trash' => __( 'No books found in Trash.', 'uberpress-events' )
			),

			// REST API
			'show_in_rest'          => true,
			'rest_base'             => 'events',
			'rest_controller_class' => 'UberPress_Events_API_Events',
		) );
	}

}