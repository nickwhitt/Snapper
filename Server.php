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
	protected $excludes = array();
	protected $conn;
	
	public function __construct($user, $pass, $host='localhost') {
		$this->conn = new \PDO(sprintf('mysql:host=%s', $host), $user, $pass);
	}
	
	public function __destruct() {
		$this->conn = NULL;
	}
	
	public function scanDatabases() {
		$this->databases = array();
		foreach ($this->conn->query('show databases', \PDO::FETCH_COLUMN, 0) as $database) {
			if (!in_array($database, $this->excludes)) {
				$this->databases[$database] = new Database($database, $this->conn);
			}
		}
	}
	
	public function excludeDatabase($database) {
		if (!in_array($database, $this->excludes)) {
			$this->excludes[] = $database;
		}
	}
	
	public function excludeDatabases(array $databases) {
		foreach ($databases as $database) {
			$this->excludeDatabase($database);
		}
	}
	
	public function listDatabases() {
		return array_keys($this->databases);
	}
	
	public function getDatabase($database) {
		return $this->databases[$database];
	}
	
	public function exportDatabase($database, $conf=NULL, $path=NULL) {
		$this->getDatabase($database)->exportTables($conf, $this->defaultFilepath($database, $path));
	}
	
	public function exportDatabases($conf=NULL, $path=NULL) {
		foreach ($this->listDatabases() as $database) {
			$this->exportDatabase($database, $conf, $path);
		}
	}
	
	protected function defaultFilepath($database, $path=NULL) {
		return sprintf(
			'%s/%s/',
			is_null($path) ? dirname(__FILE__) : $path,
			$database
		);
	}
}