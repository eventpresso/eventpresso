<?php

use PostTypes\PostType;

class Eventpresso_Post_Type {

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
		add_submenu_page( 'edit.php?post_type=eventpresso', __('Invitations', 'eventpresso'), __('Invitations', 'eventpresso'), 'edit_others_posts', 'eventpresso-invitations', array($this, 'render_invitations_page') );
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
			<h2><?php _e('Please select an event', 'eventpresso') ?></h2>
			<select onChange="window.location = this.value">
				<option value=""><?php _e('Select event', 'eventpresso') ?></option>
				<?php foreach(get_posts('post_type=eventpresso&posts_per_page=-1') as $event) : ?>
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
				<h3><?php _e( 'Invited users', 'eventpresso' ) ?></h3>
				<?php $invitations = Eventpresso()->invited->event($event_id)->sort()->offset($start_from, 20)->all(); ?>
				<table class="wp-list-table widefat fixed striped posts">
					<thead>
						<tr>
							<th scope="col" class="manage-column" style="width:65%">
								<?php _e('Name', 'eventpresso') ?>
							</th>
							<th scope="col" class="manage-column">
								<?php _e('Actions', 'eventpresso') ?>
							</th>
						</tr>
					</thead>

					<tbody id="the-list">
						<?php foreach($invitations as $invitation) : $user = get_user_by( 'id', $invitation->user_id ); ?>
						<tr>
							<td><?php echo $user->display_name ?> (<?php echo $user->user_email ?>)</td>
							<td>
								<a href="<?php echo admin_url("edit.php?post_type=eventpresso&page=eventpresso-invitations&event={$invitation->event_id}&action=revoke&invitation={$invitation->id}") ?>" class="button-primary">
									<?php _e('Revoke', 'eventpresso') ?>
								</a>
								<button class="button-secondary reveal-key" data-key="<?php echo $invitation->invite_key ?>">
									<?php _e('Reveal key', 'eventpresso') ?>
								</a>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>

				</table>
			</div>
			<div style="width:40%;float:right">
				<h3><?php _e( 'Invite more', 'eventpresso' ) ?></h3>

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
			'rest_controller_class' => 'Eventpresso_API_Events',
		) );
	}

	public function create_events_post_type_columns() {
		$this->post_types['events']->columns()->add(array(
			'title' => __( 'Name of the event', 'eventpresso' ),
			'invitations' => __( 'Invitations', 'eventpresso' ),
			'actions' => __( 'Actions', 'eventpresso' )
		));

		$this->post_types['events']->columns()->populate('invitations', function($column, $event_id) {
			echo Eventpresso()->invited->event($event_id)->count() . ' are invited<br>';
			echo Eventpresso()->invited->event($event_id)->response('pending')->count() . ' have not replied.<br>';
			echo Eventpresso()->invited->event($event_id)->response('attending')->count() . ' are attending.<br>';
		});

		$this->post_types['events']->columns()->populate('actions', function($column, $event_id) {
			?>
			<a href="<?php echo admin_url("edit.php?post_type=eventpresso&page=eventpresso-invitations&event={$event_id}") ?>" class="button-primary"><?php _e( 'Manage invitations', 'eventpresso' ) ?></a>
			<?php
		});
	}

	public function create_events_metabox() {
		$metabox = new Eventpresso_Metabox(
			'info',
			__('Event', 'eventpresso'),
			'eventpresso'
		);
		$metabox->add_tab( __( 'Basic', 'eventpresso' ) );
		$metabox->add_field(
			'date',
			__( 'Date', 'eventpresso' ),
			__( 'The date for the event', 'eventpresso' ),
			'date'
		);
		$metabox->add_tab( __( 'Location', 'eventpresso' ) );
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
	}

}