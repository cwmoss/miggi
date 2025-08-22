<?php

namespace miggi\ddl;

use LogicException;

class sqlite extends driver {

    public function column_definition(column $col, array $keys): string {
        $ddl = [$col->name];
        $type = $this->type($col);
        $ddl[] = $type;
        if ($col->auto) {
            $ddl[] = "PRIMARY KEY AUTOINCREMENT NOT NULL"; # todo nn
            if (count($keys) > 1) {
                // print_r($keys);
                throw new LogicException("can't have multiple keys with autoincrement feature");
            }
        }
        if ($col->pk) {
            if (count($keys) == 1 && !$col->auto) {
                $ddl[] = "PRIMARY KEY";
            }
        }
        if ($col->unique) {
            $ddl[] = "UNIQUE";
        }
        if ($col->notnull && !$col->pk && !$col->auto) {
            $ddl[] = "NOT NULL";
        }
        if ($col->default !== null) {
            $ddl[] = "DEFAULT \"$col->default\"";
        }
        if ($col->max) {
            $ddl[] = "CHECK(LENGTH($col->name)<=$col->max)";
        }
        return join(" ", $ddl);
    }

    public function type(column $col) {
        return match ([$col->type, $col->size]) {
            [type::string, $col->size] => 'TEXT',
            [type::number, $col->size] => 'INTEGER',
            [type::datetime, $col->size] => 'TIMESTAMP',
            [type::timestamp, $col->size] => 'TIMESTAMP',
            default => 'TEXT'
        };
    }
}
