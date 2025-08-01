## adodb

C
: character fields that should be shown in a tag, Add the length of the field C(20)

C2
: Like a C field, but where possible a field that can hold multi-byte (unicode) data is created Add the length of the field C2(20)

X
: TeXt, large text or CLOB fields that should be shown in a textarea

X2
: Like an X field, but where possible creates a field that can hold multi-byte (unicode) data is created

XL
: On systems that support it, creates a large clob field (>32K). This may require additional database configuration. If the database does not support it, a standard clob field is created.

B
: Blobs, or Binary Large Objects. Typically images.

D
: Date (sometimes DateTime) field

T
: Timestamp field

L
: Logical field (boolean or bit-field). Some databases emulate logicals using I2 fields

I
: Integer field, This may be broken down further into I2,I4 or I8 types to represent small,medium and large integers. The largest integer data size is always represented by an I field

N
: Numeric field. Includes autoincrement, numeric, floating point, real and integer. Add the precision and decimal digits N 14.4

R
: Serial field. Includes serial, autoincrement integers. This works for selected databases. Some databases do not signify if a field is auto-increment

## miggi

### types

| code      | type enum    | sqlite                | postgres    | mysql                  |
| --------- | ------------ | --------------------- | ----------- | ---------------------- |
| C(20),c20 | string 20    | TEXT                  | TEXT        | VARCHAR(20)            |
| X         | text         | TEXT                  | TEXT        | TEXT                   |
| XL        | text_xl      | TEXT                  | TEXT        | LONGTEXT               |
| I         | integer      | TEXT                  | TEXT        | INTEGER                |
| I1        | integer 1    | INTEGER               | smallint    | TINYINT                |
| I2        | integer 2    | INTEGER               | smallint    | SMALLINT               |
| I4        | integer 4    | INTEGER               | INTEGER     | INTEGER                |
| I8        | integer 8    | INTEGER               | bigint      | TINYINT                |
| TS        | timestamp    | timestamp             | TIMESTAMP   | TIMESTAMP              |
| T         | datetime     | datetime              | TIMESTAMP   | DATETIME               |
| DTZ       | datetimezone | datetimezone          | timestamptz | DATETIME               |
| D         | date         | DATE                  | DATE        | DATE                   |
| B         | blob         | BLOB                  | BYTEA       | LONGBLOB               |
| F         | float        | REAL                  | FLOAT8      | DOUBLE                 |
| S         | serial       | INTEGER AUTOINCREMENT | SERIAL      | INTEGER AUTO_INCREMENT |
