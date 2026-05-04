-- ============================================
-- fix_charset.sql
-- Run this ONCE in phpMyAdmin to fix emoji support
-- Go to phpMyAdmin → socialmini database → SQL tab → paste & run
-- ============================================

USE socialmini;

-- Step 1: Convert the database default charset
ALTER DATABASE socialmini
    CHARACTER SET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

-- Step 2: Convert each table
ALTER TABLE users
    CONVERT TO CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

ALTER TABLE posts
    CONVERT TO CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

ALTER TABLE comments
    CONVERT TO CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

ALTER TABLE likes
    CONVERT TO CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

ALTER TABLE follows
    CONVERT TO CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Done! Your database now fully supports emojis 🎉
