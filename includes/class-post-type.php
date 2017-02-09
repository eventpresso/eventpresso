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
		$this->create_events_post_type_columns();
		$this->create_events_metabox();
		add_action('admin_menu', array($this, 'create_invitations_page'));
	}

	public function create_invitations_page() {
		add_submenu_page( 'edit.php?post_type=uberpress-events', __('Invitations', 'uberpress-events'), __('Invitations', 'uberpress-events'), 'edit_others_posts', 'uberpress-invitations', array($this, 'render_invitations_page') );
	}

	public function render_invitations_page() {
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';
		if(!method_exists($this, 'invitations_page_action_'. strtolower($action))) {
			$action = 'list';
		}
		$method = 'invitations_page_action_'. strtolower($action);
		?>
		<div class="wrap">
			<?php $this->$method(); ?>
		</div>
		<?php
	}

	public function invitations_page_action_list() {
		$event_id = isset($_REQUEST['event']) ? $_REQUEST['event'] : false;
		if(!$event_id) {
			?>
			<h2><?php _e('Please select an event', 'uberpress-events') ?></h2>
			<select onChange="window.location = this.value">
				<option value=""><?php _e('Select event', 'uberpress-events') ?></option>
				<?php foreach(get_posts('post_type=uberpress-events&posts_per_page=-1') as $event) : ?>
					<option value="<?php echo add_query_arg( 'event', $event->ID  ); ?>"><?php echo get_the_title($event) ?></option>
				<?php endforeach; ?>
			</select>
			<?php
		} else {
			$event = get_post($event_id);
			$start_from = isset($_REQUEST['start_from']) ? $_REQUEST['start_from'] : 0;
			?>
			<h2><?php echo sprintf( __( 'Manage invitations for %s' ), get_the_title($event) ); ?></h2>
			<div style="width:55%;float:left">
				<h3><?php _e( 'Invited users', 'uberpress-events' ) ?></h3>
				<?php $invitations = UBE()->invited->event($event_id)->sort()->offset($start_from, 20)->all(); ?>
				<table class="wp-list-table widefat fixed striped posts">
					<thead>
						<tr>
							<th scope="col" class="manage-column" style="width:65%">
								<?php _e('Name', 'uberpress-events') ?>
							</th>
							<th scope="col" class="manage-column">
								<?php _e('Actions', 'uberpress-events') ?>
							</th>
						</tr>
					</thead>

					<tbody id="the-list">
						<?php foreach($invitations as $invitation) : $user = get_user_by( 'id', $invitation->user_id ); ?>
						<tr>
							<td><?php echo $user->display_name ?> (<?php echo $user->user_email ?>)</td>
							<td>
								<a href="<?php echo admin_url("edit.php?post_type=uberpress-events&page=uberpress-invitations&event={$invitation->event_id}&action=revoke&invitation={$invitation->id}") ?>" class="button-primary">
									<?php _e('Revoke', 'uberpress-events') ?>
								</a>
								<button class="button-secondary reveal-key" data-key="<?php echo $invitation->invite_key ?>">
									<?php _e('Reveal key', 'uberpress-events') ?>
								</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>

				</table>
			</div>
			<div style="width:40%;float:right">
				<h3><?php _e( 'Invite more', 'uberpress-events' ) ?></h3>

			</div>
			<?php
		}
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

	public function create_events_post_type_columns() {
		$this->post_types['events']->columns()->add(array(
			'title' => __( 'Name of the event', 'uberpress-events' ),
			'invitations' => __( 'Invitations', 'uberpress-events' ),
			'actions' => __( 'Actions', 'uberpress-events' )
		));

		$this->post_types['events']->columns()->populate('invitations', function($column, $event_id) {
			echo UBE()->invited->event($event_id)->count() . ' are invited<br>';
			echo UBE()->invited->event($event_id)->response('pending')->count() . ' have not replied.<br>';
			echo UBE()->invited->event($event_id)->response('attending')->count() . ' are attending.<br>';
		});

		$this->post_types['events']->columns()->populate('actions', function($column, $event_id) {
			?>
			<a href="<?php echo admin_url("edit.php?post_type=uberpress-events&page=uberpress-invitations&event={$event_id}&action=invite") ?>" class="button-primary"><?php _e( 'Invite more', 'uberpress-events' ) ?></a>
			<a href="<?php echo admin_url("edit.php?post_type=uberpress-events&page=uberpress-invitations&event={$event_id}") ?>" class="button-secondary"><?php _e( 'Invited', 'uberpress-events' ) ?></a>
			<?php
		});
	}

	public function create_events_metabox() {
		$metabox = new VP_Metabox(array(
			'id' => '_uberpress_events',
			'title' => __( 'Event Information', 'uberpress-events' ),
			'types' => array( 'uberpress-events' ),
			'template' => plugin_dir_path( UBE_PLUGIN_FILE ) . '/includes/metabox/events.php'
		));
	}

}