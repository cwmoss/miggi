<?php

require_once(__DIR__ . '/vendor/autoload.php');

use miggi\ddl\migration;
use miggi\ddl\type;
use miggi\ddl\specs;


#set_exception_handler('miggi\\cli_exception_handler');

class migtest01 extends migration {

    public function up() {
        $this->create_table("test01", "
                id I AUTO KEY,
                ip c(40),
                status I1 NOTNULL,
                blocked_until T,
                created_at T NOTNULL,
                modified_at T,
        ");

        $this->create_table("test02", "
                c_id I key,
                d_id I key,
                created_at T NOTNULL
                ");
        $this->create_table("test03")
            ->add_column("id", type::number, specs::autoinc, specs::notnull)
            ->add_column("token", type::string)
            ->add_column("created_at", type::timestamp);
        $this->drop_table("test04");
        $this->rename_column("users", "fname", "first_name");
        $this->create_table("test05")
            ->add_column("x_id", specs::primary_key, type::number)
            ->add_column("y_id", specs::primary_key, type::number)
            ->add_column("descr", type::string, 200);
        $this->add_column("users", "email c(200)");
        $this->add_column("users", "email2", specs::notnull, type::string, 150);
    }
}

$mig = new migtest01("test01");

$res = $mig->run();
print_r($res);
exit;

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
