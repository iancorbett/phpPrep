## Policy Tracker (Raw PHP + MySQL)

A lightweight CRUD web app built with plain PHP and PDO, designed to demonstrate raw, framework-free development skills — the exact tech stack  small-team PHP shops use daily.

---

<img width="1052" height="697" alt="Screenshot 2025-10-14 at 9 05 52 PM" src="https://github.com/user-attachments/assets/d1655d0e-19c6-459c-b498-7ba9c10d1d4e" />

## Features

Add, edit, and delete insurance policies

Filter and search by client name or status

View active, pending, and expired policy counts

Secure database operations using PDO prepared statements

Clean, responsive UI with custom CSS (no framework bloat)

Fully standalone — no Laravel, no dependencies

---


## Tech Stack

Language	PHP 8+

Database	MySQL

Styling	Custom CSS

Server	PHP built-in server (php -S localhost:8080)

---

## Database Setup

## 1. Create the database

CREATE DATABASE policy_tracker;

USE policy_tracker;


## 2. Create the tables

CREATE TABLE users (
  
  id INT AUTO_INCREMENT PRIMARY KEY,
  
  username VARCHAR(50) NOT NULL UNIQUE,
  
  password_hash VARCHAR(255) NOT NULL,
  
  role ENUM('admin','staff') DEFAULT 'staff',
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

INSERT INTO users (username, password_hash, role)

VALUES ('admin', SHA2('admin123', 256), 'admin');

CREATE TABLE policies (
 
  id INT AUTO_INCREMENT PRIMARY KEY,
  
  client_name VARCHAR(100) NOT NULL,
  
  policy_number VARCHAR(50) NOT NULL UNIQUE,
  
  premium DECIMAL(10,2) NOT NULL DEFAULT 0,
  
  status ENUM('Active','Pending','Expired') NOT NULL DEFAULT 'Pending',
  
  user_id INT DEFAULT NULL,
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL

);


## Optional sample data

INSERT INTO policies (client_name, policy_number, premium, status, user_id) VALUES

('Alice Johnson', 'POL-1001', 120.00, 'Active', 1),

('Beacon Logistics', 'POL-1002', 350.50, 'Pending', 1),

('Carlton & Co', 'POL-1003', 999.99, 'Expired', 1);

---

## How It Works

index.php handles all CRUD logic and rendering

db.php connects to the database using PDO

styles.css defines the clean card-based layout

Each action (add, update, delete) is handled via POST with proper validation
