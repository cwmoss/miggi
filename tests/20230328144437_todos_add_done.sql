-- migrate:up
-- put your up migrations here

ALTER TABLE todos
ADD done tinyint;


-- migrate:down
-- can be left empty

ALTER TABLE todos
DROP COLUMN done;