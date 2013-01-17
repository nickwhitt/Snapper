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
	
	public function exportTable($table, $conf=NULL, $path=NULL) {
		exec(sprintf(
			'%s > %s',
			$this->callMysqlDump($table, $conf),
			$this->destinationFile($table, $path)
		));
	}
	
	public function exportTables($conf=NULL, $path=NULL) {
		foreach ($this->tables as $table) {
			$this->exportTable($table, $conf, $path);
		}
	}
	
	protected function callMysqldump($table, $conf=NULL) {
		return sprintf(
			'/usr/bin/env mysqldump %s %s',
			is_null($conf) ? $this->database : sprintf('--defaults-file=%s %s', $conf, $this->database),
			$table
		);
	}
	
	protected function destinationFile($table, $path=NULL) {
		$this->validatePath($path);
		return sprintf(
			'%s%s.sql',
			is_null($path) ? sprintf('%s/', dirname(__FILE__)) : $path,
			$table
		);
	}
	
	protected function validatePath($path=NULL, $mode=0750) {
		if (!is_null($path) && !file_exists($path)) {
			mkdir($path, $mode, TRUE);
		}
	}
}