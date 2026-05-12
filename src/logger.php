<?php

namespace miggi;

use Psr\Log\AbstractLogger;
use Stringable;

class logger extends AbstractLogger {

    function __construct(public string $minlevel = "debug", public string $logfile = "php://stderr") {
    }

    public function log($level, Stringable|string $message, array $context = []): void {

        $message = "\033[1;34m" . $message . "\033[0m";
        $context = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        error_log(date("Y-m-d H:i:s") . " " . $message . " " . $context . "\n", 3, $this->logfile);
    }
}
