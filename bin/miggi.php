#!/usr/bin/env php
<?php

namespace miggi;

use PDO, Dotenv;

require_once(__DIR__ . '/../vendor/autoload.php');

set_exception_handler('miggi\\cli_exception_handler');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/..");
$dotenv->load();



$cli = new cli_parser($argv);
#print_r($cli);

#$prefix = $cli->opts['pre'] ?? "";

$pdo = new PDO('sqlite:test.db');
$db = new db($pdo, $cli->opts['prefix']??"");

$miggi = new miggi($db, __DIR__ . '/../tests/', $cli->opts, $cli->switches);

$res = match (true) {
    $cli->command == 'init' => $miggi->init(),
    $cli->command == 'status' => $miggi->status(),
    $cli->command == 'new' => $miggi->new_migration(join('_', $cli->args)),
    $cli->command == 'up' => $miggi->up(true),
    $cli->command == 'down' => $miggi->down(true),
    $cli->command == 'to_version' => $miggi->to_version($cli->args[0]),
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

\e[1m\e[96m   
    new \e[0m \e[93m name or description of the migration     \e[0m
        create a new migration file
\e[1m\e[96m   
    status  \e[0m    
        show available migrations and theri status
\e[1m\e[96m   
    up   \e[0m       
        do all pending migrations
\e[1m\e[96m   
    down \e[0m       
        undo latest (one) migration
\e[1m\e[96m   
    to_version \e[0m \e[93m key    \e[0m
        up- or downgrade to version specified by key


    options
    --prefix=xyz 
        with the commands init, status, up and down
        executes the command on a prefixed table, if exists


    switches
    --prefixed  
        with the command new
        creates a new migration file with prefixed table names


EOH;
/* //init muß nicht mehr vom nutzer ausgeführt werden (könnte aber)

  \e[1m\e[96m   
    init    \e[0m
        initialize the versions table to keep track of migrations

 */

}
