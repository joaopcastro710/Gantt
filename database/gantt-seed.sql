-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS gantt;
USE gantt;

-- Drop old table if it exists
DROP TABLE IF EXISTS task;

-- Create table
CREATE TABLE task (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    deadline DATETIME NOT NULL
);

-- Insert data
INSERT INTO task (title, start_date, end_date, deadline) VALUES
('Initial', '2025-03-20', '2025-03-25', '2025-03-25'),
('Design', '2025-03-25', '2025-03-30', '2025-03-30'),
('Development', '2025-03-30', '2025-04-10', '2025-04-10'),
('Testing', '2025-04-10', '2025-04-15', '2025-04-15'),
('Deployment', '2025-04-15', '2025-04-20', '2025-04-20');



-- docker exec -it laravel_mysql mysql -u user -p
