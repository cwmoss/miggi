<?php
/*

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);
*/

namespace miggi\ddl;

use LogicException;

class mysql extends driver {

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
            $ddl[] = "NOTNULL";
        }
        if ($col->default !== null) {
            $ddl[] = "DEFAULT \"$col->default\"";
        }
        if ($col->max) {
            // $ddl[] = "CHECK(LENGTH($col->name)<=$col->max)";
        }
        return join(" ", $ddl);
    }

    public function type(column $col): string {
        return match ([$col->type, $col->size]) {
            [type::string, $col->size] => 'VARCHAR($col->size)',
            [type::number, $col->size] => 'INTEGER',
            [type::datetime, $col->size] => 'DATETIME',
            [type::timestamp, $col->size] => 'DATETIME',
            default => 'TEXT'
        };
    }
}
