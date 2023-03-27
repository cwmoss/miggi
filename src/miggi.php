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
    @key - nur eine bestimmte migration ausführen
    */
    public function up( $keys=null ) {
        $available = $this->status();
        /*
        if($keys) {
            print "folgende migrationen ausführen \n". join("\n", $keys)."\n";
        } else {
            print "alle anstehenden migrationen ausführen";
        }
        */

        foreach($available as $appmig){            
            if($appmig->status === "pending"){
                $file = $this->dir . $appmig->file;
                $stmt = $this->up_stmt( $file );
                // wenn kein(e) key(s) - alle ausführen
                // wenn key(s), nur diese migration(en) ausführen
                if(!$keys || in_array($appmig->key, $keys)){
                    print "{$appmig->key} - ausführen\n";
                    print $stmt . "\n";
                    $res = $this->db->execute($stmt);
                    if($res!==false){
                        $checkin_result = $this->db->checkin( $appmig->key );
                        if( $checkin_result !== false){
                            // fehler beim checkin
                        }
                    } else {
                        // fehler bei der migration 
                        print "fehler bei der migration\n";
                    }
                    print $res."\n";
                    // return $res;
                } else {
                    print "{$appmig->key} - nicht ausführen\n";
                }
            } elseif ($appmig->status === "applied") {
                print "bereits ausgeführt {$appmig->key}\n"; //
            } else {
                print "{$appmig->key} - nicht ausführen "; //
            }
        }
    
        return ( $this->status() );
    }

    public function down( $keys=null ) {
        $applied = $this->fetch_applied();
        
        if($keys) {
            print "folgende migrationen entfernen \n". join("\n", $keys)."\n";
            foreach($keys as $key){
                if(!in_array($key, $applied)){
                    print "😢 {$key} nicht in den ausgeführten migrationen gefunden\n"; //
                } else {
                    print "kann entfernt werden\n ";
                }
            }
        } else {
            print "die letzte migration entfernen\n";
        }
        print_r($applied);
        
/*
        foreach($available as $appmig){            
            if($appmig->status === "pending"){
                $file = $this->dir . $appmig->file;
                $stmt = $this->up_stmt( $file );
                // wenn kein(e) key(s) - alle ausführen
                // wenn key(s), nur diese migration(en) ausführen
                if(!$keys || in_array($appmig->key, $keys)){
                    print "{$appmig->key} - ausführen\n";
                    print $stmt . "\n";
                    $res = $this->db->execute($stmt);
                    if($res==0){
                        $checkin_result = $this->db->checkin( $appmig->key );
                        if( $checkin_result !== 0){
                            // fehler beim checkin
                        }
                    } else {
                        // fehler bei der migration 
                    }
                    print $res."\n";
                    // return $res;
                } else {
                    print "{$appmig->key} - nicht ausführen\n";
                }
            } elseif ($appmig->status === "applied") {
                print "bereits ausgeführt {$appmig->key}\n"; //
            } else {
                print "{$appmig->key} - nicht ausführen "; //
            }
        }
    */
        return ($applied);
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

    
}
