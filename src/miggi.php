<?php

namespace miggi;

class miggi {

    public function __construct(public db $db, public string $dir) {
    }

    public function init() {
        $res = $this->db->init();
        return $res;
    }

    public function status() {
        $applied = $this->fetch_applied();
        $available = $this->fetch_available();
        return $this->merged($available, $applied);
    }

    public function new_migration($name) {
        $fname = date('YmdHis') . '_' . $name . '.sql';
        $tpl = file_get_contents(__DIR__ . '/migration.tpl');
        file_put_contents($this->dir . '/' . $fname, $tpl);
        return ($this->dir . '/' . $fname);
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
        return [];
    }
}
