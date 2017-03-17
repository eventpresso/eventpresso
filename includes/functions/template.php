<?php
/**
 * Template Functions
 *
 * Functions that manages the templates within the plugin.
 *
 * Taken from WooCommerce. All credit goes to them.
 *
 * @author 		WooThemes
 * @category 	Core
 * @package 	EventPresso/Templates
 * @version     0.0.1
 */

/**
 * Get template part (for templates like the shop-loop).
 *
 * EVENTPRESSO_TEMPLATE_DEBUG_MODE will prevent overrides in themes from taking priority.
 *
 * @access public
 * @param mixed $slug
 * @param string $name (default: '')
 */
function eventpresso_get_template_part( $slug, $name = '' ) {
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/eventpresso/slug-name.php
	if ( $name && ! EVENTPRESSO_TEMPLATE_DEBUG_MODE ) {
		$template = locate_template( array( "{$slug}-{$name}.php", EventPresso()->get_template_dir() . "{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( EventPresso()->get_dir() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = EventPresso()->get_dir() . "/templates/{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/eventpresso/slug.php
	if ( ! $template && ! EVENTPRESSO_TEMPLATE_DEBUG_MODE ) {
		$template = locate_template( array( "{$slug}.php", EventPresso()->get_template_dir() . "{$slug}.php" ) );
	}

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'eventpresso_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @access public
 * @param string $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function eventpresso_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = eventpresso_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'eventpresso_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'eventpresso_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'eventpresso_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Like eventpresso_get_template, but returns the HTML instead of outputting.
 * @see eventpresso_get_template
 * @since 2.5.0
 * @param string $template_name
 */
function eventpresso_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	eventpresso_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @access public
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function eventpresso_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = EventPresso()->get_template_dir();
	}

	if ( ! $default_path ) {
		$default_path = EventPresso()->get_dir() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template/
	if ( ! $template || EVENTPRESSO_TEMPLATE_DEBUG_MODE ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'eventpresso_locate_template', $template, $template_name, $template_path );
}