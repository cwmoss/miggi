<?php

namespace miggi\ddl;

use LogicException;

class column {

    public function __construct(
        public string $name,
        public type $type = type::string,
        public ?int $size = null,
        public bool $pk = false,
        public bool $notnull = false,
        public mixed $default = null,
        public ?int $max = null,
        public bool $auto = false,
        public bool $unique = false
    ) {
    }

    public function set_specs(specs $spec, $args = null) {
        match ($spec) {
            specs::autoinc => $this->auto = true,
            specs::default => $this->default = $args,
            specs::maxlen => $this->max = $args,
            specs::notnull => $this->notnull = true,
            specs::unique => $this->unique = true,
            specs::primary_key => $this->pk = true,
            // default => throw new LogicException("spec not implemented: " . json_encode($spec))
        };

        return $this;
    }
}
