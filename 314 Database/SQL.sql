CREATE TABLE users (
    users_id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
	fname VARCHAR(255) NOT NULL,
	lname VARCHAR(255) NOT NULL,
	license_number VARCHAR(255) NOT NULL,
	account_suspended INT NOT NULL,
    verifed INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
	facebook VARCHAR(255),
    location_id INT NOT NULL,
	PRIMARY KEY (users_id)
);

CREATE TABLE admins (
    admins_id INT NOT NULL AUTO_INCREMENT,
    users_id INT NOT NULL,
	PRIMARY KEY (admins_id)
);

CREATE TABLE car (
    car_id INT NOT NULL AUTO_INCREMENT,
    image INT NOT NULL,
    model VARCHAR(50) NOT NULL,
    manufacturer VARCHAR(50) NOT NULL,
    transmission VARCHAR(50) NOT NULL,
    odometer INT NOT NULL,
	fee DOUBLE NOT NULL,
	car_status VARCHAR(50) NOT NULL,
    users_id INT NOT NULL,
	PRIMARY KEY (car_id)
);

CREATE TABLE car_rating (
	rating_id INT NOT NULL AUTO_INCREMENT,
	review VARCHAR(255) NOT NULL,
	rating INT NOT NULL,
	car_id INT NOT NULL,
	PRIMARY KEY (rating_id)
); 

CREATE TABLE location (
    location_id INT NOT NULL AUTO_INCREMENT,
    address VARCHAR(255) NOT NULL,
    suburb VARCHAR(50) NOT NULL,
    postcode VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL,
	PRIMARY KEY (location_id)
);

CREATE TABLE reservation (
    reservation_id INT NOT NULL AUTO_INCREMENT,
    reservation_status VARCHAR(50) NOT NULL,
    startdate DATETIME NOT NULL,
    enddate DATETIME NOT NULL,
	owner INT NOT NULL,
    renter INT NOT NULL,
    car_id INT NOT NULL,
	PRIMARY KEY (reservation_id)
);

CREATE TABLE payment (
	payment_id INT NOT NULL AUTO_INCREMENT,
	payment_status VARCHAR(50) NOT NULL,
	total_fee DOUBLE NOT NULL,
	owner INT NOT NULL,
	renter INT NOT NULL,
	PRIMARY KEY (payment_id)
);

CREATE TABLE message (
    message_id INT NOT NULL AUTO_INCREMENT,
    content VARCHAR(255) NOT NULL,
    sentby INT NOT NULL,
	sentto INT NULL,
	PRIMARY KEY (message_id)
);
