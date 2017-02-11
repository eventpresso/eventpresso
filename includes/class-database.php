<?php

class Eventpresso_Database {

	public static function setup_database() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		self::setup_invited_table();
	}

	public static function setup_invited_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'eventpresso_invited';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id mediumint(9) NOT NULL,
			event_id mediumint(9) NOT NULL,
			response varchar(255) DEFAULT 'pending',
			invite_key varchar(255),
			invited_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta( $sql );
	}

}