<?php

namespace miggi;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class logger extends AbstractLogger {

    function __construct(public $minlevel = LogLevel::DEBUG, public string $logfile = "php://stderr") {
    }

    public function log($level, $message, array $context = []): bool {

        $message = "\033[1;34m" . $message . "\033[0m";
        $context = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        error_log(date("Y-m-d H:i:s") . " " . $message . " " . $context . "\n", 3, $this->logfile);
        return false; // init() failed.
    }
}
