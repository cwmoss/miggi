-- migrate:up
-- put your up migrations here

ALTER TABLE todos
ADD priority integer;


-- migrate:down
-- can be left empty


ALTER TABLE todos
DROP COLUMN priority;


