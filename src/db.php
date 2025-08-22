<?php

namespace miggi;

use Exception;
use PDO;

class db {

    public string $table = 'schema_migrations';

    public function __construct(public pdox $pdo, public String $prefix = "") {
        // if ($prefix) $this->table = $prefix . "_" . $this->table;
    }

    public function init() {
        $query = $this->create_versions_table_statement();
        return $this->pdo->exec($query);
    }

    public function checkin($key) {
        return $this->pdo->insert($this->table, ['version' => $key]);
        # $query = $this->create_checkin_statement($key);
        # return $this->pdo->exec($query);
    }

    public function checkout($key) {
        return $this->pdo->delete($this->table, 'WHERE version=?', [$key]);
        # $query = $this->create_checkout_statement($key);
        # return $this->pdo->exec($query);
    }

    public function fetch() {
        $res = $this->pdo->select_all($this->table, 'version', 'ORDER BY version ASC');
        return array_map(fn($r) => $r['version'], $res);
    }

    /*
    public function fetch_by_keys($keys=[]) {
        
        $placeholders = array_map(function(){return "?";}, $keys);
        $placeholders_string = join(", ", $placeholders);
        $querystring = "SELECT * 
            FROM $this->table
            WHERE version in ($placeholders_string)";
        // print "placeholders $placeholders_string";
        print ("+++querystring: $querystring");
        $sth = $this->pdo->prepare($querystring);
        $sth->execute($keys);
        $res = $sth->fetchAll();
        return $res;
        // return sprintf('SELECT version from %s WHERE id in ('.join(",", $keys).') ORDER BY version ASC', $this->table, $keys);
        
    }
    */

    public function create_versions_table_statement() {
        return sprintf('CREATE TABLE IF NOT EXISTS %s (
            version VARCHAR(255) PRIMARY KEY
          )', $this->pdo->table($this->table));
    }

    /*

    public function select_all_query() {
        return sprintf('SELECT version from %s ORDER BY version ASC', $this->table);
    }

    public function create_checkin_statement($key) {
        return sprintf('INSERT INTO %s (version)
            VALUES (%s)', $this->table, $key);
    }

    public function create_checkout_statement($key) {
        $co_stmt = sprintf('DELETE FROM %s WHERE version = \'%s\'', $this->table, $key);
        return $co_stmt;
    }
    */

    public function execute($query) {
        return $this->pdo->exec($query);
    }

    /**
     * Check if a table exists in the current database.
     *
     * @param string $table Table to search for.
     * @return bool TRUE if table exists, FALSE if no table found.
     */
    function table_exists($table) {
        $table = $this->pdo->table($table);

        // Try a select statement against the table
        // Run it in try-catch in case PDO is in ERRMODE_EXCEPTION.
        try {
            $result = $this->pdo->query("SELECT 1 FROM {$table} LIMIT 1");
        } catch (Exception $e) {
            // We got an exception (table not found)
            return FALSE;
        }

        // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
        return $result !== FALSE;
    }
}
