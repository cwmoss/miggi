-- migrate:up
CREATE TABLE IF NOT EXISTS todos 
    (title VARCHAR(50), content TEXT);

-- migrate:down
DROP TABLE IF EXISTS todos;

