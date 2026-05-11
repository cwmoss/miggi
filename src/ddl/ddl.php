<?php

namespace miggi\ddl;

use LogicException;

class ddl {

    public driver $driver;

    public function __construct(public string $driver_name, public string $prefix) {
        $this->driver = match ($driver_name) {
            "sqlite" => new sqlite,
            "mysql" => new mysql,
            "postgres" => new postgres,
            default => new sqlite
        };
        if ($prefix) $this->prefix = $prefix . "_";
    }

    public function make_statement($statement): string {
        $ddl = match (true) {
            $statement instanceof table => $this->create_table($statement),
            is_array($statement) => call_user_func_array([$this, $statement[0]], $statement[1]),
            default => "-- not implemented\n"
        };
        return $ddl;
    }

    public function drop_table($name): string {
        return "DROP TABLE {$this->prefix}$name";
    }

    public function rename_table($old, $new): string {
        return "ALTER TABLE {$this->prefix}$old RENAME TO $new";
    }

    public function rename_column($table, $old, $new): string {
        return "ALTER TABLE {$this->prefix}$table RENAME COLUMN FROM $old TO $new";
    }

    public function add_column(table $table): string {
        $def = $this->driver->column_definition($table->columns[0], []);
        return "ALTER TABLE {$this->prefix}$table->name ADD COLUMN $def";
    }
    public function drop_column(string $table, string $name): string {
        return "ALTER TABLE {$this->prefix}$table DROP COLUMN $name";
    }

    public function alter_column(string $table, string $name): string {
        return "ALTER TABLE {$this->prefix}$table ALTER COLUMN $name";
    }

    public function create_index(string $table, string $name, array $cols, string $type = ""): string {
        $cols = join(", ", $cols);
        return "CREATE $type INDEX {$this->prefix}$name ON {$this->prefix}$table ($cols)";
    }

    public function drop_index(string $table, string $name, array $cols): string {
        return "DROP INDEX {$this->prefix}$name ON {$this->prefix}$table";
    }

    public function create_table(table $table): string {
        $keys = array_reduce($table->columns, function ($res, $item) {
            if ($item->pk) $res[] = $item->name;
            return $res;
        }, []);
        $ddl = ['CREATE TABLE ' . $this->prefix . $table->name . '('];
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
    public function create_columns(array $cols, array $keys): array {
        $ddl = [];
        foreach ($cols as $col) {
            $ddl[] = $this->driver->column_definition($col, $keys);
        }
        return $ddl;
    }
}
