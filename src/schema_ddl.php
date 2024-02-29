<?php

namespace miggi;

if (!defined('ADODB_DIR')) define('ADODB_DIR', __DIR__ . '/adodb');
require_once(ADODB_DIR . '/adodb-lib.inc.php');
require_once(ADODB_DIR . '/adodb-datadict.inc.php');

use ADODB_DataDict;

class schema_ddl {

    public ADODB_DataDict $dict;
    public $db;

    public function __construct($db = null) {
        $this->init_adodb('sqlite');
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
        return $ct;
    }
}
