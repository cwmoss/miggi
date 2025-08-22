<?php

namespace miggi;

use Exception;
use InvalidArgumentException;

class miggi {

    public string $prefix;
    public string $prefix_placeholder_regex;
    public string $driver_name;

    public function __construct(
        public db $db,
        public string $dir,
        public array $opts,
        public array $switches,
        public bool $auto_answer = true
    ) {
        #print "miggi opts\n";
        #print_r($this->opts);
        $this->prefix = $this->opts['prefix'] ?? "";
        $this->prefix_placeholder_regex = '~/\*\s*prefix\s*\*/\s*~';
        $this->dir = rtrim($dir, '/') . '/';
        $this->driver_name = $this->db->pdo->driver_name;
    }

    public function init(): bool {
        $q = file_get_contents(__DIR__ . '/miggi.sql');

        $q = $this->handle_prefix($q, $this->opts['prefix'] ?? '', $total);
        $res = $this->db->execute($q);

        // $res = $this->db->init($this->opts['prefix'] ?? "");
        return $res === 0;
    }

    public function status($limit = null) {

        if (!$this->is_initialized()) {
            return "not yet initialized" . ($this->prefix ? " (with prefix " . $this->prefix . ")" : "") . "\n";
        }

        $applied = array_flip($this->fetch_applied()); // applied values as keys
        #print_r($applied);

        $available = $this->fetch_available();
        foreach ($available as $appmig) {
            if (isset($applied[$appmig->key])) {
                $appmig->status = "applied";
            } else {
                $appmig->status = "pending";
            }
            #print_r($appmig);
        }

        if ($limit) {
            $limit = abs($limit);
            $max = count($available);
            $limit = ($limit > $max ? $max : $limit);
            print "LIMIT:" . $limit;
            $available = array_slice($available, -$limit, null, true);
        }
        return $available;
        #return $this->merged($available, $applied);
    }

    public function new_migration($name) {
        if (!$name) throw new \LogicException('you must provide a name for your migration.');
        $fname = date('YmdHis') . '_' . $name . '.sql';
        $tpl = file_get_contents(__DIR__ . '/migration.tpl');
        if ($this->switches['prefixed'] ?? 0) {
            $tpl = str_replace(
                "table_name",
                "/*prefix*/ table_name",
                $tpl
            );
        }
        file_put_contents($this->dir . '/' . $fname, $tpl);
        return ($this->dir . '/' . $fname . "\n");
    }


    /* 
up - apply all pending migrations
down - go back 1 migration
to_version - go up or down to this version
*/

    /*
    einzelne migration ausführen
    private function
    */
    private function one($key, $direction): bool {

        if (!$this->check_key($key)) {
            throw new InvalidArgumentException("not a valid key {$key}");
        }

        $file = $this->get_migration_file($key);

        $this->db->pdo->beginTransaction();

        $stmt = match ($direction) {
            "up" => $this->up_stmt($file),
            "down" => $this->down_stmt($file)
        };

        if (!$stmt) {
            throw new InvalidArgumentException("no statements found in migration file {$file}");
        }

        if (!is_array($stmt)) $stmt = [$stmt];

        foreach ($stmt as $s) {
            $this->db->execute($s);
        }

        $checkf = "check" . ($direction == 'up' ? 'in' : 'out');
        $this->db->$checkf($key);
        $this->db->pdo->commit();

        return true;
    }



    // alle anstehenden migrationen ausführen
    public function up(): miggi_result {

        $result = new miggi_result("applying all pending migrations\n");

        if (!$this->initialize_if_not_already()) {
            $result->msg .= "operation canceled\n";
            return $result;
        }

        $available = $this->status();
        $appliedkeys = [];

        foreach ($available as $appmig) {
            if ($appmig->status === "pending") {
                $file = $this->dir . $appmig->file;
                $result->msg .= "{$appmig->key} - ausführen $file\n";
                $this->one($appmig->key, "up");
                $appliedkeys[] = $appmig->key;
            }
        }

        if (count($appliedkeys)) {
            // return applied migrations
            // optinal alle (status)
            $result->migrations =  ($this->fetch_by_keys($appliedkeys));
            $result->success = true;
        } else {
            $result->msg .= "no applicable migrations found\n";
        }

        return $result;
    }

    // remove last applied migration
    public function down(): miggi_result {

        $result = new miggi_result("removing last applied migration\n");

        if (!$this->initialize_if_not_already()) {
            $result->msg .= "operation canceled\n";
            return $result;
        }

        $applied = $this->fetch_applied();

        if (count($applied) == 0) {
            $result->msg .= "not able to migrate down - no more applied migrations\n";
            return $result;
        }

        $key = end($applied);
        $result->msg .= "migration {$key} entfernen \n";

        $this->one($key, "down");
        $result->migrations = $this->fetch_by_keys([$key]); //$this->status();
        $result->migrations[0]->status = "removed";
        return $result;
    }

    public function to_version($key) {

        $result = new miggi_result("migrating to version {$key}\n");

        $all = $this->status();

        $result->migrations = $all;

        if (!$this->check_key($key)) {
            $result->msg .= "not a valid key\n";
            return $result;
        }

        $latest = $this->latest();
        if ($latest == $key) {
            $result->msg .=  "up to date\n";
            return $result;
        } else if ($key > $latest) { //up
            $result->msg .= "migrate up\n";
            foreach ($all as $i => $mig) {
                $mig->status = "applied";
                if ($mig->key <= $latest || $mig->key > $key) {
                    unset($all[$i]);
                }
            }
            $direction = "up";
        } else { //down
            $result->msg .= "migrate down\n";
            foreach ($all as $i => $mig) {
                $mig->status = "applied";
                if ($mig->key <= $key || $mig->key > $latest) {
                    unset($all[$i]);
                }
            }
            $all = array_reverse($all);
            $direction = "down";
        }

        foreach ($all as $mig) {
            $this->one($mig->key, $direction);
        }

        // return applied migrations
        $result->migrations = $all; // $this->status(); //$this->fetch_by_keys($appliedkeys);
        return $result;
    }

    // status:
    //      applied / not-applied / missing
    public function merged($available, $applied) {
        return [$available, $applied];
    }

    /*
    returns list of migration-objects
    */
    public function fetch_available() {
        $candidates = glob($this->dir . '/*.{sql,php}', \GLOB_BRACE);

        $candidates = array_filter($candidates, function ($f) {
            if (!preg_match('!^\d{14}_!', basename($f))) {
                return false;
            }
            return true;
        });
        #print_r($candidates);
        $candidates = array_map(function ($filename) {
            list($key, $name) = explode('_', basename($filename, '.sql'), 2);
            return new migration($key, $name, $filename);
        }, $candidates);

        return $candidates;
    }


    public function fetch_applied() {
        // return ["20230320172951", "20230322155900"];
        // return ["20230320172951"];
        return $this->db->fetch();
    }



    // get all pending migrations
    // returns array (which can be empty)
    public function fetch_pending() {
        $available = $this->fetch_available();
        $applied = $this->fetch_applied();
        $pending = [];
        foreach ($available as $avmig) {
            if (!in_array($avmig->key, $applied)) {
                $pending[] = $avmig;
            }
        }
        return $pending;
    }

    public function fetch_by_keys($keys) {

        $candidates = glob($this->dir . '/*.sql');
        $result = [];

        $candidates = array_filter($candidates, function ($f) {
            if (!preg_match('!^\d{14}_!', basename($f))) {
                return false;
            }
            return true;
        });
        foreach ($candidates as $filename) {
            list($key, $name) = explode('_', basename($filename, '.sql'), 2);
            if (in_array($key, $keys)) {
                $appmig = new migration($key, $name, $filename);
                $appmig->status = "applied";
                $result[] = $appmig;
            }
        }
        // print_r($result);
        return $result;
    }

    public function handle_prefix(string $ddl, string $prefix, ?int &$total_found): string {
        $ddl = preg_replace($this->prefix_placeholder_regex, $prefix ? $prefix . "_" : "", $ddl, -1, $total_found);
        return $ddl;
    }

    public function statements_php(string $file, string $direction) {
        [$key, $name] = explode('_', basename($file, '.php'), 2);
        // $clasn = "miggi\\migrations\\$name";
        // include($file);
        $clasn = self::find_class_in_file($file);
        $m = new $clasn($this->driver_name, $this->prefix);
        $m->run($direction == "down");
        return $m->ddl;
    }

    // https://stackoverflow.com/questions/7153000/get-class-name-from-file
    static function find_class_in_file(string $file) {
        $classes = get_declared_classes();
        include $file;
        $diff = array_diff(get_declared_classes(), $classes);
        $class = reset($diff);
        return $class;
    }

    public function up_stmt($file) {
        $type = pathinfo($file, \PATHINFO_EXTENSION);

        if ($type == 'php') {
            return $this->statements_php($file, 'up');
        }

        $all = file_get_contents($file);

        #list($upstr, $downstr) = explode("-- migrate:down", $all);

        $upstr = strstr($all, "-- migrate:down", true); // alles vor migrate:down
        $upstr = trim(strstr($upstr, "-- migrate:up")); // alles vor migrate:up entfernen


        $p = $this->opts['prefix'] ?? "";
        if ($p) print("up_stmt prefix: " . $p . "\n");
        $upstr = preg_replace($this->prefix_placeholder_regex, $p ? $p . "_" : "", $upstr, -1, $replacements);
        if ($p && $replacements == 0) {
            print "placeholder for prefixes not found in migration file\n";
            return false;
        }


        // put checks here

        return $upstr;
    }

    public function down_stmt($file) {
        $type = pathinfo($file, \PATHINFO_EXTENSION);
        if ($type == 'php') {
            return $this->statements_php($file, 'down');
        }

        $all = file_get_contents($file);

        $downstr = trim(strstr($all, "-- migrate:down")); // alles nach migrate:down

        $p = $this->opts['prefix'] ?? "";
        if ($p) print("down_stmt prefix: " . $p . "\n");
        $downstr = preg_replace($this->prefix_placeholder_regex, $p ? $p . "_" : "", $downstr, -1, $replacements);
        if ($p && $replacements == 0) {
            print "placeholder for prefixes not found in migration file\n";
            return false;
        }

        print $downstr . "\n";
        // put checks here

        return $downstr;
    }


    // get the last applied version
    // returns a key or false
    public function latest() {
        $applied = $this->fetch_applied();
        if (count($applied)) {
            $res = end($applied);
        } else {
            $res = false;
        }
        return $res;
    }

    public function check_key($key) {
        if (preg_match('!^\d{14}!', $key)) {
            return $key;
        }
        return null;
    }

    // finds the array-index of a migration in a migration-list 
    public function find_index($list, $key) {
        foreach ($list as $k => $v) {
            if ($key == $k->key) {
                return $k;
            }
        }
        return false;
    }


    public function is_initialized() {
        // $tn = ($this->opts['prefix'] ?? "" ? $this->opts['prefix'] . "_" : "") . "schema_migrations";
        return $this->db->table_exists($this->db->table);
    }

    public function initialize_if_not_already() {
        if ($this->auto_answer) {
            $this->init();
            return true;
        }
        if (!$this->is_initialized()) {
            print "not yet initialized" . ($this->prefix ? " (with prefix " . $this->prefix . ")" : "") . "\n";
            print "do you want to initialize now? [yn]\n";
            $answer = $this->readc();
            if ($answer == 'y') {
                print "initializing...\n";
                $this->init();
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    private function get_migration_file($key) {
        $files = glob($this->dir . $key . '_*');
        if (count($files) > 1) {
            throw new Exception("multiple files with the same key {$key}??");
        } else if (count($files) < 1) {
            throw new Exception("no migration file for key {$key} found");
        } else {
            return $files[0];
        }
    }


    private function readc() {
        $stdinpointer = fopen("php://stdin", "r");
        $line = fgets($stdinpointer);
        fclose($stdinpointer);
        return trim($line);
    }
}
