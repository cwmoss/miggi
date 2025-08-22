-- migrate:up
-- put your up migrations here

CREATE DATABASE databasename;

CREATE TABLE table_name (
    column1 datatype,
    column2 datatype,
    column3 datatype,
   ....
);

ALTER TABLE table_name
ADD column_name datatype;


-- migrate:down
-- can be left empty

DROP DATABASE databasename;

ALTER TABLE table_name
DROP COLUMN column_name;


