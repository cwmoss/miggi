<?php

namespace miggi\ddl;

enum specs {
    case notnull;
    case maxlen;
    case default;
    case autoinc;
    case primary_key;
    case unique;

    public function needs_arg() {
        return match ($this) {
            self::default, self::maxlen => true,
            default => false
        };
    }
}
