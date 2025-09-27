-- Run this SQL to add missing columns to the teachers table
ALTER TABLE teachers 
    ADD COLUMN phone VARCHAR(32) DEFAULT NULL,
    ADD COLUMN qualification VARCHAR(128) DEFAULT NULL,
    ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL;