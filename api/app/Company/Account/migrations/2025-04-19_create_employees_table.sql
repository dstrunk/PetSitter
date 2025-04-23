CREATE TABLE employees (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    about TEXT,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50)
);
