CREATE OR REPLACE FUNCTION link_engine( varchar, varchar, varchar, varchar ) returns varchar AS $$

SELECT 'link_engine(' || $1 || '||' || $2 || '||' || COALESCE($3,'NULL') || '||' || $4 || ')';

$$ LANGUAGE sql STABLE;

