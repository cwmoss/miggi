<?php

namespace miggi\ddl;

enum type {
    case number;
    case serial;
    case string;
    case text;
    case text_xl;
    case date;
    case datetime;
    case datetimezone;
    case timestamp;
    case blob;
    case decimal;
    case float;

    public function needs_arg() {
        return match ($this) {
            default => false
        };
    }
}
