<?php

namespace miggi\migrations;

use miggi\schema_ddl;

class guard_v2  extends schema_ddl {

   /*
   CREATE TABLE IF NOT EXISTS guard_blocked (
    rule CHAR(3) NOT NULL,
    val VARCHAR(64),
    blocked_until TIMESTAMP,
    block_count INT,
    created_at TIMESTAMP NOT NULL,
    modified_at TIMESTAMP,
    PRIMARY KEY (rule, val)
);

----
CREATE TABLE IF NOT EXISTS guard_failed_attempts(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip VARCHAR(40) NOT NULL,
    user VARCHAR(64) NOT NULL,
    data VARCHAR(64) NOT NULL,
    message VARCHAR(64),
    created_at TIMESTAMP
);
*/

   function up() {
      $this->create_table("guard_blocked", "
         rule c(3) KEY,
         val c(64) KEY,
         status I1 NOTNULL,
         blocked_until T,
         block_count I,
         created_at T NOTNULL,
         modified_at T
         ");


      $this->create_table("guard_failed_attempts", "
         id I AUTO KEY,
         ip c(40) NOTNULL,
         user c(64) NOTNULL,
         data c(64) NOTNULL,
         message c(64),
         created_at T
         ");
   }

   function down() {
      $this->drop_table("guard_blocked");
      $this->drop_table("guard_failed_attempts");
   }
}
