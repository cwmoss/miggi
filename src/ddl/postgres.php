<?php
/*

CREATE TABLE accounts (
  user_id SERIAL PRIMARY KEY,
  username VARCHAR (50) UNIQUE NOT NULL,
  password VARCHAR (50) NOT NULL,
  email VARCHAR (255) UNIQUE NOT NULL,
  created_at TIMESTAMP NOT NULL,
  last_login TIMESTAMP
);

ALTER TABLE assets
    ALTER COLUMN location TYPE VARCHAR(255),
    ALTER COLUMN description TYPE VARCHAR(255);

    ALTER TABLE table_name
RENAME COLUMN column_name TO new_column_name;
*/