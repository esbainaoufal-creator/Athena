USE athena;

--@block
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'member') NOT NULL DEFAULT 'member',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

--@block
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    owner_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_projects_owner
    Foreign Key (owner_id) REFERENCES users(id)
    ON DELETE CASCADE
);

--@block
CREATE TABLE sprints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sprints_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE CASCADE
);

--@block
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sprint_id INT NOT NULL,
    user_id INT,
    title VARCHAR(150) NOT NULL,
    status ENUM('todo', 'in_progress', 'done') NOT NULL DEFAULT 'todo',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_tasks_sprint
    FOREIGN KEY (sprint_id) REFERENCES sprints(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_tasks_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
);

--@block
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_comments_task
    FOREIGN KEY (task_id) REFERENCES tasks(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_comments_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
);
--@block

CREATE TABLE notificatinons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
)ENGINE=InnoDB;

--@block
DROP TABLE IF EXISTS user_tasks;

--@block
DROP TABLE IF EXISTS notifications;

--@block
SET FOREIGN_KEY_CHECKS = 0;

--@block
DROP TABLE IF EXISTS tasks;

--@block
DROP TABLE IF EXISTS sprints;

--@block
DROP TABLE IF EXISTS projects;

--@block
DROP TABLE IF EXISTS users;

--@block
SET FOREIGN_KEY_CHECKS = 1;

--@block
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','member') NOT NULL DEFAULT 'member',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

--@block
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    owner_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_owner
        FOREIGN KEY (owner_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

--@block
CREATE TABLE sprints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sprints_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

--@block
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sprint_id INT NOT NULL,
    user_id INT,
    title VARCHAR(150) NOT NULL,
    status ENUM('todo','in_progress','done') NOT NULL DEFAULT 'todo',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tasks_sprint
        FOREIGN KEY (sprint_id) REFERENCES sprints(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_tasks_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;
