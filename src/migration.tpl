-- migrate:up
-- put your up migrations here

CREATE TABLE example (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL UNIQUE CHECK (LENGTH(name) <= 8),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE example
    ADD points INTEGER DEFAULT 0;

--
-- migrate:down
-- can be left empty
--

ALTER TABLE example
    DROP COLUMN points;


