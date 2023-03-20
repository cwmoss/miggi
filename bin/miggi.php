<?php

namespace miggi;

use PDO;

require_once(__DIR__ . '/../vendor/autoload.php');

$pdo = new PDO('sqlite:test.db');
$db = new db($pdo);

$miggi = new miggi($db, __DIR__ . '/../tests/');

print_r($argv);

$commands;
$my_args = array();

for ($i = 1; $i < count($argv); $i++) {
    if (preg_match('/^--([^=]+)=(.*)/', $argv[$i], $match)) {
        $my_args[$match[1]] = $match[2];
    } elseif (preg_match('/^--([^=]+)/', $argv[$i], $match)) {
        $my_args[$match[1]] = true;
    } else {
        $commands[$argv[$i]] = true;
    }
}
print_r($commands);
print_r($my_args);

if ($commands['init'] ?? false) {
    $res = $miggi->init();
    var_dump($res);
    exit;
}

if ($commands['new'] ?? false) {
    $desc = end($argv);
    if ($desc == 'new') {
        die("please give a name for the migration");
    }

    $res = $miggi->new_migration($desc);
    var_dump($res);
    exit;
}

if ($commands['status'] ?? false) {
    $desc = end($argv);
    if ($desc == 'new') {
        die("please give a name for the migration");
    }

    $res = $miggi->status();
    var_dump($res);
    exit;
}
