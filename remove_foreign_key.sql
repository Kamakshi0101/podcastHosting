-- Remove the foreign key constraint
ALTER TABLE episodes DROP FOREIGN KEY episodes_ibfk_1;

-- Make podcast_id field optional (allow NULL)
ALTER TABLE episodes MODIFY podcast_id INT NULL;
