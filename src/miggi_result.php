<?php

namespace miggi;

use Throwable;

class miggi_result {

    /**
     *  @var migration[] $migrations
     */
    public array $migrations = [];
    public ?migration $failed_migration = null;
    public ?Throwable $exception = null;

    public function __construct(
        public string $msg,
        array $migrations = [],
        public bool $success = true
    ) {
        $this->migrations = $migrations;
    }

    public function failed(migration $failed_migration, Throwable $exception): self {
        $this->failed_migration = $failed_migration;
        $this->exception = $exception;
        $this->success = false;
        return $this;
    }

    public function ok_symbol(bool $ok): string {
        return $ok ? "✅" : "❌";
    }

    public function success_symbol(): string {
        return $this->success ? "👍" : "❌";
    }
    public function print() {
        print $this->success_symbol() . " " . $this->msg . "\n";
        foreach ($this->migrations as $mig) {
            print "  " . $this->ok_symbol(true) . " " . $mig->name . " " . $mig->status . "\n";
        }
        if ($this->failed_migration) {
            print "  " . $this->ok_symbol(false) . " " . $this->failed_migration->name . "\n";
            print $this->exception_trace();
        }
        print "\n";
    }
    public function exception_trace(): string {
        $e = $this->exception;
        if (!$e) return "";
        $file = $e->getFile();
        $line = $e->getLine();
        $trace  = $e->getTrace();
        return join("\n", [
            $e->getMessage(),
            sprintf(" in  %s:%s", $file, $line),
            $trace
        ]);
    }
}
