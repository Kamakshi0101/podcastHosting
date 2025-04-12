-- Add user_id columns to episodes table
ALTER TABLE episodes
ADD COLUMN user_id INT NOT NULL AFTER id,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Add user_id columns to podcast_schedules table
ALTER TABLE podcast_schedules
ADD COLUMN user_id INT NOT NULL AFTER id,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Add user_id columns to likes table
ALTER TABLE likes
ADD COLUMN user_id INT NOT NULL AFTER id,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Add user_id columns to comments table
ALTER TABLE comments
ADD COLUMN user_id INT NOT NULL AFTER id,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Add user_id columns to analytics table
ALTER TABLE analytics
ADD COLUMN user_id INT NOT NULL AFTER id,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;