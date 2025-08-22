<?php

require_once(__DIR__ . '/vendor/autoload.php');

use miggi\ddl\ddl;

#set_exception_handler('miggi\\cli_exception_handler');

$test = "
id I AUTO KEY,
ip c(40),
status I1 NOTNULL,
blocked_until T,
created_at T NOTNULL,
modified_at T,
";

$ddl = new ddl('sqlite');
$ddl->parse_columns($test);
print_r($ddl);
print $ddl->create_table("huhu", $test);

print "\n\n###\n\n";

$test = "
rule c(3) KEY max 3,
val c(64) KEY,
status I1 NOTNULL,
blocked_until T,
block_count I,
created_at T NOTNULL,
modified_at T
";

$ddl = new ddl('sqlite');
$ddl->parse_columns($test);
print_r($ddl);
print $ddl->create_table("huhu", $test);

exit;

$schema = new miggi\schema_ddl();

$ddl = $schema->create_table("users", "
    id I AUTO KEY,
    ip c(40),
    blocked_until T,
    created_at T NOTNULL,
    modified_at T
");

print_r($ddl);
