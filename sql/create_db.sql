-- create_db.sql
-- SAE23 database
-- IUT Blagnac BUT R&T 2026

CREATE DATABASE IF NOT EXISTS sae23;
USE sae23;

-- Building table
CREATE TABLE IF NOT EXISTS Batiment (
    id_batiment INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    adresse VARCHAR(255)
);

-- Room table
CREATE TABLE IF NOT EXISTS Salle (
    id_salle INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    etage INT NOT NULL,
    type VARCHAR(50),
    capacite INT,
    id_batiment INT NOT NULL,
    FOREIGN KEY (id_batiment) REFERENCES Batiment(id_batiment)
);

-- Sensor table
CREATE TABLE IF NOT EXISTS Capteur (
    id_capteur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    type_capteur VARCHAR(50) NOT NULL,
    unite VARCHAR(20),
    id_salle INT NOT NULL,
    FOREIGN KEY (id_salle) REFERENCES Salle(id_salle)
);

-- Measurement table
CREATE TABLE IF NOT EXISTS Mesure (
    id_mesure INT AUTO_INCREMENT PRIMARY KEY,
    valeur FLOAT NOT NULL,
    date_mesure DATE NOT NULL,
    heure_mesure TIME NOT NULL,
    id_capteur INT NOT NULL,
    FOREIGN KEY (id_capteur) REFERENCES Capteur(id_capteur)
);

-- User table
CREATE TABLE IF NOT EXISTS Utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'gestionnaire') NOT NULL,
    id_batiment INT NULL,
    FOREIGN KEY (id_batiment) REFERENCES Batiment(id_batiment)
);

-- Insert buildings
INSERT INTO Batiment (nom, adresse) VALUES
('Batiment A', 'IUT de Blagnac'),
('Batiment B', 'IUT de Blagnac'),
('Batiment C', 'IUT de Blagnac'),
('Batiment E', 'IUT de Blagnac');

-- Insert rooms Building A (id_batiment = 1)
INSERT INTO Salle (nom, etage, type, capacite, id_batiment) VALUES
('Salle-conseil', 1, 'Reunion', 20, 1);

-- Insert rooms Building B (id_batiment = 2)
INSERT INTO Salle (nom, etage, type, capacite, id_batiment) VALUES
('B001', 0, 'TD', 30, 2),
('B101', 1, 'TP', 24, 2),
('B102', 1, 'TP', 24, 2),
('B103', 1, 'TP', 24, 2),
('B105', 1, 'TP', 24, 2),
('B106', 1, 'TP', 24, 2),
('B109', 1, 'TP', 24, 2),
('B110', 1, 'TP', 24, 2),
('B111', 1, 'TP', 24, 2),
('B201', 2, 'TD', 30, 2),
('B202', 2, 'TD', 30, 2),
('B203', 2, 'TD', 30, 2),
('B212', 2, 'TD', 30, 2),
('B234', 2, 'TD', 30, 2),
('Foyer-personnels', 2, 'Commun', 40, 2),
('Foyer-etudiants-entree', 0, 'Commun', 60, 2);

-- Insert rooms Building C (id_batiment = 3)
INSERT INTO Salle (nom, etage, type, capacite, id_batiment) VALUES
('C001', 0, 'CM', 60, 3),
('C002', 0, 'CM', 60, 3),
('C004', 0, 'TD', 30, 3),
('C006', 0, 'TD', 30, 3),
('C101', 1, 'TD', 30, 3),
('C102', 1, 'TD', 30, 3);

-- Insert rooms Building E (id_batiment = 4)
INSERT INTO Salle (nom, etage, type, capacite, id_batiment) VALUES
('E001', 0, 'TD', 30, 4),
('E003', 0, 'TD', 30, 4),
('E006', 0, 'TD', 30, 4),
('E007', 0, 'TP', 24, 4),
('E100', 1, 'TP', 24, 4),
('E104', 1, 'TP', 24, 4),
('E105', 1, 'TP', 24, 4);

-- Insert sensors (4 types per room = 4 x 30 = 120 sensors)
-- Types: temperature, humidity, co2, illumination

INSERT INTO Capteur (nom, type_capteur, unite, id_salle)
SELECT
    CONCAT('AM107-', s.nom, '-temp'), 'temperature', '°C', s.id_salle
FROM Salle s;

INSERT INTO Capteur (nom, type_capteur, unite, id_salle)
SELECT
    CONCAT('AM107-', s.nom, '-hum'), 'humidity', '%', s.id_salle
FROM Salle s;

INSERT INTO Capteur (nom, type_capteur, unite, id_salle)
SELECT
    CONCAT('AM107-', s.nom, '-co2'), 'co2', 'ppm', s.id_salle
FROM Salle s;

INSERT INTO Capteur (nom, type_capteur, unite, id_salle)
SELECT
    CONCAT('AM107-', s.nom, '-lum'), 'illumination', 'lux', s.id_salle
FROM Salle s;




-- Insert user accounts (passwords stored as MD5 hash)
INSERT INTO Utilisateur (login, mot_de_passe, role, id_batiment) VALUES
('admin', MD5('admin'), 'admin', NULL),
('gestionnaire_b', MD5('gestb'), 'gestionnaire', 2),
('gestionnaire_c', MD5('gestc'), 'gestionnaire', 3),
('gestionnaire_e', MD5('geste'), 'gestionnaire', 4);
