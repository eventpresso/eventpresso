<?php
/**
 * Event Functions
 *
 * Functions that lets the developer interact with events.
 *
 * @author 		Tor Morten Jensen
 * @category 	Core
 * @package 	EventPresso/Events
 * @version     0.0.1
 */

function eventpresso_get_event( $event ) {

	$post = get_post( $event );

	return $post;

}