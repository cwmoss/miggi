<?php

namespace miggi;

use Exception;

class miggi {

    public string $prefix;
    public string $prefix_placeholder_regex;

    public function __construct(public db $db, public string $dir, public array $opts, public array $switches) {
        #print "miggi opts\n";
        #print_r($this->opts);
        $this->prefix = $this->opts['prefix'] ?? "";
        $this->prefix_placeholder_regex = '~/\*\s*prefix\s*\*/\s*~';
    }

    public function init(): bool {
        $q = file_get_contents(__DIR__ . '/miggi.sql');

        $q = $this->handle_prefix($q, $this->opts['prefix'] ?? '', $total);
        $res = $this->db->execute($q);

        // $res = $this->db->init($this->opts['prefix'] ?? "");
        return $res === 0;
    }

    public function status() {

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
    private function one($key, $direction) {

        $result = new miggi_result("{$key} ({$direction}) - ausführen\n", [$key], false);

        # print "{$key} ({$direction}) - ausführen\n";

        if (!$this->check_key($key)) {
            $result->msg .= "not a valid key\n";
            return $result;
        }

        $files = glob($this->dir . $key . '_*');
        if (count($files) > 1) {
            #throw new Exception("multiple files with the same key {$key}??");
            $result->msg .= "multiple files with the same key {$key}??\n";
            return $result;
        } else if (count($files) < 1) {
            $result->msg .= "no migration file for key {$key} found\n";
            return $result;
            #throw new Exception("no migration file for key {$key} found");
            #$err = "no migration file for key {$key} found\n";
            #print $err;
            #return false;
        } else {
            if ($direction === "up") {
                $stmt = $this->up_stmt($files[0]);
            } else if ($direction === "down") {
                $stmt = $this->down_stmt($files[0]);
            } else {
                $result->msg .= "direction parameter must be 'up' or 'down'\n";
                return $result;
                #$err = "direction parameter must be 'up' or 'down'\n";
                #print $err;
            }

            #print $stmt."\n";

            if (!$stmt) {
                $result->msg .= "not a valid statement\n";
                return $result;
                #$err = "not a valid statement\n";
                #print $err;
                #return false;
            }

            $res = $this->db->execute($stmt);
            if ($res !== false) {

                if ($direction === "up") {
                    $result->msg .=  "checking in version {$key}\n";
                    $check_in_result = $this->db->checkin($key);
                    if ($check_in_result == false) {
                        // migration eingefügt, schema_migrations aber nicht aktualisiert 
                        $result->msg .= "fehler beim checkin\n";
                        return $result;
                        #print $err;
                        #return false;
                    } else {
                        $result->success = true;
                    }
                } else {
                    $result->msg .=  "checking out version {$key}\n";
                    $check_out_result = $this->db->checkout($key);
                    if ($check_out_result == false) {
                        // migration entfernt, schema_migrations aber nicht aktualisiert 
                        $result->msg .= "fehler beim checkout\n";
                        return $result;
                        #$err = "fehler beim checkout\n";
                        #print $err;

                        #return false;
                    } else {
                        $result->success = true;
                    }
                }
                #$result->success = true;
                return $result;
                #return $key;
            } else {
                #$err = "fehler bei der migration\n";
                #print $err;
                #return false;
                $result->msg .= "fehler bei der migration\n";
                return $result;
            }
        }
    }



    // alle anstehenden migrationen ausführen
    public function up($stats = false) {

        if (!$this->initialize_if_not_already()) {
            return "operation canceled\n";
        }

        $available = $this->status();
        $appliedkeys = [];

        foreach ($available as $appmig) {
            if ($appmig->status === "pending") {
                $file = $this->dir . $appmig->file;

                print "{$appmig->key} - ausführen\n";

                $res = $this->one($appmig->key, "up"); // returns miggi_result --migration key--
                if ($res->success) {
                    $appliedkeys[] = $res->keys[0];
                } else {
                    $res->msg .= "upgrading stopped - refer to above errors\n";
                    #$err = "upgrading stopped - refer to above errors\n";
                    break;
                }
            }
        }
        if (!$res->success) {
            print $res->msg;
        }
        // print_r ($appliedkeys);

        if ($stats == true) {
            if (count($appliedkeys)) {
                return ($this->fetch_by_keys($appliedkeys));
            } else {
                return "no applicable migrations found\n";
            }
        } else {
            return true;
        }
    }

    // remove last applied migration
    public function down($stats = false) {

        if (!$this->initialize_if_not_already()) {
            return "operation canceled\n";
        }

        $applied = $this->fetch_applied();

        if (count($applied) == 0) {
            return "not able to migrate down - no more applied migrations\n";
        }

        $key = end($applied);
        print "migration {$key} entfernen \n";

        $res = $this->one($key, "down"); // returns migration key

        if ($stats == true) {
            if ($res->success) {
                return ($this->status());
            } else {
                print $res->msg;
                return "not able to migrate down";
            }
        } else {
            return $res;
        }
    }

    public function to_version($key) {

        $all = $this->status();
        $appliedkeys = [];

        if (!$this->check_key($key)) {
            print "not a valid key\n";
            return $all;
        }

        $latest = $this->latest();
        if ($latest == $key) {
            print "up to date";
            return $all;
        }
        print "key: " . $key . " - latest: " . $latest . "\n";


        if ($key > $latest) { //up
            print "migrate up\n";
            foreach ($all as $i => $mig) {
                if ($mig->key <= $latest || $mig->key > $key) {
                    unset($all[$i]);
                }
            }
            $all = array_map(function ($m) {
                $m->status = "applying";
                return $m;
            }, $all);
            $direction = "up";
        } else { //down
            print "rollback down\n";
            foreach ($all as $i => $mig) {
                if ($mig->key <= $key || $mig->key > $latest) {
                    unset($all[$i]);
                }
            }
            $all = array_reverse($all);
            $all = array_map(function ($m) {
                $m->status = "rolling back";
                return $m;
            }, $all);
            $direction = "down";
        }

        foreach ($all as $mig) {
            $res = $this->one($mig->key, $direction);
            $appliedkeys[] = $res->keys[0];
        }

        #print $appliedkeys;

        return $all;

        /*
        [0,1,2,3,4]
        key = 3, latest = 1 -> up from >1 bis 3

        
        key = 2, latest = 4 -> down from 4 to >2
        [0,1,2,3,4] -> [4,3,2,1,0]
        */
    }

    // status:
    //      applied / not-applied / missing
    public function merged($available, $applied) {
        return [$available, $applied];
    }

    public function fetch_available() {
        $candidates = glob($this->dir . '/*.sql');

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

    public function up_stmt($file) {
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
        $tn = ($this->opts['prefix'] ?? "" ? $this->opts['prefix'] . "_" : "") . "schema_migrations";
        return $this->db->table_exists($tn);
    }

    public function initialize_if_not_already() {
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


    private function readc() {
        $stdinpointer = fopen("php://stdin", "r");
        $line = fgets($stdinpointer);
        fclose($stdinpointer);
        return trim($line);
    }
}
