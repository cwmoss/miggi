<?php

namespace miggi;

use DateTimeImmutable;

class import_legacy {

    public static function import(string $from_directory, string $to_directory) {
        $from = glob("$from_directory/*.php");
        $from = array_filter($from, fn ($f) => preg_match('!^\d{3}_!', basename($f)));
        $start = new DateTimeImmutable('2020-01-01 05:00:00');
        $c = 0;
        foreach ($from as $legacy) {
            $parts = explode('_', basename($legacy, '.php'));
            $num = (int) array_shift($parts);
            $name = join("_", $parts);
            $classdef = ltrim(file_get_contents($legacy), " \n\r\t\v\0ph?<");
            $classdef = "<?php\nnamespace miggi\\migrations;\nuse miggi\\schema_ddl;\n\n" . $classdef;
            $classdef = preg_replace("~class migration_~i", "class ", $classdef);
            $classdef = preg_replace("~extends xorcstore_migration~i", " extends schema_ddl ", $classdef);
            $classdef = rtrim($classdef, "?\n\t >");
            $next = $start->modify("+{$num} days");
            $newfile = $to_directory . '/' . $next->format('YmdHis') . '_' . $name . '.php';
            file_put_contents($newfile, $classdef);
            $c++;
        }

        return "$c files imported from $from_directory to $to_directory\n";
    }
}
