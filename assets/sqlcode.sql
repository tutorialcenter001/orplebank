CREATE DATABASE orple_db;

USE orple_db;

CREATE TABLE staffs (
	id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    middlename VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE KEY,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(15) NOT NULL UNIQUE KEY,
    date_of_birth DATE NOT NULL,
    gender ENUM('male','female','others') DEFAULT 'male',
    home_address TEXT NOT NULL,
    nin VARCHAR(16) NOT NULL UNIQUE KEY,
    bvn VARCHAR(11) NOT NULL UNIQUE KEY,
    mother_maiden_name VARCHAR(50) NOT NULL,
    state_of_origin VARCHAR(255) NOT NULL,
    lga_of_origin VARCHAR(255) NOT NULL,
    delated_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)

ALTER TABLE staffs 
ADD COLUMN profile_picture VARCHAR(255) NULL AFTER bvn, 
ADD COLUMN nationality VARCHAR(255) DEFAULT 'Nigerian' AFTER profile_picture;
ALTER TABLE staffs ADD COLUMN verified_at DATETIME NULL AFTER email; 

CREATE TABLE account_verifications (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    user_type ENUM('staff', 'customer') DEFAULT 'customer',
    code VARCHAR(255) NOT NULL,
    expired_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);