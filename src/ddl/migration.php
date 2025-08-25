<?php

namespace miggi\ddl;

use LogicException;

class migration {

    public array $statements = [];
    public bool $transaction = true;

    public ddl $ddl;

    public function __construct(public string $driver_name, public string $prefix = "") {
        $this->ddl = new ddl($driver_name, $prefix);
    }

    public function run($down = false) {
        if ($down) {
            $this->down();
        } else {
            $this->up();
        }
        $ddl = [];
        foreach ($this->statements as $statement) {
            if (is_string($statement)) $ddl[] = $statement;
            else $ddl[] = $this->ddl->make_statement($statement);
        }
        // print_r($this);
        // var_dump(["+++ ddl", $ddl]);
        return $ddl;
    }

    // raw ddl statement, supports %TABLE% prefix
    public function ddl(string $table, string $ddl) {
        $this->statements[] = str_replace("%TABLE%", $this->table_name($table), $ddl);
    }

    public function create_table(string $name, ?string $definitions = null): table {
        $table = new table($name);
        if ($definitions) {
            $table->add_colums_from_text_definitions($definitions);
        }
        $this->statements[] = $table;
        return $table;
    }

    public function drop_table(string $name) {
        $this->statements[] = ["drop_table", [$name]];
    }

    public function rename_table(string $oldname, string $newname) {
        $this->statements[] = ["rename_table", [$oldname, $newname]];
    }

    public function unrename_table(string $oldname, string $newname) {
        $this->statements[] = ["rename_table", [$newname, $oldname]];
    }

    public function add_column(string $table, string $name_w_defs, ...$defs) {
        $table = new table($this->table_name($table));
        if (!$defs) {
            $table->add_colums_from_text_definitions($name_w_defs);
        } else {
            $table->add_column($name_w_defs, ...$defs);
        }
        $this->statements[] = ["add_column", [$table]];
    }

    public function drop_column(string $table, string $name) {
        $this->statements[] = ["drop_column", [$table, $name]];
    }

    public function alter_column(string $table, string $name) {
        $this->statements[] = ["alter_column", [$table, $name]];
    }

    public function rename_column(string $table, string $old, string $new) {
        $this->statements[] = ["rename_column", [$table, $old, $new]];
    }

    public function unrename_column(string $table, string $old, string $new) {
        $this->statements[] = ["rename_column", [$table, $old, $new]];
    }

    public function create_index(string $table, string|array $cols, string $type = "", string $index_name = "") {
        $cols = $this->cols($cols);
        if (!$index_name) $index_name = $this->index_name($table, $cols);
        $type = strtoupper($type);
        if ($type && $type != "UNIQUE") {
            throw new LogicException("index type not supported. only UNIQUE is supported.");
        }
        $this->statements[] = ["create_index", [$table, $index_name, $cols, $type]];
    }

    public function drop_index(string $table, string|array $cols, string $index_name = "") {
        $cols = $this->cols($cols);
        if (!$index_name) $index_name = $this->index_name($table, $cols);
        $this->statements[] = ["drop_index", [$table, $index_name, $cols]];
    }

    public function table_name(string $name, bool $shorten = false): string {
        // legacy option to shorten long table names
        if ($shorten) {
            $name = join("", array_map(fn($part) => substr($part, 0, 3), explode("_", $name)));
        }
        if ($this->prefix) $name = $this->prefix . "_" . $name;
        return $name;
    }

    private function cols(string|array $cols): array {
        if (is_array($cols)) return $cols;
        $cols = explode(",", $cols);
        return array_map(fn($c) => trim($c), $cols);
    }

    private function index_name(string $table, string|array $cols): string {
        $cols = join("_", $this->cols($cols));
        return $this->table_name($table) . "_idx_" . $cols;
    }

    public function up() {
    }
    public function down() {
    }
}
