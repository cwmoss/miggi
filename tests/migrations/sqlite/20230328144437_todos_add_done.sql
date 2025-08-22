-- migrate:up
-- put your up migrations here

ALTER TABLE /*prefix*/ todos
ADD done integer; -- postgres does not have TINYINT


-- migrate:down
-- can be left empty

ALTER TABLE /*prefix*/ todos
DROP COLUMN done;