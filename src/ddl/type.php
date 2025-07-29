<?php

namespace miggi\ddl;

enum type {
    case number;
    case string;
    case date;
    case datetime;
    case datetimezone;
    case blob;
    case decimal;

    public function needs_arg() {
        return match ($this) {
            default => false
        };
    }
}
