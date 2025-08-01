<?php

namespace miggi\ddl;

use LogicException;

class ddl {

    public sqlite $driver;

    public function __construct(public string $driver_name) {
        $this->driver = new sqlite;
    }

    public function create_statement($statement) {
        $ddl = match (true) {
            $statement instanceof table => $this->create_table($statement),
            is_array($statement) => call_user_func_array([$this, $statement[0]], $statement[1]),
            default => "-- not implemented\n"
        };
        return $ddl;
    }

    public function drop_table($name) {
        return "DROP TABLE $name";
    }

    public function rename_column($table, $old, $new) {
        return "ALTER TABKE $table RENAME COLUMN FROM $old TO $new";
    }

    public function create_table(table $table) {
        $keys = array_reduce($table->columns, function ($res, $item) {
            if ($item->pk) $res[] = $item->name;
            return $res;
        }, []);
        $ddl = ['CREATE TABLE ' . $table->name . '('];
        $cols_ddl = $this->create_columns($table->columns, $keys);

        if ($keys && count($keys) > 1) {
            // table ddl
            $cols_ddl[] = "PRIMARY KEY(" . join(", ", $keys) . ")";
        }
        $ddl[] = join(",\n  ", $cols_ddl);
        $ddl[] = ')';
        return join("\n  ", $ddl);
    }

    /**
     * @param column[] $cols
     */
    public function create_columns(array $cols, array $keys) {
        $ddl = [];
        foreach ($cols as $col) {
            $ddl[] = $this->driver->column_definition($col, $keys);
        }
        return $ddl;
    }
}
