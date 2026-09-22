-- Tourism Management System Database Schema
-- Run with: sudo mysql tourism_db < schema.sql

CREATE DATABASE IF NOT EXISTS tourism_db;
USE tourism_db;

-- 1. Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Packages table
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    duration VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(500),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    max_travelers INT NULL DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_packages_destination (destination),
    INDEX idx_packages_price (price),
    INDEX idx_packages_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    travel_date DATE NOT NULL,
    number_of_travelers INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') NOT NULL DEFAULT 'pending',
    special_requests TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    INDEX idx_bookings_user_id (user_id),
    INDEX idx_bookings_package_id (package_id),
    INDEX idx_bookings_status (status),
    INDEX idx_bookings_payment_status (payment_status),
    INDEX idx_bookings_travel_date (travel_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_package_review (user_id, package_id),
    INDEX idx_reviews_package (package_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Enquiries table
CREATE TABLE IF NOT EXISTS enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    package_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    admin_reply TEXT NULL,
    status ENUM('unread', 'read', 'replied') NOT NULL DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    INDEX idx_enquiries_status (status),
    INDEX idx_enquiries_created_at (created_at),
    INDEX idx_enquiries_user (user_id),
    INDEX idx_enquiries_package (package_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample Data (Realistic dataset)
INSERT INTO users (name, email, phone, password) VALUES 
('Sarah Mitchell', 'sarah.mitchell@gmail.com', '+1 (555) 234-5678', '$2y$12$6mLibf8qkbafSgO738RoGuX7vNzXuwRcHA6o5QlPIb7FX5iHca4.i'),
('James Richardson', 'j.richardson@yahoo.com', '+1 (555) 345-6789', '$2y$12$6mLibf8qkbafSgO738RoGuX7vNzXuwRcHA6o5QlPIb7FX5iHca4.i'),
('Emma Chen', 'emma.chen@hotmail.com', '+1 (555) 456-7890', '$2y$12$6mLibf8qkbafSgO738RoGuX7vNzXuwRcHA6o5QlPIb7FX5iHca4.i'),
('Michael Kumar', 'michael.kumar@outlook.com', '+1 (555) 567-8901', '$2y$12$6mLibf8qkbafSgO738RoGuX7vNzXuwRcHA6o5QlPIb7FX5iHca4.i'),
('Olivia Thompson', 'olivia.thompson@gmail.com', '+1 (555) 678-9012', '$2y$12$6mLibf8qkbafSgO738RoGuX7vNzXuwRcHA6o5QlPIb7FX5iHca4.i'),
('David Anderson', 'd.anderson@gmail.com', '+1 (555) 789-0123', '$2y$12$6mLibf8qkbafSgO738RoGuX7vNzXuwRcHA6o5QlPIb7FX5iHca4.i'),
('Sophia Martinez', 'sophia.martinez@yahoo.com', '+1 (555) 890-1234', '$2y$12$6mLibf8qkbafSgO738RoGuX7vNzXuwRcHA6o5QlPIb7FX5iHca4.i');

INSERT INTO packages (name, destination, description, image, price, duration, is_active, featured, max_travelers) VALUES 
('Paris City Escape', 'France', 'Experience the magic of Paris with guided tours of the Eiffel Tower, Louvre Museum, and Notre-Dame Cathedral. Enjoy gourmet dining at renowned French restaurants and romantic evening cruises along the Seine River. This package includes airport transfers, 5-star hotel accommodation, and daily breakfast.', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800&q=80', 1299.00, '5 Days / 4 Nights', 1, 1, 20),
('Tokyo Discovery Tour', 'Japan', 'Explore the vibrant streets of Tokyo, visit ancient temples in Kyoto, and experience traditional Japanese culture. This package includes visits to Senso-ji Temple, Meiji Shrine, Shibuya Crossing, and a traditional tea ceremony. Round-trip flights, hotel accommodation, and daily breakfast included.', 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=800&q=80', 1899.00, '7 Days / 6 Nights', 1, 1, 15),
('Bali Beach Resort Vacation', 'Indonesia', 'Relax in paradise at a luxury beach resort in Bali. Enjoy white sand beaches, crystal clear waters, and breathtaking sunsets. This package includes spa treatments, snorkeling adventures in Nusa Penida, visit to Uluwatu Temple, and nightly beachfront dinners.', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800&q=80', 1199.00, '6 Days / 5 Nights', 1, 0, 25),
('Nepal Himalayan Adventure', 'Nepal', 'Embark on an unforgettable trekking adventure through the majestic Himalayas. This package includes Everest View trek, visit to ancient monasteries, cultural immersion with local Sherpa communities, and luxury lodge accommodation. Perfect for adventure seekers and nature lovers.', 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80', 1499.00, '10 Days / 9 Nights', 1, 0, 12),
('California Coastal Road Trip', 'USA', 'Drive along the stunning Pacific Coast Highway from San Francisco to Los Angeles. This package includes stops at Big Sur, Monterey Bay Aquarium, Hearst Castle, Santa Barbara, and Santa Monica Pier. Includes SUV rental, hotel stays, and daily breakfast.', 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=800&q=80', 1399.00, '8 Days / 7 Nights', 1, 0, 18);

INSERT INTO bookings (user_id, package_id, travel_date, number_of_travelers, total_price, status, payment_status, special_requests, booking_date) VALUES
(1, 1, '2026-09-30', 2, 2598.00, 'confirmed', 'paid', 'Window seats on flights and vegetarian meals.', '2026-09-10 09:30:00'),
(2, 2, '2026-10-01', 1, 1899.00, 'pending', 'pending', NULL, '2026-09-11 14:15:00'),
(3, 3, '2026-09-28', 2, 2398.00, 'confirmed', 'paid', 'Honeymoon arrangement with ocean view room.', '2026-09-08 11:00:00'),
(4, 4, '2026-10-02', 1, 1499.00, 'pending', 'pending', NULL, '2026-09-12 16:45:00'),
(5, 5, '2026-09-25', 2, 2798.00, 'confirmed', 'paid', NULL, '2026-09-05 08:20:00'),
(7, 4, '2026-10-05', 1, 1499.00, 'pending', 'pending', NULL, '2026-09-15 10:00:00');

INSERT INTO reviews (user_id, package_id, rating, comment, created_at) VALUES
(1, 1, 5, 'An unforgettable trip! The Seine river cruise and Eiffel Tower dinner were magical. Highly recommend!', '2026-09-15 14:20:00'),
(3, 3, 5, 'The resort was breathtaking, and the snorkeling tour in Nusa Penida was the highlight of our holiday.', '2026-09-12 18:30:00'),
(5, 5, 4, 'Stunning coastal views and great itinerary. Big Sur was absolutely magnificent.', '2026-09-10 11:15:00');

INSERT INTO enquiries (user_id, package_id, name, email, phone, subject, message, status) VALUES 
(1, 1, 'Robert Williams', 'r.williams@company.com', '+1 (555) 312-4567', 'Corporate Group Tour Inquiry', 'We are planning a corporate retreat for 25 employees and would like information about group discounts for the Paris City Escape package. Our preferred dates are October 15-20, 2026.', 'unread'),
(2, 3, 'Jennifer Garcia', 'j.garcia@email.com', '+1 (555) 423-5678', 'Bali Vacation Question', 'My family (2 adults, 2 children aged 8 and 12) is interested in the Bali Beach Resort Vacation. Can you tell us if the package is suitable for children and what the weather is like in June?', 'unread'),
(3, 2, 'Thomas Brown', 't.brown@email.com', '+1 (555) 534-6789', 'Tokyo Tour Availability', 'I would like to book the Tokyo Discovery Tour for next spring. Is the package available in April 2027? Also, do you offer vegetarian meal options?', 'read'),
(4, 4, 'Amanda Wilson', 'amanda.wilson@email.com', '+1 (555) 645-7890', 'Himalayan Trek Difficulty Level', 'I am interested in the Nepal Himalayan Adventure but I have moderate fitness level. Can you provide more details about the trekking difficulty and daily walking distance?', 'read');
