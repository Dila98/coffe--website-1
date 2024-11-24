-- Drop and recreate database
DROP DATABASE IF EXISTS coffee_shop;
CREATE DATABASE coffee_shop;
USE coffee_shop;

-- Create tables
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,  -- Added username field
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255),
    category_id INT,
    is_available BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- Insert sample data
INSERT INTO categories (name) VALUES 
('Hot Coffee'),
('Cold Coffee'),
('Desserts');


-- Insert new menu items
INSERT INTO menu_items (name, description, price, image_path, category_id) VALUES 
-- Hot Coffees (category_id = 1)
('Espresso', 'Pure and intense shot of coffee, perfectly extracted for maximum flavor and aroma. A classic choice for coffee purists.', 1290.00, 'espresso.jpeg', 1),

('Cappuccino', 'Classic Italian coffee with equal parts espresso, steamed milk, and silky milk foam. Perfect balance of strength and smoothness.', 1590.00, 'cappuccino.webp', 1),

('Latte', 'Smooth espresso with steamed milk and a light layer of milk foam. Our most popular drink for its creamy, balanced flavor.', 1590.00, 'Latte.jpg', 1),

('Americano', 'Espresso shots diluted with hot water, delivering a rich coffee flavor similar to drip coffee. Bold and straightforward.', 1290.00, 'Americano.webp', 1),

('Irish Coffee', 'Premium coffee blend with Irish whiskey, topped with lightly whipped cream and a dusting of cocoa. A perfect after-dinner treat.', 2590.00, 'Irish Coffee.jpeg', 1),

('Caramel Macchiato', 'Freshly steamed milk with vanilla-flavored syrup, marked with espresso and topped with caramel drizzle. A perfect balance of sweetness and coffee.', 1890.00, 'Macchiato.jpg', 1),

-- Cold Coffees (category_id = 2)
('Iced Mocha', 'Chilled espresso with chocolate syrup and cold milk, topped with whipped cream and chocolate drizzle. A refreshing chocolate-coffee indulgence.', 1890.00, 'Iced Mocha.jpeg', 2),

('Cold Brew', 'Smooth and rich coffee slowly steeped in cold water for 12 hours, served over ice. Less acidic with a naturally sweet taste.', 1590.00, 'Cold Brew .jpeg', 2),

('Mocha Frappuccino', 'Blended coffee with rich mocha sauce, milk and ice, topped with whipped cream and chocolate drizzle. A chocolate lovers dream.', 2090.00, 'Frappuccino.jpeg', 2);


INSERT INTO users (
    name,
    username,
    email,
    password,
    is_admin
) VALUES (
    'Admin User',
    'admin',
    'admin@coffee.com',
    '$2y$10$8K1p/bFhF0TuWX8H2XQ6/.WX.GGH.j4aaC5bZjh6QVcXTOc1jKEdi',
    TRUE
);
