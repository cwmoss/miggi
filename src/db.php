<?php

namespace miggi;

use PDO;

class db {

    public string $table = 'schema_migrations';

    public function __construct(public PDO $pdo) {
    }

    public function init() {
        $query = $this->create_versions_table_statement();
        return $this->pdo->exec($query);
    }

    public function create_versions_table_statement() {
        return sprintf('CREATE TABLE IF NOT EXISTS %s (
            version VARCHAR(255) PRIMARY KEY
          )', $this->table);
    }
}
