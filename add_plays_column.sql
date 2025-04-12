-- Add plays column to episodes table if it doesn't exist
ALTER TABLE episodes ADD COLUMN IF NOT EXISTS plays INT DEFAULT 0;

-- Make sure the plays table exists
CREATE TABLE IF NOT EXISTS plays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    episode_id INT NOT NULL,
    play_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE,
    INDEX idx_episode_plays (episode_id),
    INDEX idx_user_plays (user_id)
);