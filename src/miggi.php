<?php

namespace miggi;

use Exception;

class miggi {

    public function __construct(public db $db, public string $dir) {
    }

    public function init() {
        $res = $this->db->init();
        return $res;
    }

    public function status() {
        
        $applied = array_flip($this->fetch_applied()); // applied values as keys
        #print_r($applied);
        
        $available = $this->fetch_available();
        foreach($available as $appmig){            
            if(isset($applied[$appmig->key])){
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
        file_put_contents($this->dir . '/' . $fname, $tpl);
        return ($this->dir . '/' . $fname);
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
    private function one($key, $direction){
        print "{$key} ({$direction}) - ausführen\n";

        if(!$this->check_key($key)){ 
            print "not a valid key\n";
            return false;
        }

        $files = glob($this->dir.$key.'_*');
        if(count($files)>1) {
            throw new Exception("multiple files with the same key {$key}??");
        } else if(count($files)<1) {
            $err = "no migration file for key {$key} found\n";
            print $err;
            return false;
        } else {
            if($direction === "up"){
                $stmt = $this->up_stmt( $files[0] );
            } else if($direction === "down"){
                $stmt = $this->down_stmt( $files[0] );
            } else {
                $err = "direction parameter must be 'up' or 'down'\n";
                print $err;
            }

            #print $stmt."\n";

            $res = $this->db->execute($stmt);
            if($res!==false){

                if($direction === "up"){
                    print "checking in version {$key}\n";
                    $check_in_result = $this->db->checkin( $key );
                    if( $check_in_result == false){
                        $err = "fehler beim checkin\n";
                        print $err;
                        // migration eingefügt, schema_migrations aber nicht aktualisiert 
                        return false;
                    }
                } else {
                    print "checking out version {$key}\n";
                    $check_out_result = $this->db->checkout( $key );
                    if( $check_out_result == false){
                        $err = "fehler beim checkout\n";
                        print $err;
                        // migration entfernt, schema_migrations aber nicht aktualisiert 
                        return false;
                    }
                }
                
                return $key;
            
            } else {
                $err = "fehler bei der migration\n";
                print $err; 
                return false;
            }

        } 

    }
    //
    // alle anstehenden migrationen ausführen
    public function up( $stats=false ) {

        $available = $this->status();
        $appliedkeys = [];

        foreach($available as $appmig){            
            if($appmig->status === "pending"){
                $file = $this->dir . $appmig->file;
                  
                print "{$appmig->key} - ausführen\n";
                
                $res = $this->one($appmig->key, "up"); // returns migration key
                if($res) {
                    $appliedkeys[] = $res;
                }
                
                // print "\n+++res".$res."\n";
            } 
        }
        // print_r ($appliedkeys);

        if($stats==true) {
            return ($this->fetch_by_keys($appliedkeys));
        } else {
            return true;
        }
        

    }

    // remove last applied migration
    public function down( $stats=false ) {

        $applied = $this->fetch_applied();
        
        $key = end($applied);
        print "migration {$key} entfernen \n";
        
        $res = $this->one($key, "down"); // returns migration key
        
        if($stats==true) {
            return ($this->status());
        } else {
            return $res;
        }
        
    }

    public function to_version($key){

        $all = $this->status();
        $appliedkeys = [];
        
        if(!$this->check_key($key)){ 
            print "not a valid key\n";
            return $all;
        }
        
        $latest = $this->latest();
        if($latest == $key) {
            print "up to date";
            return $all;
        }
        print "key: ".$key." - latest: ".$latest."\n";
        
        
        if($key > $latest){ //up
            print "migrate up\n";
            foreach($all as $i => $mig){
                if($mig->key <= $latest || $mig->key > $key){
                    unset($all[$i]);
                } 
            }
            $all = array_map(function($m){ $m->status = "applying"; return $m; }, $all);
            $direction = "up";
        } else { //down
            print "rollback down\n";
            foreach($all as $i => $mig){
                if($mig->key <= $key || $mig->key > $latest){
                    unset($all[$i]);
                }
            }
            $all = array_reverse($all);
            $all = array_map(function($m){ $m->status = "rolling back"; return $m; }, $all);
            $direction = "down";
        }

        foreach($all as $mig){
            $res = $this->one($mig->key, $direction);
            $appliedkeys[] = $res;
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
    public function fetch_pending(){
        $available = $this->fetch_available();
        $applied = $this->fetch_applied();
        $pending = [];
        foreach($available as $avmig){
            if(!in_array($avmig->key, $applied)){
                $pending[] = $avmig;
            }
        }
        return $pending;
    }

    public function fetch_by_keys($keys){

        $candidates = glob($this->dir . '/*.sql');
        $result = [];

        $candidates = array_filter($candidates, function ($f) {
            if (!preg_match('!^\d{14}_!', basename($f))) {
                return false;
            }
            return true;
        });
        foreach ($candidates as $filename){
            list($key, $name) = explode('_', basename($filename, '.sql'), 2);
            if(in_array($key, $keys)) {
                $appmig = new migration($key, $name, $filename);
                $appmig->status = "applied";
                $result[] = $appmig;
            }
        }
        // print_r($result);
        return $result;
    }


    public function up_stmt( $file ){
        $all = file_get_contents($file);

        #list($upstr, $downstr) = explode("-- migrate:down", $all);

        $upstr = strstr($all, "-- migrate:down", true); // alles vor migrate:down
        $upstr = trim( strstr($upstr, "-- migrate:up") ); // alles vor migrate:up entfernen
        
        // put checks here

        return $upstr;
    }

    public function down_stmt( $file ){
        $all = file_get_contents($file);
        
        $downstr = trim( strstr($all, "-- migrate:down") ); // alles nach migrate:down
        
        // put checks here

        return $downstr;
    }

    // get the last applied version
    // returns a key or false
    public function latest(){
        $applied = $this->fetch_applied();
        if(count($applied)){
            $res = end($applied);
        } else {
            $res = false;
        }
        return $res;
    }

    public function check_key($key){
        if (preg_match('!^\d{14}!', $key)) {
            return $key;
        }
        return null;
    }

    // finds the array-index of a migration in a migration-list 
    public function find_index($list, $key){
        foreach ( $list as $k => $v ) {
            if ( $key == $k->key ) {
                return $k;
            }
        }
        return false;
    }

    
}
