<?php

namespace miggi;

use Exception;
use PDO;

class db {

    public string $table = 'schema_migrations';

    public function __construct(public PDO $pdo) {
    }

    public function init() {
        $query = $this->create_versions_table_statement();
        return $this->pdo->exec($query);
    }

    public function fetch() {
        $query = $this->select_all_query();
        $stmt = $this->pdo->query($query);
        if ($stmt === false) {
            throw new Exception("could not query versions from {$this->table}");
        }
        $res = $stmt->fetchAll();
        if ($res === false) {
            throw new Exception("could not fetch versions from {$this->table}");
        }
        return array_map(fn ($r) => $r['version'], $res);
    }

    public function select_all_query() {
        return sprintf('SELECT version from %s ORDER BY version ASC', $this->table);
    }

    public function create_versions_table_statement() {
        return sprintf('CREATE TABLE IF NOT EXISTS %s (
            version VARCHAR(255) PRIMARY KEY
          )', $this->table);
    }
}
