<?php

require_once(__DIR__ . '/vendor/autoload.php');

#set_exception_handler('miggi\\cli_exception_handler');

$schema = new miggi\schema_ddl();

$ddl = $schema->create_table("users", "
    id I AUTO KEY,
    ip c(40),
    blocked_until T,
    created_at T NOTNULL,
    modified_at T
");

print_r($ddl);
