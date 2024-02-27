<?php

declare(strict_types=1);

use miggi\db;
use PHPUnit\Framework\TestCase;
use miggi\miggi;

final class MigrationTest extends TestCase {

    public $output_directory;
    public $pdo;
    public $db;

    function get_miggi($args): miggi {
        unset($this->db, $this->pdo);
        unlink("{$this->output_directory}/unit.db");

        $this->output_directory = __DIR__ . '/_output';
        // `rm -rf {$this->output_directory}/*`;
        $this->pdo = new PDO("sqlite:{$this->output_directory}/unit.db");
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
        $this->assertSame(4, count($res));
        $res = $miggi->down();
        $res = $miggi->down();
        $res = $miggi->down();
        $res = $miggi->down();
        $sel = $this->db->fetch('SELECT count(*) as total from schema_migrations');
        $this->assertSame("0", $sel['total']);
    }
}