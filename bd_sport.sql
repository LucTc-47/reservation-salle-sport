CREATE DATABASE IF NOT EXISTS bd_sport DEFAULT CHARACTER SET utf8mb4;
USE bd_sport;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user'
);

-- Table des salles de sport
CREATE TABLE sports_halls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    description TEXT,
    image VARCHAR(255)
);

-- Table des réservations
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    salle_id INT,
    date_reservation DATE,
    heure_debut TIME,
    duree INT, -- en heures
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salle_id) REFERENCES sports_halls(id) ON DELETE CASCADE
);

-- Ajout d’un compte admin (mot de passe = admin123, hashé)
INSERT INTO users (nom, email, password, role) VALUES 
('Admin', 'admin@sport.com', '$2y$10$8qjQMEAyQwrpSnPQhDwMQeVgInZ11.YrNCrqEGCSzQH7F2JGBNo8e', 'admin');

-- Ajout de salles de sport avec images de test
INSERT INTO sports_halls (nom, description, image) VALUES
('Salle 1', 'Grande salle pour basket, volley, badminton.', 'salle1.jpg'),
('Salle 2', 'Salle de fitness avec équipement moderne.', 'salle2.jpg'),
('Salle 3', 'Salle multifonctions pour yoga et zumba.', 'salle3.jpg');
