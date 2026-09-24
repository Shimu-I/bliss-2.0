SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  role ENUM('Parent','Admin','Caregiver') NOT NULL,
  password VARCHAR(255) NOT NULL,
  contact_info VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS caregivers (
  user_id INT PRIMARY KEY,
  qualification VARCHAR(255) NOT NULL,
  experience INT NOT NULL DEFAULT 0,
  special_training VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS child (
  child_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  date_of_birth DATE NOT NULL,
  gender ENUM('male','female') NOT NULL,
  medical_history TEXT,
  special_needs ENUM('Yes','No') NOT NULL DEFAULT 'No',
  user_id INT NOT NULL,
  caregiver_id INT DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (caregiver_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS attendance (
  attendance_id INT AUTO_INCREMENT PRIMARY KEY,
  `date` DATE NOT NULL,
  status ENUM('Present','Absent') NOT NULL,
  child_id INT NOT NULL,
  UNIQUE KEY one_per_day (child_id,`date`),
  FOREIGN KEY (child_id) REFERENCES child(child_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS activity (
  activity_id INT AUTO_INCREMENT PRIMARY KEY,
  activity_type VARCHAR(100) NOT NULL,
  description TEXT,
  `date` DATE NOT NULL,
  child_id INT NOT NULL,
  FOREIGN KEY (child_id) REFERENCES child(child_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS learning (
  learning_id INT AUTO_INCREMENT PRIMARY KEY,
  learning_type VARCHAR(100) NOT NULL,
  progress_notes TEXT,
  `date` DATE NOT NULL,
  child_id INT NOT NULL,
  FOREIGN KEY (child_id) REFERENCES child(child_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS meal (
  meal_id INT AUTO_INCREMENT PRIMARY KEY,
  meal_name VARCHAR(100) NOT NULL,
  allergies VARCHAR(255) DEFAULT NULL,
  `date` DATE NOT NULL,
  child_id INT NOT NULL,
  FOREIGN KEY (child_id) REFERENCES child(child_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS pickdrop (
  pickdrop_id INT AUTO_INCREMENT PRIMARY KEY,
  status ENUM('Picked','Dropped') NOT NULL,
  `time` TIME NOT NULL,
  `date` DATE NOT NULL,
  child_id INT NOT NULL,
  FOREIGN KEY (child_id) REFERENCES child(child_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS bill_payment (
  bill_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  section_type VARCHAR(50) NOT NULL,
  section_description VARCHAR(255) DEFAULT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  due_date DATE NOT NULL,
  payment_status ENUM('Pending','Paid') NOT NULL DEFAULT 'Pending',
  paid_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS notification (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  child_id INT DEFAULT NULL,
  message VARCHAR(500) NOT NULL,
  notification_type VARCHAR(50) NOT NULL,
  status ENUM('Unread','Read') NOT NULL DEFAULT 'Unread',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (child_id) REFERENCES child(child_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
