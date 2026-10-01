-- ============================================================
-- EduShare — Online Book & Educational Donation Management System
-- MySQL database schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS edushare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edushare;

-- ------------------------------------------------------------
-- DONORS
-- ------------------------------------------------------------
CREATE TABLE donors (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    phone           VARCHAR(20),
    address         VARCHAR(255),
    location        VARCHAR(100),
    profile_image   VARCHAR(255),
    points          INT DEFAULT 0,
    status          ENUM('active','suspended') DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- INSTITUTIONS (receivers)
-- ------------------------------------------------------------
CREATE TABLE institutions (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    institution_name    VARCHAR(150) NOT NULL,
    institution_type    ENUM('School','Piriven','Educational Organization','Other') NOT NULL,
    reg_number          VARCHAR(100) NOT NULL,
    official_email      VARCHAR(150) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    contact_person      VARCHAR(150),
    contact_number      VARCHAR(20),
    address             VARCHAR(255),
    province            VARCHAR(100),
    district            VARCHAR(100),
    city                VARCHAR(100),
    verification_doc    VARCHAR(255),
    description         TEXT,
    status              ENUM('pending','approved','rejected','info_required') DEFAULT 'pending',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ADMINS
-- ------------------------------------------------------------
CREATE TABLE admins (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CATEGORIES
-- ------------------------------------------------------------
CREATE TABLE categories (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL UNIQUE,
    description     VARCHAR(255)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DONATIONS (listed by donors)
-- ------------------------------------------------------------
CREATE TABLE donations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    donor_id        INT NOT NULL,
    category_id     INT,
    item_name       VARCHAR(150) NOT NULL,
    subject         VARCHAR(100),
    education_level VARCHAR(50),
    quantity        INT NOT NULL DEFAULT 1,
    condition_status ENUM('New','Good','Fair') DEFAULT 'Good',
    description     TEXT,
    image           VARCHAR(255),
    location        VARCHAR(100),
    delivery_pref   ENUM('Donor delivers','Institution collects','Either') DEFAULT 'Either',
    availability_date DATE,
    status          ENUM('available','claimed','completed','cancelled') DEFAULT 'available',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- REQUIREMENTS (posted by institutions)
-- ------------------------------------------------------------
CREATE TABLE requirements (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    institution_id      INT NOT NULL,
    category_id         INT,
    item_name           VARCHAR(150) NOT NULL,
    subject             VARCHAR(100),
    education_level     VARCHAR(50),
    quantity_needed     INT NOT NULL DEFAULT 1,
    quantity_received   INT NOT NULL DEFAULT 0,
    required_date       DATE,
    description         TEXT,
    urgency             ENUM('normal','urgent') DEFAULT 'normal',
    status              ENUM('pending','active','completed','cancelled') DEFAULT 'pending',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CLAIMS (a donation matched/claimed against a requirement)
-- ------------------------------------------------------------
CREATE TABLE claims (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    donation_id         INT NOT NULL,
    requirement_id      INT,
    institution_id      INT NOT NULL,
    donor_id            INT NOT NULL,
    quantity_claimed    INT NOT NULL DEFAULT 1,
    status              ENUM('in_progress','collected','completed','cancelled') DEFAULT 'in_progress',
    claimed_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at        TIMESTAMP NULL,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE,
    FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE SET NULL,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- LEADERBOARD POINTS LEDGER
-- ------------------------------------------------------------
CREATE TABLE points_ledger (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    donor_id        INT NOT NULL,
    points          INT NOT NULL,
    reason          VARCHAR(255),
    awarded_by_admin_id INT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (awarded_by_admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- IMPACT UPDATES (photos published back to donors)
-- ------------------------------------------------------------
CREATE TABLE impact_updates (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    claim_id            INT NOT NULL,
    photo_path          VARCHAR(255) NOT NULL,
    caption              TEXT,
    published_by_admin_id INT,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
    FOREIGN KEY (published_by_admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TRACKING EVENTS (timeline shown on donor-tracking.html / receiver-tracking.html)
-- ------------------------------------------------------------
CREATE TABLE tracking_events (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    claim_id        INT NOT NULL,
    status          ENUM('offered','accepted','preparing','collected','completed','cancelled') NOT NULL,
    note            VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- NOTIFICATIONS
-- ------------------------------------------------------------
CREATE TABLE notifications (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_type       ENUM('donor','institution','admin') NOT NULL,
    user_id         INT NOT NULL,
    title           VARCHAR(150) NOT NULL,
    message         VARCHAR(255) NOT NULL,
    is_read         TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CONTACT MESSAGES (public contact form)
-- ------------------------------------------------------------
CREATE TABLE contact_messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    message         TEXT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- SEED DATA
-- ------------------------------------------------------------
INSERT INTO categories (name, description) VALUES
('Textbooks','School and A/L textbooks'),
('Exercise Books','Blank exercise/note books'),
('Stationery','Pens, pencils, and general stationery'),
('School Bags','Bags and backpacks'),
('Calculators','Scientific and basic calculators'),
('Mathematical Instruments','Geometry sets and instruments'),
('Educational Equipment','Other classroom equipment');

-- Default admin login: admin@edushare.lk / Admin@123  (CHANGE THIS PASSWORD after first login)
INSERT INTO admins (name, email, password_hash) VALUES
('System Admin', 'admin@edushare.lk', '$2b$10$bVcgHlO/jJBGzmtW33F3nOnSdOMMdPx2TWnCtw65PdH.k2Wct8b6a');
