<?php
/**
 * MySQL Snapshot Generation Tool
 *
 * Provides table scanning and export methods on a MySQL database.
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/PayPalAPI Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace Snapper;
class Database {
	protected $database;
	protected $tables = array();
	protected $excludes = array();
	
	public function __construct($db, \PDO $pdo) {
		$this->database = $db;
		
		// scan all tables within the database
		$pdo->query(sprintf('use %s', $this->database));
		foreach ($pdo->query('show tables', \PDO::FETCH_COLUMN, 0) as $table) {
			$this->tables[] = $table;
		}
	}
	
	/**
	 * Lists all scanned tables
	 *
	 * @param void
	 * @return array
	 */
	public function listTables() {
		return $this->tables;
	}
	
	/**
	 * Prevents the table from being scanned or exported
	 *
	 * @param str $table
	 * @return void
	 */
	public function excludeTable($table) {
		if (!$this->isExcluded($table)) {
			$this->excludes[] = $table;
		}
	}
	
	/**
	 * Prevents all the given tables from being scanned or exported
	 *
	 * @param array $tables
	 * @return void
	 */
	public function excludeTables(array $tables) {
		foreach ($tables as $table) {
			$this->excludeTable($table);
		}
	}
	
	/**
	 * Tests if the given table is excluded from being scanned or exported
	 *
	 * @param str $table
	 * @return bool
	 */
	public function isExcluded($table) {
		return in_array($table, $this->excludes);
	}
	
	/**
	 * Exports the given table
	 *
	 * Utilizes mysqldump to create a file at the given filepath location.
	 *
	 * @param str $table
	 * @param str $conf
	 * @param str $path
	 * @return void
	 */
	public function exportTable($table, $conf=NULL, $path=NULL) {
		exec(sprintf(
			'%s > %s',
			$this->callMysqlDump($table, $conf),
			$this->destinationFile($table, $path)
		));
	}
	
	/**
	 * Exports all tables in the database
	 *
	 * @param str $conf
	 * @param str $path
	 * @return void
	 */
	public function exportTables($conf=NULL, $path=NULL) {
		foreach ($this->tables as $table) {
			if (!$this->isExcluded($table)) {
				$this->exportTable($table, $conf, $path);
			}
		}
	}
	
	/**
	 * Generates the mysqldump command to be executed by exportTable
	 *
	 * Utilizes an optional defaults file for [client] and [mysqldump] options.
	 *
	 * @param str $table
	 * @param str $conf
	 * @return str
	 */
	protected function callMysqldump($table, $conf=NULL) {
		return sprintf(
			'/usr/bin/env mysqldump %s %s',
			is_null($conf) ? $this->database : sprintf('--defaults-file=%s %s', $conf, $this->database),
			$table
		);
	}
	
	/**
	 * Creates a filepath to the table export
	 *
	 * Given an optional $path (defaults to the Snapper directory), builds
	 * a filepath like:
	 *   $path/table_name.sql
	 *
	 * @param str $table
	 * @param str $path
	 * @return str
	 */
	protected function destinationFile($table, $path=NULL) {
		$this->validatePath($path);
		return sprintf(
			'%s%s.sql',
			is_null($path) ? sprintf('%s/', dirname(__FILE__)) : $path,
			$table
		);
	}
	
	/**
	 * Ensures the given filepath is a valid system location
	 *
	 * @param str $path
	 * @param octal $mode
	 * @return void
	 */
	protected function validatePath($path=NULL, $mode=0750) {
		if (!is_null($path) && !file_exists($path)) {
			mkdir($path, $mode, TRUE);
		}
	}
}