-- migrate:up
-- put your up migrations here

ALTER TABLE todos
DROP COLUMN done; -- modify column type wird von sqlite nicht unterstützt :(

ALTER TABLE todos
ADD done boolean;



-- migrate:down
-- can be left empty

ALTER TABLE todos
DROP COLUMN done;

ALTER TABLE todos
ADD done tinyint;




