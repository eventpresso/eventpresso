<?php

class Eventpresso_Invited {

	protected $db;

	protected $table;

	protected $query;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'eventpresso_invited';
		$this->db = $wpdb;
	}

	public function all() {
		return $this->db->get_results( $this->query );
	}

	public function first() {
		$this->query .= ' ORDER BY id DESC LIMIT 1';
		return $this->db->get_row( $this->query );
	}

	public function last() {
		$this->query .= ' ORDER BY id ASC LIMIT 1';
		return $this->db->get_row( $this->query );
	}

	public function get($id) {
		$this->query = $this->db->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id );
		return $this->db->get_row( $this->query );
	}

	public function event($id) {
		$this->query = $this->db->prepare( "SELECT * FROM {$this->table} WHERE event_id = %d", $id );
		return $this;
	}

	public function count() {
		$this->query = str_replace('SELECT * ', 'SELECT COUNT(*) ', $this->query);
		return $this->db->get_var( $this->query );
	}

	public function sort() {
		$this->query .= ' ORDER BY invited_at ASC';
		return $this;
	}

	public function offset($start_from, $limit = 20) {
		$this->query .= " LIMIT {$start_from},{$limit}";
		return $this;
	}

	public function response($response) {
		$this->query .= $this->db->prepare( " AND response = %s", $response );
		return $this;
	}

}