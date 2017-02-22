<?php

/**
 * Retrieve a page ID
 * @param  string $page
 * @return integer
 */
function eventpresso_get_page_id( $page ) {

	$page = apply_filters( 'eventpresso_get_' . $page . '_page_id', get_option('eventpresso_' . $page . '_page_id' ) );

	return $page ? absint( $page ) : -1;
}