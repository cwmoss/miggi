<?php

namespace miggi;

class miggi_result {

    public function __construct(
        public string $msg, 
        public array $keys = [], 
        public bool $success = false 
    ) {}
    
}