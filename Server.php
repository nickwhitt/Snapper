<?php
/**
 * MySQL Snapshot Generation Tool
 *
 * Provides database scanning and export methods on a MySQL server.
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
	
	/**
	 * Internally stores a Database object for each database on the server
	 *
	 * Only non-excluded databases will be stored.
	 *
	 * @param void
	 * @return void
	 */
	public function scanDatabases() {
		$this->databases = array();
		foreach ($this->conn->query('show databases', \PDO::FETCH_COLUMN, 0) as $database) {
			if (!$this->isExcluded($database)) {
				$this->databases[$database] = new Database($database, $this->conn);
			}
		}
	}
	
	/**
	 * Prevents the database from being scanned or exported
	 *
	 * @param str $database
	 * @return void
	 */
	public function excludeDatabase($database) {
		if (!$this->isExcluded($database)) {
			$this->excludes[] = $database;
		}
	}
	
	/**
	 * Prevents all the given databases from being scanned or exported
	 *
	 * @param array $databases
	 * @return void
	 */
	public function excludeDatabases(array $databases) {
		foreach ($databases as $database) {
			$this->excludeDatabase($database);
		}
	}
	
	/**
	 * Tests if the given database is excluded from being scanned or exported
	 *
	 * @param str $database
	 * @return bool
	 */
	public function isExcluded($database) {
		return in_array($database, $this->excludes);
	}
	
	/**
	 * Lists all scanned databases
	 *
	 * @param void
	 * @return array
	 */
	public function listDatabases() {
		return array_keys($this->databases);
	}
	
	/**
	 * Retrieves the internal Database object for the given database
	 *
	 * @param str $database
	 * @return Snapper\Database
	 */
	public function getDatabase($database) {
		return $this->databases[$database];
	}
	
	/**
	 * Exports all tables of the given database
	 *
	 * @param str $database
	 * @param str $conf
	 * @param str $path
	 * @return void
	 */
	public function exportDatabase($database, $conf=NULL, $path=NULL) {
		$this->getDatabase($database)->exportTables($conf, $this->defaultFilepath($database, $path));
	}
	
	/**
	 * Iteratively exports all tables of each scanned database
	 *
	 * @param str $conf
	 * @param str $path
	 * @return voids
	 */
	public function exportDatabases($conf=NULL, $path=NULL) {
		foreach ($this->listDatabases() as $database) {
			if (!$this->isExcluded($database)) {
				$this->exportDatabase($database, $conf, $path);
			}
		}
	}
	
	/**
	 * Creates a filepath to the table export location
	 *
	 * Given an optional $path (defaults to the Snapper directory), builds
	 * a directory structure like:
	 *   $path/database_name/
	 *
	 * @param str $database
	 * @param str $path
	 * @return str
	 */
	protected function defaultFilepath($database, $path=NULL) {
		return sprintf(
			'%s/%s/',
			is_null($path) ? dirname(__FILE__) : $path,
			$database
		);
	}
}