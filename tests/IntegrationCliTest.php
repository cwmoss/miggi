<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class IntegrationCliTest extends TestCase {


    function run_cli($args) {
        $base = realpath(__DIR__ . '/../');
        // $spool = $base . '/transfer/bancosprint_out/';
        chdir($base);
        $command = "bin/miggi $args";
        $result = `$command`;
        return $result;
    }

    public function testShowHelp(): void {
        $res = $this->run_cli("");
        $this->assertStringContainsString("i'm miggi", $res);
        // $this->assertSame($string, $email->asString());
    }
}
