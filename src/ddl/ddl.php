<?php

namespace miggi\ddl;

use LogicException;

class ddl {

    public function __construct(public string $driver_name) {
    }

    public function create_table($table, $cols) {
        $cols = $this->parse_columns($cols);
        $keys = array_reduce($cols, function ($res, $item) {
            if ($item->pk) $res[] = $item->name;
            return $res;
        }, []);
        $ddl = ['CREATE TABLE ' . $table . '('];
        [$cols_ddl, $keys] = $this->sqlite_create_table($cols, $keys);
        $ddl = array_merge($ddl, $cols_ddl);
        if ($keys) {
            $ddl[] = ",\n   PRIMARY KEY(" . join(", ", $keys) . ")";
        }
        $ddl[] = ')';
        return join("\n", $ddl);
    }

    /**
     * @param colum[] $cols
     */
    public function sqlite_create_table(array $cols, array $keys) {
        $ddl = [];
        foreach ($cols as $col) {
            $ddl[] = $col->sqlite_column_definition($keys);
        }
        return [["  " . join(",\n  ", $ddl)], $keys];
    }

    public function parse_columns(string $cols) {
        $cols = trim($cols);
        $cols = rtrim($cols, ',');
        $cols = explode(",\n", $cols);
        $cols = array_map(fn ($c) => $this->parse_column($c), $cols);
        print_r($cols);
        return $cols;
    }

    public function parse_column(string $coltext) {
        $col = array_merge(array_filter(explode(' ', $coltext), 'trim'));
        $name = array_shift($col);
        $type = array_shift($col);
        $default =  $max = null;
        $pk = $auto = $notnull = $unique = false;
        while ($token = array_shift($col)) {
            match (strtolower($token)) {
                'key' => $pk = $token,
                'notnull' => $notnull = true,
                'auto' => $auto = true,
                'unique' => $unique = true,
                'max' => $max = array_shift($col),
                'default' => $default = array_shift($col),
                default => throw new LogicException("unrecognized column token >>$token<< in definition >>$coltext<<")
            };
        }
        return new column($name, $type, $pk, $notnull, $default, $max, $auto, $unique);
    }
}
