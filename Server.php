<?php
/**
 * MySQL Snapshot Generation Tool
 * Server Encapsulation
 *
 * @author Nick Whitt
 */

namespace Snapper;
class Server {
	protected $databases = array();
	protected $conn;
	
	public function __construct($user, $pass, $host='localhost') {
		$this->conn = new \PDO(sprintf('mysql:host=%s', $host), $user, $pass);
		
		$this->databases = array();
		foreach ($this->conn->query('show databases', \PDO::FETCH_COLUMN, 0) as $database) {
			$this->databases[$database] = new Database($database, $this->conn);
		}
	}
	
	public function __destruct() {
		$this->conn = NULL;
	}
	
	public function getDatabases() {
		return array_keys($this->databases);
	}
	
	public function getDatabase($database) {
		return $this->databases[$database];
	}
}