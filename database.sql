CREATE DATABASE IF NOT EXISTS socialmini;
USE socialmini;

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    username    VARCHAR(50)   NOT NULL UNIQUE,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    bio         TEXT,
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    profile_pic VARCHAR(255)  NULL
);

CREATE TABLE IF NOT EXISTS posts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT           NOT NULL,
    content     TEXT          NOT NULL,
    image       VARCHAR(255)  DEFAULT NULL,
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS comments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    post_id     INT           NOT NULL,
    user_id     INT           NOT NULL,
    content     TEXT          NOT NULL,
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS likes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    post_id     INT           NOT NULL,
    user_id     INT           NOT NULL,
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS follows (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    follower_id  INT           NOT NULL,
    following_id INT           NOT NULL,
    UNIQUE KEY unique_follow (follower_id, following_id),
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
);


-- Password for all sample users: "password123"
-- (bcrypt hash of "password123")
INSERT INTO users (name, username, email, password, bio) VALUES
('Alice Johnson', 'alice', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hello! I love photography 📷'),
('Bob Smith',    'bob',   'bob@example.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Traveler & coder 🌍'),
('Carol White',  'carol', 'carol@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Coffee addict ☕');

INSERT INTO posts (user_id, content) VALUES
(1, 'Just started using SocialMini! Excited to connect with everyone 🎉'),
(2, 'Beautiful sunset today. Nothing beats nature 🌅'),
(1, 'Working on a new photography project. Stay tuned!'),
(3, 'Good morning everyone! Coffee in hand, ready for the day ☕');

INSERT INTO follows (follower_id, following_id) VALUES
(1, 2), (1, 3), (2, 1), (3, 1), (3, 2);

INSERT INTO likes (post_id, user_id) VALUES
(1, 2), (1, 3), (2, 1), (3, 2), (4, 1);

INSERT INTO comments (post_id, user_id, content) VALUES
(1, 2, 'Welcome! Great to have you here 😊'),
(1, 3, 'Yay! Let us connect!'),
(2, 1, 'Stunning photo! Where was this?'),
(4, 2, 'Same! Morning coffee is life.');
