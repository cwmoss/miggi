#!/usr/bin/env php
<?php

namespace miggi;

use PDO;

require_once(__DIR__ . '/../vendor/autoload.php');

set_exception_handler('miggi\\cli_exception_handler');

$pdo = new PDO('sqlite:test.db');
$db = new db($pdo);

$miggi = new miggi($db, __DIR__ . '/../tests/');

$cli = new cli_parser($argv);

// print_r($cli);

$res = match (true) {
    $cli->command == 'init' => $miggi->init(),
    $cli->command == 'status' => $miggi->status(),
    $cli->command == 'new' => $miggi->new_migration(join('_', $cli->args)),
    $cli->command == 'up' => $miggi->up( true ),
    $cli->command == 'down' => $miggi->down( true ),
    $cli->command == 'to_version' => $miggi->to_version( $cli->args[0] ),
    default => help()
};


#var_dump($res);

if (is_string($res)) {
    print $res;
} elseif ($cli->command == 'status') {
    #var_dump($res[0]);
    $table = new cli_table($res, ['key' => 'Version', 'descr' => 'Name', 'file' => 'File', 'status' => 'Status', 'date' => 'Date']);
    print $table->render();
} elseif ($cli->command == 'up') {
    print "migrate up\n";
    $table = new cli_table($res, ['key' => 'Version', 'descr' => 'Name', 'file' => 'File', 'status' => 'Status', 'date' => 'Date']);
    print $table->render();
} elseif ($cli->command == 'down') {
    print "migrate down\n";
    $table = new cli_table($res, ['key' => 'Version', 'descr' => 'Name', 'file' => 'File', 'status' => 'Status', 'date' => 'Date']);
    print $table->render();
} elseif ($cli->command == 'to_version') {
    if(count($res)) {
        $table = new cli_table($res, ['key' => 'Version', 'descr' => 'Name', 'file' => 'File', 'status' => 'Status', 'date' => 'Date']);
        print $table->render();
    }
} else {
    var_dump($res);
}

function cli_exception_handler($e) {
    print "\n😢 error\n   > " . $e->getMessage() . "\n";
}

function help() {
    print <<<EOH
😃 hi, i'm miggi, your friendly migration tool.
i know about these commands:

    init
    new     name or description of the migration 
    status
    up
    down

EOH;
}
