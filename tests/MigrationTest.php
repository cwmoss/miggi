<?php

declare(strict_types=1);

use miggi\db;
use miggi\logger;
use PHPUnit\Framework\TestCase;
use miggi\miggi;
use miggi\pdox;

final class MigrationTest extends TestCase {

    public $output_directory;
    public $pdo;
    public $db;

    function get_miggi($args): miggi {
        $this->output_directory = __DIR__ . '/_output';

        print "unset";
        unset($this->db, $this->pdo);
        print "++ remove";
        unlink("{$this->output_directory}/unit.db");
        print "++ start\n";

        // `rm -rf {$this->output_directory}/*`;
        // $this->pdo = new PDO("sqlite:{$this->output_directory}/unit.db");
        $this->pdo = pdox::new_sqlite("{$this->output_directory}/unit.db");
        $this->pdo->logger = new logger();
        $this->db = new db($this->pdo);
        $miggi = new miggi($this->db, __DIR__ . '/migrations/sqlite', [], []);
        return $miggi;
    }

    public function testInit(): void {
        $miggi = $this->get_miggi("");
        $res = $miggi->init();
        $this->assertSame(true, $res);
        $res = $miggi->init();
        $this->assertSame(true, $res);
    }

    public function testAll(): void {
        $miggi = $this->get_miggi("");
        $res = $miggi->up();
        // $this->assertSame(4, count($res));
        $total = $this->pdo->fetch_first_cell('SELECT count(version) as total from schema_migrations');
        $this->assertSame(4, $total);

        $res = $miggi->down();
        $res = $miggi->down();
        $res = $miggi->down();
        $res = $miggi->down();
        $total = $this->pdo->fetch_first_cell('SELECT count(*) as total from schema_migrations');
        $this->assertSame(0, $total);

        $res = $miggi->up();
        $res = $miggi->to_version("20230322155900");
        $total = $this->pdo->fetch_first_cell('SELECT count(*) as total from schema_migrations');
        $this->assertSame(2, $total);
    }

    public function testNew(): void {
        $miggi = $this->get_miggi("");

        // neue migration erzeugen
        $res = $miggi->new_migration("some_new_migration");
        $this->assertStringContainsString("some_new_migration.sql", $res);
        $tpl = "
            -- migrate:up
            ALTER TABLE /*prefix*/ todos
            ADD importance int;
            -- migrate:down
            ALTER TABLE /*prefix*/ todos
            DROP COLUMN importance;
        ";
        $newfile = trim($res);
        file_put_contents($newfile, $tpl);

        // migriere mit testmigration
        $res = $miggi->up();
        $total = $this->pdo->fetch_first_cell('SELECT count(*) as total from schema_migrations');
        $this->assertSame($total, count($res->migrations));
        
        // testmigration entfernen
        $miggi->down();
        foreach (glob($newfile) as $filename) {
            #echo "$filename size " . filesize($filename) . "\n";
            unlink($filename);
        }
    }
}
