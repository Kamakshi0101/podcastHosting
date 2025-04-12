CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    episode_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (episode_id) REFERENCES episodes(id)
);