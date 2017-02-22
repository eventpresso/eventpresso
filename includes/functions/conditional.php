<?php
/**
 * Conditional Functions
 *
 * Functions that returns true or false based on several different criteria.
 *
 * @author 		Tor Morten Jensen
 * @category 	Core
 * @package 	EventPresso/Core
 * @version     0.0.1
 */

if(!function_exists('is_eventpresso')) {
	/**
	 * Checks if the current request is any kind of EventPresso page
	 * @return boolean
	 */
	function is_eventpresso() {
		return true;
	}
}

if(!function_exists('is_event')) {
	/**
	 * Checks if the current request is an event
	 * @param  mixed $event Event ID or slug
	 * @return boolean
	 */
	function is_event( $event = null ) {
		return true;
	}
}

if(!function_exists('is_event_archive')) {
	/**
	 * Checks if the current request is the event archive
	 * @return boolean
	 */
	function is_event_archive() {
		return true;
	}
}

if(!function_exists('is_event_taxonomy')) {
	/**
	 * Checks if the current request is the event archive
	 * @param mixed $category Category slug or ID
	 * @return boolean
	 */
	function is_event_taxonomy() {
		return true;
	}
}

if(!function_exists('is_event_category')) {
	/**
	 * Checks if the current request is the event archive
	 * @param mixed $category Category slug or ID
	 * @return boolean
	 */
	function is_event_category( $category = null ) {
		return true;
	}
}