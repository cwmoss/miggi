<?php

namespace miggi;

class miggi_result {

    public function __construct(
        public string $msg, 
        public array $migrations = [], 
        public bool $success = false 
    ) {}
    
}