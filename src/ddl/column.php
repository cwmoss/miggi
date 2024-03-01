<?php

namespace miggi\ddl;

use LogicException;

class column {

    public function __construct(
        public string $name,
        public string $type,
        public bool $pk = false,
        public bool $notnull = false,
        public mixed $default = null,
        public ?int $max = null,
        public bool $auto = false,
        public bool $unique = false
    ) {
        $this->parse_type($type);
    }

    public string $parsed_type;
    public ?int $size = null;

    public function parse_type(string $type): void {
        if (preg_match("/^(\w+)\((\d+)\)$/", $type, $mat)) {
            $this->parsed_type = strtolower($mat[1]);
            $this->size = $mat[2];
        } elseif (preg_match("/^i(\d+)$/i", $type, $mat)) {
            $this->parsed_type = 'i';
            $this->size = $mat[1];
        } else {
            $this->parsed_type = strtolower($type);
        }
    }

    public function sqlite_column_definition(array &$keys) {
        $ddl = $this->name . " ";
        $type = $this->sqlite_type();
        $ddl .= $type;
        if ($this->auto) {
            $ddl .= " PRIMARY KEY AUTOINCREMENT";
            if (count($keys) > 1) {
                // print_r($keys);
                throw new LogicException("can't have multiple keys with autoincrement feature");
            } else {
                $keys = [];
            }
        }
        if ($this->notnull) {
            $ddl .= " NOT NULL";
        }
        if ($this->default !== null) {
            $ddl .= " DEFAULT \"$this->default\"";
        }
        if ($this->max) {
            $ddl .= " CHECK(LENGTH($this->name)<=$this->max)";
        }
        return $ddl;
    }

    public function sqlite_type() {
        return match ([$this->parsed_type, $this->size]) {
            ['c', $this->size] => 'TEXT',
            ['i', $this->size] => 'INTEGER',
            ['t', $this->size] => 'TIMESTAMP'
        };
    }
}
