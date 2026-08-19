CREATE DATABASE online_result_management_system;
USE online_result_management_system;
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins(full_name,email,password)
VALUES
('System Admin','admin@gmail.com','admin123');
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL
);

INSERT INTO departments(department_name)
VALUES
('CSE'),
('EEE'),
('Civil'),
('Mechanical'),
('Architecture');
CREATE TABLE semesters (
    semester_id INT AUTO_INCREMENT PRIMARY KEY,
    semester_name VARCHAR(50) NOT NULL
);

INSERT INTO semesters(semester_name)
VALUES
('1st Semester'),
('2nd Semester'),
('3rd Semester'),
('4th Semester'),
('5th Semester'),
('6th Semester'),
('7th Semester'),
('8th Semester');
CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    department_id INT,
    password VARCHAR(255),
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(department_id)
    REFERENCES departments(department_id)
);
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    student_roll VARCHAR(30) UNIQUE,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    department_id INT,
    semester_id INT,
    password VARCHAR(255),
    status ENUM('Active','Inactive') DEFAULT 'Active',

    FOREIGN KEY(department_id)
    REFERENCES departments(department_id),

    FOREIGN KEY(semester_id)
    REFERENCES semesters(semester_id)
);
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(30),
    course_name VARCHAR(100),
    credit DOUBLE,
    department_id INT,
    semester_id INT,

    FOREIGN KEY(department_id)
    REFERENCES departments(department_id),

    FOREIGN KEY(semester_id)
    REFERENCES semesters(semester_id)
);
INSERT INTO courses
(course_code,course_name,credit,department_id,semester_id)
VALUES
('CSE101','Programming in C',3,1,1),
('CSE201','Data Structures',3,1,3),
('CSE301','Database System',3,1,3),
('CSE401','Operating System',3,1,5);
CREATE TABLE exams (

    exam_id INT AUTO_INCREMENT PRIMARY KEY,

    exam_name VARCHAR(100) NOT NULL,

    department_id INT NOT NULL,

    semester_id INT NOT NULL,

    course_id INT NOT NULL,

    exam_date DATE NOT NULL,

    total_marks INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);
