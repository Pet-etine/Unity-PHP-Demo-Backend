CREATE TABLE IF NOT EXISTS highscores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    playername VARCHAR(255) NOT NULL,
    hits INT NOT NULL,         -- Stores the number of hits (mandatory)
    accuracy DECIMAL(5, 2),    -- Stores accuracy as a percentage (e.g., 85.50%)
    playtime INT               -- Stores playtime in seconds or minutes
);
