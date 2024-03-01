<?php

namespace miggi;

if (!defined('ADODB_DIR')) define('ADODB_DIR', __DIR__ . '/adodb');
require_once(ADODB_DIR . '/adodb-lib.inc.php');
require_once(ADODB_DIR . '/adodb-datadict.inc.php');

use ADODB_DataDict;
use DateTime;
use DateTimeImmutable;

class schema_ddl {

    public ADODB_DataDict $dict;
    public $db;
    public $dry = false;

    public array $ddl = [];

    public function __construct(public string $drivername, public string $prefix = "") {
        $this->init_adodb($drivername == 'pgsql' ? 'postgres' : $drivername);
    }

    public function init_adodb($drivername) {
        $path = ADODB_DIR . "/datadict/datadict-{$drivername}.inc.php";
        require_once($path);
        $class = "ADODB2_$drivername";
        /** @var ADODB_DataDict $dict */
        $dict = new $class();
        #$dict->dataProvider = $conn->dataProvider;
        #$dict->connection = $conn;
        $dict->upperName = strtoupper($drivername);
        #$dict->quote = $conn->nameQuote;
        if (!empty($conn->_connectionID)) {
            $dict->serverInfo = $conn->ServerInfo();
        }

        $this->dict = $dict;
    }

    public function create_table(string $table, string $cols) {
        //last character is ',' ? cut it off!
        $cols = trim($cols);
        // remove trailing commata
        $cols = rtrim($cols, ',');
        $ct = $this->dict->CreateTableSQL($table, $cols);

        // NO TRIGGERS
        foreach ($ct as $k => $ddl) {
            if (preg_match("/ trigger /i", $ddl)) unset($ct[$k]);
        }
        $this->exec($ct);
    }
    function drop_table($tab) {
        $this->exec($this->dict->DropTableSQL($this->table($tab)));
    }

    function rename_table($old, $new) {
        $this->exec($this->dict->RenameTableSQL($this->table($old), $this->table($new)));
    }

    function unrename_table($old, $new) {
        $this->rename_table($new, $old);
    }

    function alter_table($tab, $cols) {
        $this->exec($this->dict->ChangeTableSQL($this->table($tab), $cols));
    }

    function add_column($tab, $col) {
        $this->exec($this->dict->AddColumnSQL($this->table($tab), $col));
    }

    function drop_column($tab, $col) {
        $this->exec($this->dict->DropColumnSQL($this->table($tab), $col));
    }

    function alter_column($tab, $col) {
        $this->exec($this->dict->AlterColumnSQL($this->table($tab), $col));
    }

    function rename_column($tab, $old, $new) {
        $tab = $this->table($tab);
        $sql = $this->dict->RenameColumnSQL($tab, $old, $new);

        //mysql(i) want to know the fieldtype again...
        /* 
        if (preg_match("/mysql/", $this->dict->connection->databaseType)) {
            $sql[0] .= ' ' . $this->type_for_column($tab, $old);
        }
        */
        $this->exec($sql);
    }

    function table($tab, $short = false) {
        if ($short) {
            $nameL = explode("_", $tab);
            $name = "";
            foreach ($nameL as $n) {
                $name .= substr($n, 0, 3);
            }
            $tab = $name;
        }

        //if ($this->db && $this->db->prefix) {
        if ($this->prefix) {
            return $this->prefix . "_" . $tab;
        } else {
            return $tab;
        }
    }

    function index($tab, $cols) {
        if (preg_match("/,/", $cols)) {
            $cols = str_replace(" ", "", $cols);
            $cols = str_replace(",", "_", $cols);
        }
        return $this->table($tab, true) . "_idx_" . $cols;
    }

    /*
    function type_for_column($table, $column) {
        $schema = "";
        $this->dict->connection->_findschema($table, $schema);

        if ($schema) {
            $dbName = $this->dict->connection->database;
            $this->dict->connection->SelectDB($schema);
        }
        global $ADODB_FETCH_MODE;
        $save = $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_NUM;

        if ($this->dict->connection->fetchMode !== false) $this->dict->connection->SetFetchMode(false);
        $rs = $this->dict->connection->Execute(sprintf($this->dict->connection->metaColumnsSQL, $table));

        while (!$rs->EOF) {
            if ($rs->fields[0] == $column) {
                $found = $rs->fields[1];
                break;
            }
            $rs->MoveNext();
        }
        $rs->Close();

        $ADODB_FETCH_MODE = $save;

        #if(!$found)throw new Exception("No fieldtype found for Table: $table - Column: $column");
        return $found;
    }
    */

    /*
    function print_column_lengths($table) {
        $columns = $this->dict->MetaColumns($this->table($table));
        foreach ($columns as $column) {
            if (!$column->max_length || $column->type != 'varchar') continue;
            print "'" . $column->name . "'" . ' => ' . $column->max_length . ",\n";
        }
    }
    */

    function unrename_column($tab, $old, $new) {
        $this->rename_column($tab, $new, $old);
    }

    function create_index($tab, $cols, $extra = null, $idxname = null) {
        if ($extra == "UNIQUE") $extra = array($extra);
        if (!$idxname) $idxname = $this->index($tab, $cols);
        $this->exec($this->dict->CreateIndexSQL($idxname, $this->table($tab), $cols, $extra));
    }

    function drop_index($tab, $idxname) {
        $this->exec($this->dict->DropIndexSQL($this->index($tab, $idxname), $this->table($tab)));
    }

    function ddl($table, $ddl) {
        $ddl = str_replace("%TABLE%", $this->table($table), $ddl);
        $this->exec(array($ddl));
    }

    function exec($ddl) {
        $this->ddl = array_merge($this->ddl, $ddl);
        /*
        $debug = join("\n", $ddl);
        print "-- Generated DDL for Migration\n$debug\n";
        if (!$this->dry) $this->dict->ExecuteSQLArray($ddl);
        */
    }
}
