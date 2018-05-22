ALTER TABLE users
ADD FOREIGN KEY (location_id) REFERENCES location(location_id)
ON DELETE CASCADE;

ALTER TABLE admins
ADD FOREIGN KEY (users_id) REFERENCES users(users_id)
ON DELETE CASCADE;

ALTER TABLE car
ADD FOREIGN KEY (users_id) REFERENCES users(users_id)
ON DELETE CASCADE;

ALTER TABLE car_rating
ADD FOREIGN KEY (car_id) REFERENCES car(car_id)
ON DELETE CASCADE;

ALTER TABLE reservation
ADD FOREIGN KEY (owner) REFERENCES users(users_id)
ON DELETE CASCADE;
ALTER TABLE reservation
ADD FOREIGN KEY (renter) REFERENCES users(users_id)
ON DELETE CASCADE;
ALTER TABLE reservation
ADD FOREIGN KEY (car_id) REFERENCES car(car_id)
ON DELETE CASCADE;

ALTER TABLE payment
ADD FOREIGN KEY (owner) REFERENCES users(users_id)
ON DELETE CASCADE;
ALTER TABLE payment
ADD FOREIGN KEY (renter) REFERENCES users(users_id)
ON DELETE CASCADE;
ALTER TABLE payment
ADD FOREIGN KEY (reservation_id) REFERENCES reservation(reservation_id)
ON DELETE CASCADE;

ALTER TABLE message
ADD FOREIGN KEY (sentby) REFERENCES users(users_id)
ON DELETE CASCADE;
ALTER TABLE message
ADD FOREIGN KEY (sentto) REFERENCES users(users_id)
ON DELETE CASCADE;

INSERT INTO location (location_id, street, suburb, postcode, city, country, users_id) VALUES  (1, "", "", "", "", "", 1);
INSERT INTO users (users_id, username, password, fname, lname, license_number, location_id, verifed) VALUES (1, "root", "$2y$10$sKfiEapIjE/sJZtQjUHfoOv2Rn1x5sTBN5NgBeKbhwmVSu7gWSka2", "system", "@ root", "", 1, 1);
INSERT INTO admins (users_id) VALUES (1);

INSERT INTO location (street, suburb, postcode, city, country, users_id) VALUES  ("1 Small Street", "Figtree", "2500", "Wollongong", "Australia", 2);
INSERT INTO users (username, password, fname, lname, license_number, location_id, verifed) VALUES ("lewis", "$2y$10$sKfiEapIjE/sJZtQjUHfoOv2Rn1x5sTBN5NgBeKbhwmVSu7gWSka2", "Lewis", "Torrington", "2546533", 2, 1);