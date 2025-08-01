<?php

namespace miggi\ddl;

use LogicException;

class migration {

    public array $statements = [];
    public bool $transaction = true;

    public ddl $ddl;

    public function __construct(public string $name) {
        $this->ddl = new ddl("sqlite");
    }

    public function run() {
        $this->up();
        $ddl = [];
        foreach ($this->statements as $statement) {
            $ddl[] = $this->ddl->create_statement($statement);
        }
        print_r($this);
        return $ddl;
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
    /*
rename_table
unrename_table
alter_table
add_column
drop_column
alter_column
rename_column
unrename_column
create_index
drop_index

function index($tab, $cols){
      if(preg_match("/,/", $cols)){
         $cols=str_replace(" ", "", $cols);
         $cols=str_replace(",", "_", $cols);
      }
      return $this->table($tab, true)."_idx_".$cols;
   }
*/
    public function rename_column(string $table, string $old, string $new) {
        $this->statements[] = ["rename_column", [$table, $old, $new]];
    }
    public function up() {
    }
    public function down() {
    }
}
