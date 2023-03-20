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
    default => help()
};

var_dump($res);

if ($cli->command == 'status') {
    $table = new TableBuilder;
    $rendered = $table->getTableRows($res[0], ['key', 'name', 'filename', 'status']);
    $table->echoTableRows($rendered);
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
