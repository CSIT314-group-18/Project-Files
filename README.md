# Project-Files
All files for the project

download and use on your own computer

download and install the latest version of XAMPP
(which is a distrobution of apache that works beautifully on windows and comes with php and mysql built in)

Turn apache and mysql on in the xampp gui window (after running xampp as admin)

Go into the admin section of php and create a database 'demo'

Copy the text of the sql file, and paste it in the 'SQL' section of phpmyadmin
to create the sql columns of users and passwords in your db 'demo'

Transfer the early php pages from the repository into the 'htdocs' folder in the installation folder of xampp

Type in your browser 'loaclahost/register.php' and look at the magic work

Creating the database:

0. Assuming you haven't created a database yet: click new, name it "demo" and click "Create".
1. While in database "demo", copy the code in the file SQL.sql into the SQL tab and click "Go". This creates the tables.
2. Copy the code from AddFKs.sql into the SQL tab and click "Go". This adds foreign keys.
3. If for whatever reason you need to delete any tables, the easiest way is to drop the entire database. This is done by going into the SQL tab in "demo" and typing: 
DROP DATABASE demo;
After this refer to step 0 again.
