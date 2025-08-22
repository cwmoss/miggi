-- migrate:up
-- put your up migrations here
ALTER TABLE /*prefix*/ todos
ADD deadline date;

-- migrate:down
ALTER TABLE /*prefix*/ todos
DROP COLUMN deadline;

-- can be left empty