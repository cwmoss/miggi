
            -- migrate:up
            ALTER TABLE /*prefix*/ todos
            ADD next int;
            
            -- migrate:down
            -- empty
        