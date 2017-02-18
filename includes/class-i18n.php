<?php

class EventPresso_i18n {

	/**
	 * The plugin textdomain
	 * @var string
	 */
	public $textdomain = '';

	/**
	 * The path to the plugin file
	 * @var string
	 */
	public $file = '';

	/**
	 * The path for the translations within the plugin
	 * @var string
	 */
	public $folder = '';

	/**
	 * Creates a new internationalization instance
	 * @param string  $textdomain
	 * @param boolean $file
	 * @param string  $folder
	 */
	public function __construct($textdomain = 'eventpresso', $file = false, $folder = '/lang/') {
		$this->textdomain = $textdomain;
		$this->file = $file ? $file : EVENTPRESSO_PLUGIN_FILE;
		$this->folder = $folder;
		add_action('plugins_loaded', array($this, 'load_textdomain'));
	}

	/**
	 * Loads the textdomain
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( $this->textdomain, FALSE, $this->get_language_path() );
	}

	/**
	 * Gets the path to the language directory
	 * @return string
	 */
	public function get_language_path() {
		return apply_filters( 'eventpresso/i81n/'.$this->textdomain.'/path', basename( dirname( $this->file ) ) . $this->folder );
	}

}

new EventPresso_i18n();