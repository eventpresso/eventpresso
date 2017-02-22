<?php
/**
 * EventPresso Template
 *
 * Loads up the templates needed automatically.
 *
 * @author 		Tor Morten Jensen
 * @category 	Class
 * @package 	EventPresso/Templates
 * @version     0.0.1
 */

class EventPresso_Template {

	/**
	 * Hook in methods.
	 */
	public function __construct() {
		add_filter( 'template_include', array( $this, 'template_loader' ) );
	}

	/**
	 * Loads a template
	 *
	 * Looks first for the eventpresso.php file in root of the theme.
	 * Then the tempalte name in the root of the theme.
	 * Then the template name in the eventpresso folder in the theme.
	 * Then the template folder of the plugin.
	 *
	 * @param mixed $template
	 * @return string
	 */
	public function template_loader( $template ) {
		$find = array( 'eventpresso.php' );
		$file = '';

		if ( is_embed() ) {
			return $template;
		}

		if ( is_single() && get_post_type() == 'eventpresso' ) {

			$file 	= 'single-event.php';
			$find[] = $file;
			$find[] = EventPresso()->get_template_dir() . $file;

		} elseif ( is_event_taxonomy() ) {

			$term   = get_queried_object();

			if ( is_tax( 'eventpresso_cat' ) || is_tax( 'eventpresso_tag' ) ) {
				$file = 'taxonomy-' . $term->taxonomy . '.php';
			} else {
				$file = 'archive-event.php';
			}

			$find[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] = EventPresso()->get_template_dir() . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] = 'taxonomy-' . $term->taxonomy . '.php';
			$find[] = EventPresso()->get_template_dir() . 'taxonomy-' . $term->taxonomy . '.php';
			$find[] = $file;
			$find[] = EventPresso()->get_template_dir() . $file;

		} elseif ( is_post_type_archive( 'eventpresso' ) || is_page( eventpresso_get_page_id( 'events' ) ) ) {

			$file 	= 'archive-event.php';
			$find[] = $file;
			$find[] = EventPresso()->get_template_dir() . $file;

		}

		if ( $file ) {
			$template       = locate_template( array_unique( $find ) );
			if ( ! $template || WC_TEMPLATE_DEBUG_MODE ) {
				$template = EventPresso()->get_dir() . '/templates/' . $file;
			}
		}

		return $template;
	}

}