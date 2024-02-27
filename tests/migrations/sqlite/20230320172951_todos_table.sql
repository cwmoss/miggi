-- migrate:up
CREATE TABLE IF NOT EXISTS /*prefix*/ todos -- ein inline-kommentar
    (title VARCHAR(50), content TEXT);

-- migrate:down
DROP TABLE IF EXISTS /*prefix*/ todos;