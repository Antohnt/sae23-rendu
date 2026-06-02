-- create_db.sql
-- SAE23 database creation script

CREATE DATABASE IF NOT EXISTS sae23;
USE sae23;

-- Building table
CREATE TABLE Batiment (
    id_batiment INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    adresse VARCHAR(255)
);

-- Room table
CREATE TABLE Salle (
    id_salle INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    etage INT NOT NULL,
    type VARCHAR(50),
    capacite INT,
    id_batiment INT NOT NULL,
    FOREIGN KEY (id_batiment) REFERENCES Batiment(id_batiment)
);

-- Sensor table
CREATE TABLE Capteur (
    id_capteur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    type_capteur VARCHAR(50) NOT NULL,
    unite VARCHAR(20),
    id_salle INT NOT NULL,
    FOREIGN KEY (id_salle) REFERENCES Salle(id_salle)
);

-- Measurement table
CREATE TABLE Mesure (
    id_mesure INT AUTO_INCREMENT PRIMARY KEY,
    valeur FLOAT NOT NULL,
    date_mesure DATE NOT NULL,
    heure_mesure TIME NOT NULL,
    id_capteur INT NOT NULL,
    FOREIGN KEY (id_capteur) REFERENCES Capteur(id_capteur)
);

-- User table
CREATE TABLE Utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'gestionnaire') NOT NULL,
    id_batiment INT NULL,
    FOREIGN KEY (id_batiment) REFERENCES Batiment(id_batiment)
);

-- Insert buildings
INSERT INTO Batiment (nom, adresse) VALUES
('Batiment E', 'IUT de Blagnac'),
('Batiment C', 'IUT de Blagnac');

-- Insert rooms for Building E
INSERT INTO Salle (nom, etage, type, capacite, id_batiment) VALUES
('E101', 1, 'TP', 24, 1),
('E102', 1, 'TP', 24, 1),
('E103', 1, 'TP', 24, 1),
('E105', 1, 'TD', 30, 1),
('E206', 2, 'TP', 24, 1),
('E207', 2, 'TP', 24, 1),
('E208', 2, 'TD', 30, 1);

-- Insert rooms for Building C
INSERT INTO Salle (nom, etage, type, capacite, id_batiment) VALUES
('C101', 1, 'CM', 60, 2),
('C102', 1, 'TD', 30, 2);

-- Insert sensors for Building E
INSERT INTO Capteur (nom, type_capteur, unite, id_salle) VALUES
('AM107-E101-temp', 'temperature', '°C', 1),
('AM107-E101-co2', 'co2', 'ppm', 1),
('AM107-E208-temp', 'temperature', '°C', 7),
('AM107-E208-hum', 'humidity', '%', 7);

-- Insert sensors for Building C
INSERT INTO Capteur (nom, type_capteur, unite, id_salle) VALUES
('AM107-C101-temp', 'temperature', '°C', 8),
('AM107-C101-co2', 'co2', 'ppm', 8),
('AM107-C102-temp', 'temperature', '°C', 9),
('AM107-C102-hum', 'humidity', '%', 9);

-- Insert user accounts
INSERT INTO Utilisateur (login, mot_de_passe, role, id_batiment) VALUES
('admin', 'admin', 'admin', NULL),
('gestionnaire_e', 'geste', 'gestionnaire', 1),
('gestionnaire_c', 'gestc', 'gestionnaire', 2);
