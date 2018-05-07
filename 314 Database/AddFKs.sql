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

ALTER TABLE message
ADD FOREIGN KEY (sentby) REFERENCES users(users_id)
ON DELETE CASCADE;
ALTER TABLE message
ADD FOREIGN KEY (sentto) REFERENCES users(users_id)
ON DELETE CASCADE;
