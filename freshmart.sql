-- --------------------------------------------------------
-- 1. Administrative User Table
-- Stores login credentials for the system administrator.
-- --------------------------------------------------------
CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 2. Product Categories
-- Groups products (e.g., Fruits, Vegetables) for better organization.
-- --------------------------------------------------------
CREATE TABLE `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 3. Customer Information
-- Stores user profiles, contact details, and encrypted passwords.
-- --------------------------------------------------------
CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 4. Main Orders Table
-- Records the header of a transaction, linked to a specific customer.
-- --------------------------------------------------------
CREATE TABLE `order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `order_status` varchar(50) DEFAULT 'Pending',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 5. Order Details (Line Items)
-- Records the specific products, quantities, and prices for each order.
-- --------------------------------------------------------
CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 6. Product Inventory
-- Manages the stock levels and pricing for all grocery items.
-- --------------------------------------------------------
CREATE TABLE `product` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `min_stock_level` int(11) DEFAULT 5, -- Low stock alert threshold
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 7. Relationships & Constraints (Foreign Keys)
-- Ensures data integrity (e.g., an order cannot exist without a valid customer).
-- --------------------------------------------------------