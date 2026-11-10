HOSPITAL APPOINTMENT MANAGEMENT SYSTEM
SECURITY AND ACCESS CONTROL MODULE

Assigned module:
Security and Access Control

Features:
- Secure password hashing using password_hash()
- Password verification using password_verify()
- Login authentication
- Session management
- Session ID regeneration after successful login
- Role-based authorization
- Prepared SQL statements
- Email validation
- Logout
- Access-denied protection

INSTALLATION

1. Start Apache and MySQL in XAMPP.
2. Copy this entire folder into:
   C:\xampp\htdocs\hospital_appointment_security

3. Open phpMyAdmin:
   http://localhost/phpmyadmin

4. Import database_setup.sql OR open the SQL tab and run its contents.
   The database must be named:
   appointmentsystem

5. Open:
   http://localhost/hospital_appointment_security/register.php

6. Create a test account.

7. Go to:
   http://localhost/hospital_appointment_security/login.php

8. Login using the account you created.

IMPORTANT
The supplied db_connect.php uses:
Host: localhost
Username: root
Password: empty
Database: appointmentsystem

If your XAMPP MySQL root account has a password, change the password
inside db_connect.php.

TEST ACCOUNT EXAMPLE
Name: Joy Nyabuto
Email: joy@test.com
Password: JoyPassword123
Role: patient

Do not store passwords as plain text. The registration page automatically
creates a secure password hash.

PROJECT FILES
db_connect.php
register.php
login.php
authenticate.php
dashboard.php
logout.php
access_denied.php
security/session.php
database_setup.sql
README.txt
