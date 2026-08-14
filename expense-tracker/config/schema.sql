CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    color VARCHAR(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_expenses_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (name, icon, color) VALUES
    ('Food', CONVERT(_utf8mb4 X'F09F8D94' USING utf8mb4), '#4a4a4a'),
    ('Rent', CONVERT(_utf8mb4 X'F09F8FA0' USING utf8mb4), '#2b2b2b'),
    ('Utilities', CONVERT(_utf8mb4 X'F09F92A1' USING utf8mb4), '#6b6b6b'),
    ('Transportation', CONVERT(_utf8mb4 X'F09F9A97' USING utf8mb4), '#3d3d3d'),
    ('Bills', CONVERT(_utf8mb4 X'F09FA7BE' USING utf8mb4), '#555555'),
    ('Entertainment', CONVERT(_utf8mb4 X'F09F8EAC' USING utf8mb4), '#7a7a7a'),
    ('Savings', CONVERT(_utf8mb4 X'F09F92B0' USING utf8mb4), '#1a7f37'),
    ('Other', CONVERT(_utf8mb4 X'F09F93A6' USING utf8mb4), '#8a8a8a');
