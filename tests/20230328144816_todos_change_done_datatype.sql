-- migrate:up
-- put your up migrations here

ALTER TABLE /*prefix*/ todos
DROP COLUMN done; -- modify column type wird von sqlite nicht unterstützt :(

ALTER TABLE /*prefix*/ todos
ADD done boolean;



-- migrate:down
-- can be left empty

ALTER TABLE /*prefix*/ todos
DROP COLUMN done;

ALTER TABLE /*prefix*/ todos
ADD done tinyint;




