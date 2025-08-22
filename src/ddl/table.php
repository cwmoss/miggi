<?php

namespace miggi\ddl;

use LogicException;

class table {

    public array $columns = [];

    public function __construct(public string $name) {
    }

    public function add_colums_from_text_definitions(string $text) {
        $this->columns = $this->parse_columns($text);
        return $this;
    }

    public function add_column($name, ...$defs): self {
        $col = new column($name);
        // var_dump($defs);
        while ($def = current($defs)) {
            if ($def instanceof type) {
                $arg = next($defs);
                $col->type = $def;
                if ($arg && is_int($arg)) {
                    $col->size = $arg;
                    next($defs);
                }
                continue;
            }
            $args = $def->needs_arg() ? next($defs) : null;

            if ($def instanceof specs) {
                $col->set_specs($def, $args);
            } else {
                throw new LogicException("column definition for $name not recognizeable: " . var_export($def, true));
            }
            next($defs);
        }
        $this->columns[] = $col;
        return $this;
    }

    public function parse_columns(string $cols) {
        $cols = explode("\n", trim($cols));
        $cols = array_map(fn($c) => rtrim(trim($c), ","), $cols);
        $cols = array_map(fn($c) => $this->parse_column($c), $cols);
        // print_r($cols);
        return $cols;
    }

    public function parse_column(string $coltext) {
        $col = array_merge(array_filter(explode(' ', $coltext), fn($c) => (bool) trim($c)));
        $name = array_shift($col);
        $type = array_shift($col);
        [$type, $size] = $this->parse_type($type);
        $default = $max = null;
        $pk = $auto = $notnull = $unique = false;
        while ($token = array_shift($col)) {
            match (strtolower($token)) {
                'key' => $pk = $token,
                'notnull' => $notnull = true,
                'auto' => $auto = true,
                'unique' => $unique = true,
                'max' => $max = (int) array_shift($col),
                'default' => $default = array_shift($col),
                default => throw new LogicException("unrecognized column token >>$token<< in definition >>$coltext<<")
            };
        }
        return new column($name, $type, $size, $pk, $notnull, $default, $max, $auto, $unique);
    }

    public function parse_type(string $type): array {
        $type = strtolower($type);
        $size = null;
        if (preg_match("/^(\w+)\((\d+)\)$/", $type, $mat)) {
            $type = $mat[1];
            $size = $mat[2];
        } elseif (preg_match("/^(\w+)(\d+)$/i", $type, $mat)) {
            $type = $mat[1];
            $size = $mat[2];
        }
        // todo: more cases...
        $type = match ($type) {
            "c", "char", "varchar" => type::string,
            "i", "int", "integer" => type::number,
            "t" => type::timestamp,
            "dt" => type::datetime,
            "d" => type::date,
            default => $type
        };
        return [$type, $size];
    }
}
