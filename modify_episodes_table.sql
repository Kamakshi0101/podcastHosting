-- Modify episodes table to better handle large files
ALTER TABLE episodes
MODIFY COLUMN file_size BIGINT NOT NULL DEFAULT 0 COMMENT 'Size of the audio file in bytes';