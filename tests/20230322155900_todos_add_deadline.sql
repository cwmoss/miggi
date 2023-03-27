-- migrate:up
-- put your up migrations here
ALTER TABLE todos
ADD deadline date;

-- migrate:down
ALTER TABLE todos
DROP COLUMN deadline;

-- can be left empty