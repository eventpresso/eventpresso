<?php

class EventPresso_i18n {

	public $textdomain = '';
	public $file = '';
	public $folder = '';

	public function __construct($textdomain = 'eventpresso', $file = false, $folder = '/lang/') {
		$this->textdomain = $textdomain;
		$this->file = $file ? $file : EVENTPRESSO_PLUGIN_FILE;
		$this->folder = $folder;
		add_action('plugins_loaded', array($this, 'load_textdomain'));
	}

	public function load_textdomain() {
		load_plugin_textdomain( $this->textdomain, FALSE, $this->get_language_path() );
	}

	public function get_language_path() {
		return apply_filters( 'eventpresso/i81n/'.$this->textdomain.'/path', basename( dirname( $this->file ) ) . $this->folder );
	}

}

new EventPresso_i18n();