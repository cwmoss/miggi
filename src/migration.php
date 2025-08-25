<?php

namespace miggi;

use DateTime;

class migration {

    public function __construct(
        public string $key,
        public ?string $name = null,
        public ?string $filename = null,
        public ?string $type = "sql",
        public ?string $status = '-'
    ) {
    }

    public function __get($name) {
        if ($name == 'file') {
            return basename($this->filename);
        }
        if ($name == 'date') {
            return DateTime::createFromFormat('YmdHis', $this->key)->format('d.m.y H:i');
        }
        if ($name == 'descr') {
            return str_replace(['-', '_'], ' ', $this->name);
        }
    }
}
