<style>
    td{
        vertical-align:top;
    }
a {
    color:red;
}
</style>

# migrations cheat sheet




## [create database](https://www.w3schools.com/sql/sql_create_db.asp)
```
CREATE DATABASE databasename;
```


## [drop database](https://www.w3schools.com/sql/sql_drop_db.asp)
```
DROP DATABASE databasename;
```



## [create table](https://www.w3schools.com/sql/sql_create_table.asp)

```
CREATE TABLE table_name (
    column1 datatype,
    column2 datatype,
    column3 datatype [constraint],  (s.u.)
   ....
);
```


## [drop table](https://www.w3schools.com/sql/sql_drop_table.asp)

DROP TABLE table_name;

## [alter table](https://www.w3schools.com/sql/sql_alter.asp) 

### add column
```
ALTER TABLE table_name
ADD column_name datatype;
```

### drop column
```
ALTER TABLE table_name
DROP COLUMN column_name;
```

### rename column
```
ALTER TABLE table_name
RENAME COLUMN old_name to new_name;
```

### modify datatype

#### sql-server, ms access
```
ALTER TABLE table_name
ALTER COLUMN column_name datatype;
```

#### mysql, oracle <10G
```
ALTER TABLE table_name
MODIFY COLUMN column_name datatype;
```

#### oracle >=10G
```
ALTER TABLE table_name
MODIFY column_name datatype;
```





## [constraints](https://www.w3schools.com/sql/sql_constraints.asp)

NOT NULL - Ensures that a column cannot have a NULL value
UNIQUE - Ensures that all values in a column are different
PRIMARY KEY - A combination of a NOT NULL and UNIQUE. Uniquely identifies each row in a table 
FOREIGN KEY - Prevents actions that would destroy links between tables    
CHECK - Ensures that the values in a column satisfies a specific condition    
DEFAULT - Sets a default value for a column if no value is specified  
CREATE INDEX - Used to create and retrieve data from the database very quickly    



AUTO_INCREMENT









## [datatypes](https://www.w3schools.com/sql/sql_datatypes.asp)


### Compatibility

The following types (or spellings thereof) are specified by SQL: 
- bigint, 
- bit, 
- bit varying, 
- boolean, 
- char, 
- character varying, 
- character, 
- varchar, 
- date, 
- double precision, 
- integer, 
- interval, 
- numeric, 
- decimal, 
- real, 
- smallint, 
- time (with or without time zone), 
- timestamp (with or without time zone), 
- xml.




### mysql

#### strings

| type | description |
| ------ | ----- |
| CHAR(size)	   |      A FIXED length string (can contain letters, numbers, and special characters). The size parameter specifies the column length in characters - can be from 0 to 255. Default is 1 |
| VARCHAR(size)	   |  A VARIABLE length string (can contain letters, numbers, and special characters). The size parameter specifies the maximum string length in characters - can be from 0 to 65535 |
| BINARY(size)	   |  Equal to CHAR(), but stores binary byte strings. The size parameter specifies the column length in bytes. Default is 1 |
| VARBINARY(size)	|     Equal to VARCHAR(), but stores binary byte strings. The size parameter specifies the maximum column length in bytes. |
| TINYBLOB	       |  For BLOBs (Binary Large Objects). Max length: 255 bytes |
| TINYTEXT	       |  Holds a string with a maximum length of 255 characters |
| TEXT(size)	   |      Holds a string with a maximum length of 65,535 bytes |
| BLOB(size)	   |      For BLOBs (Binary Large Objects). Holds up to 65,535 bytes of data |
| MEDIUMTEXT	   |      Holds a string with a maximum length of 16,777,215 characters |
| MEDIUMBLOB	   |      For BLOBs (Binary Large Objects). Holds up to 16,777,215 bytes of data |
| LONGTEXT	       |  Holds a string with a maximum length of 4,294,967,295 characters |
| LONGBLOB	       |  For BLOBs (Binary Large Objects). Holds up to 4,294,967,295 bytes of data |
| ENUM(val1, val2, val3, ...)	| A string object that can have only one value, chosen from a list of possible values. You can list up to 65535 values in an ENUM list. If a value is inserted that is not in the list, a blank value will be inserted. The values are sorted in the order you enter them |
| SET(val1, val2, val3, ...)	| A string object that can have 0 or more values, chosen from a list of possible values. You can list up to 64 values in a SET list |

#### numbers

| type | description |
| ------ | ----- |
| BIT(size)	       | A bit-value type. The number of bits per value is specified in size. The size parameter can hold a value from 1 to 64. The default value for size is 1.
| TINYINT(size)	   | A very small integer. Signed range is from -128 to 127. Unsigned range is from 0 to 255. The size parameter specifies the maximum display width (which is 255)
| BOOL	           | Zero is considered as false, nonzero values are considered as true.
| BOOLEAN	       |     Equal to BOOL
| SMALLINT(size)	|     A small integer. Signed range is from -32768 to 32767. Unsigned range is from 0 to 65535. The size parameter specifies the maximum display width (which is 255)
| MEDIUMINT(size)	|     A medium integer. Signed range is from -8388608 to 8388607. Unsigned range is from 0 to 16777215. The size parameter specifies the maximum display width (which is 255)
| INT(size)	       | A medium integer. Signed range is from -2147483648 to 2147483647. Unsigned range is from 0 to 4294967295. The size parameter specifies the maximum display width (which is 255)
| INTEGER(size)	   | Equal to INT(size)
| BIGINT(size)	   | A large integer. Signed range is from -9223372036854775808 to 9223372036854775807. Unsigned range is from 0 to 18446744073709551615. The size parameter specifies the maximum display width (which is 255)
| FLOAT(size, d)	|     A floating point number. The total number of digits is specified in size. The number of digits after the decimal point is specified in the d parameter. This syntax is deprecated in MySQL 8.0.17, and it will be removed in future MySQL versions
| FLOAT(p)	       | A floating point number. MySQL uses the p value to determine whether to use FLOAT or DOUBLE for the resulting data type. If p is from 0 to 24, the data type becomes FLOAT(). If p is from 25 to 53, the data type becomes DOUBLE()
| DOUBLE(size, d)	|     A normal-size floating point number. The total number of digits is specified in size. The number of digits after the decimal point is specified in the d parameter
| DOUBLE PRECISION(size, d)	 | 
| DECIMAL(size, d)	| An exact fixed-point number. The total number of digits is specified in size. The number of digits after the decimal point is specified in the d parameter. The maximum number for size is 65. The maximum number for d is 30. The default value for size is 10. The default value for d is 0.
| DEC(size, d)	   |  Equal to DECIMAL(size,d)

#### dates

| type | description |
| ------ | ----- |
| DATE	          |  A date. Format: YYYY-MM-DD. The supported range is from '1000-01-01' to '9999-12-31'
| DATETIME(fsp)	  |  A date and time combination. Format: YYYY-MM-DD hh:mm:ss. The supported range is from '1000-01-01 00:00:00' to '9999-12-31 23:59:59'. Adding DEFAULT and ON UPDATE in the column definition to get automatic initialization and updating to the current date and time
| TIMESTAMP(fsp)|	 A timestamp. TIMESTAMP values are stored as the number of seconds since the Unix epoch ('1970-01-01 00:00:00' UTC). Format: YYYY-MM-DD hh:mm:ss. The supported range is from '1970-01-01 00:00:01' UTC to '2038-01-09 03:14:07' UTC. Automatic initialization and updating to the current date and time can be specified using DEFAULT CURRENT_TIMESTAMP and ON UPDATE CURRENT_TIMESTAMP in the column definition
| TIME(fsp)	      |  A time. Format: hh:mm:ss. The supported range is from '-838:59:59' to '838:59:59'
| YEAR	          |  A year in four-digit format. Values allowed in four-digit format: 1901 to 2155, and 0000. <br><sub>(MySQL 8.0 does not support year in two-digit format.)</sub>





### POSTGRES builtin datatypes

|Name	                                |Aliases	        |      Description
| ------                                | -----             | ----- |
|bigint	                                |int8	            |   igned eight-byte integer
|bigserial	                            |serial8	        |      autoincrementing eight-byte integer
|bit [ (n) ]	 	                        |                   |   fixed-length bit string
|bit varying [ (n) ]	                    |varbit [ (n) ]	    |   ariable-length bit string
|boolean	                                |bool	            |   ogical Boolean (true/false)
|box	 	                                |                   |   rectangular box on a plane
|bytea	 	                            |                   |   binary data (“byte array”)
|character [ (n) ]	                    |char [ (n) ]	    |   ixed-length character string
|character varying [ (n) ]	            |varchar [ (n) ]	|      variable-length character string
|cidr	 	                            |                   |   IPv4 or IPv6 network address
|circle	 	                            |                   |   circle on a plane
|date	 	                            |                   |   calendar date (year, month, day)
|double precision	                    |float8	            |   ouble precision floating-point number (8 |bytes)            |
|inet	 	                            |                   |   IPv4 or IPv6 host address
|integer	                                |int, int4	        |   igned four-byte integer
|interval [ fields ] [ (p) ]	            | 	                |   ime span
|json	 	                            |                   |   textual JSON data
|jsonb	 	                            |                   |   binary JSON data, decomposed
|line	 	                            |                   |   infinite line on a plane
|lseg	 	                            |                   |   line segment on a plane
|macaddr	 	                            |                   |   MAC (Media Access Control) address
|macaddr8	 	                        |                   |   MAC (Media Access Control) address |(EUI-64 format)          |
|money	 	                            |                   |   currency amount
|numeric [ (p, s) ]	                    |decimal [ (p, s) ]	|   xact numeric of selectable precision
|path	 	                            |                   |   geometric path on a plane
|pg_lsn	 	                            |                   |   PostgreSQL Log Sequence Number
|pg_snapshot	 	                        |                   |   user-level transaction ID snapshot
|point	 	                            |                   |   geometric point on a plane
|polygon	 	                            |                   |   closed geometric path on a plane
|real	                                |float4	            |   ingle precision floating-point number (4 |bytes)            |
|smallint	                            |int2	            |   igned two-byte integer
|smallserial	                            |serial2	        |      autoincrementing two-byte integer
|serial	                                |serial4	        |      autoincrementing four-byte integer
|text	 	                            |                   |   variable-length character string
|time [ (p) ] [ without time zone ]	 	|                   |   time of day (no time zone)
|time [ (p) ] with time zone	            |timetz	            |   ime of day, including time zone
|timestamp [ (p) ] [ without time zone ]	| 	                |   ate and time (no time zone)
|timestamp [ (p) ] with time zone	    |timestamptz	    |      date and time, including time zone
|tsquery	 	                            |                   |   text search query
|tsvector	 	                        |                   |   text search document
|txid_snapshot	 	                    |                   |   user-level transaction ID snapshot |(deprecated; see pg_snapshot)|
|uuid	 	                            |                   |   universally unique identifier
|xml	 	                                |                   |   XML data



