<?php

namespace miggi;

class migration {

    public function __construct(
        public string $key,
        public ?string $name = null,
        public ?string $filename = null,
        public ?string $status = '-'
    ) {
    }
}
