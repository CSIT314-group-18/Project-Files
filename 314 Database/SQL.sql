CREATE TABLE users (
    users_id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    verifed INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    location_id INT,
	PRIMARY KEY (users_id)
);

CREATE TABLE admins (
    admins_id INT NOT NULL AUTO_INCREMENT,
    users_id INT,
	PRIMARY KEY (admins_id)
);

CREATE TABLE car (
    car_id INT NOT NULL AUTO_INCREMENT,
    image INT NOT NULL,
    model VARCHAR(50) NOT NULL,
    manufacturer VARCHAR(50) NOT NULL,
    transmission VARCHAR(50) NOT NULL,
    odometer INT NOT NULL,
    users_id INT,
	PRIMARY KEY (car_id)
);

CREATE TABLE location (
    location_id INT NOT NULL AUTO_INCREMENT,
    street VARCHAR(50) NOT NULL,
    suburb VARCHAR(50) NOT NULL,
    postcode VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL,
    users_id INT,
	PRIMARY KEY (location_id)
);

CREATE TABLE reservation (
    reservation_id INT NOT NULL AUTO_INCREMENT,
    status VARCHAR(50) NOT NULL,
    startdate DATETIME NOT NULL,
    enddate DATETIME NOT NULL,
    cost DOUBLE NOT NULL,
    fee DOUBLE NOT NULL,
	renter INT,
    rentee INT,
    car_id INT,
	PRIMARY KEY (reservation_id)
);

CREATE TABLE message (
    message_id INT NOT NULL AUTO_INCREMENT,
    content VARCHAR(255) NOT NULL,
    sentby INT,
	sentto INT,
	PRIMARY KEY (message_id)
);