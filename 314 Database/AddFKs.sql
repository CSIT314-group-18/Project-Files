ALTER TABLE users
ADD FOREIGN KEY (location_id) REFERENCES location(location_id);

ALTER TABLE admins
ADD FOREIGN KEY (users_id) REFERENCES users(users_id);

ALTER TABLE car
ADD FOREIGN KEY (users_id) REFERENCES users(users_id);

ALTER TABLE location
ADD FOREIGN KEY (users_id) REFERENCES users(users_id);

ALTER TABLE reservation
ADD FOREIGN KEY (renter) REFERENCES users(users_id);
ALTER TABLE reservation
ADD FOREIGN KEY (rentee) REFERENCES users(users_id);
ALTER TABLE reservation
ADD FOREIGN KEY (car_id) REFERENCES car(car_id);

ALTER TABLE message
ADD FOREIGN KEY (sentby) REFERENCES users(users_id);
ALTER TABLE message
ADD FOREIGN KEY (sentto) REFERENCES users(users_id);