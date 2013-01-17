<?php
/**
 * MySQL Snapshot Generation Tool
 * Server Encapsulation
 *
 * @author Nick Whitt
 */

namespace Snapper;
class Database {
	protected $database;
	protected $tables;
	
	public function __construct($db, \PDO $pdo) {
		$this->database = $db;
		$this->tables = array();
		
		$pdo->query(sprintf('use %s', $this->database));
		foreach ($pdo->query('show tables', \PDO::FETCH_COLUMN, 0) as $table) {
			$this->tables[] = $table;
		}
	}
	
	public function getTables() {
		return $this->tables;
	}
	
	public function exportTable($table, $conf=NULL) {
		exec(sprintf(
			'/usr/bin/env mysqldump %s %s > %s.%s.sql',
			is_null($conf) ? $this->database : sprintf('--defaults-file=%s %s', $conf, $this->database),
			$table,
			$this->database,
			$table
		));
	}
	
	public function exportTables($conf=NULL) {
		foreach ($this->tables as $table) {
			$this->exportTable($table, $conf);
		}
	}
}