-- ============================================================
--  BICpES Learning Hub — PostgreSQL Schema (Supabase)
--  For production deployment on Vercel + Supabase
-- ============================================================

-- Create database (if not automatic in Supabase)
-- Supabase creates 'postgres' database by default

-- ── USERS ────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    student_number VARCHAR(20) UNIQUE NULL,  -- NULL for admin accounts
    last_name VARCHAR(60) NOT NULL,
    first_name VARCHAR(60) NOT NULL,
    date_of_birth DATE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL DEFAULT 'student' CHECK (role IN ('student', 'admin')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_student_number ON users(student_number);
CREATE INDEX idx_users_role ON users(role);

-- ── TOPICS ───────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS topics (
    id SERIAL PRIMARY KEY,
    topic_num SMALLINT UNIQUE NOT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(80) NOT NULL,
    overview_body TEXT,
    pdf_filename VARCHAR(255),
    activities JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_topics_topic_num ON topics(topic_num);
CREATE INDEX idx_topics_category ON topics(category);

-- ── PROJECTS ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS projects (
    id SERIAL PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    difficulty VARCHAR(20) NOT NULL DEFAULT 'Intermediate' CHECK (difficulty IN ('Beginner', 'Intermediate', 'Advanced')),
    category VARCHAR(80) NOT NULL DEFAULT 'General',
    year SMALLINT NOT NULL DEFAULT 2026,
    hero_tag VARCHAR(120) NOT NULL DEFAULT 'Embedded Systems',
    overview_body TEXT,
    procedure_steps JSONB,
    components_json JSONB,
    video_title VARCHAR(200),
    video_duration VARCHAR(20),
    video_url VARCHAR(500),
    video_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_projects_category ON projects(category);
CREATE INDEX idx_projects_year ON projects(year);
CREATE INDEX idx_projects_difficulty ON projects(difficulty);

-- ── SIMULATION TOOLS ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS simulation_tools (
    id SERIAL PRIMARY KEY,
    tool_name VARCHAR(80) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    url_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_simulation_tools_name ON simulation_tools(tool_name);

-- ── SEED DATA ────────────────────────────────────────────────────────────────

-- Admin account (password: "password" — change immediately after setup)
-- Password hash generated with: password_hash('password', PASSWORD_BCRYPT, ['cost' => 12])
INSERT INTO users (student_number, last_name, first_name, date_of_birth, password_hash, role)
VALUES (
    NULL,
    'Administrator',
    'BICpES',
    '1990-01-01',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
)
ON CONFLICT (student_number) DO NOTHING;

-- Sample topics
INSERT INTO topics (topic_num, name, description, category, overview_body, pdf_filename)
VALUES 
(
    1,
    'Basic Circuit Theory',
    'Fundamentals of electrical circuits, Ohm''s Law, and circuit analysis',
    'Circuits & Electronics',
    'Learn the basics of circuit design|Understanding voltage, current, and resistance|Practical applications in PCB design',
    'Basic_Circuit_Theory.pdf'
),
(
    2,
    'Digital Systems',
    'Logic gates, Boolean algebra, and digital design',
    'Digital Systems',
    'Introduction to digital logic|Building blocks of digital circuits|Designing with TTL and CMOS',
    'Digital_Systems.pdf'
),
(
    3,
    'Embedded Programming',
    'C/C++ for microcontrollers and embedded systems',
    'Embedded & Programming',
    'Introduction to embedded systems|Programming microcontrollers|Real-time programming concepts',
    'Embedded_Programming.pdf'
)
ON CONFLICT (topic_num) DO NOTHING;

-- Sample projects
INSERT INTO projects (title, description, requirements, difficulty, category, year, hero_tag, overview_body, components_json)
VALUES
(
    'Arduino LED Blinker',
    'Learn the basics by controlling an LED with an Arduino',
    'Arduino Uno, LED, 220Ω Resistor, Jumper Wires, USB Cable',
    'Beginner',
    'Embedded',
    2026,
    'Getting Started with Arduino',
    'Your first Arduino project|Controlling digital outputs|Introduction to programming',
    '[{"name":"Arduino Uno","spec":"ATmega328P Microcontroller","qty":1}]'
),
(
    'Temperature Sensor Circuit',
    'Build a temperature monitoring system with a DHT sensor',
    'Arduino/Raspberry Pi, DHT22 Sensor, Resistors, Breadboard',
    'Intermediate',
    'Circuits',
    2026,
    'IoT Sensors',
    'Reading analog values|Sensor calibration|Data logging',
    '[{"name":"DHT22 Sensor","spec":"Temperature & Humidity","qty":1}]'
),
(
    ' 4-Bit Binary Counter',
    'Design and build a binary counter circuit',
    'Logic ICs (74HC193), Breadboard, LEDs, Resistors',
    'Advanced',
    'Digital Systems',
    2026,
    'Digital Logic Design',
    'Counter design|Clock signals|Logic gates in practice',
    '[{"name":"74HC193 IC","spec":"4-Bit Binary Counter","qty":1}]'
)
ON CONFLICT (title) DO NOTHING;

-- Sample simulation tools
INSERT INTO simulation_tools (tool_name, description, url_path)
VALUES
('Tinkercad', 'Web-based circuit simulator and CAD tool', 'tinkercad_view.html'),
('Multisim', 'Professional circuit simulation software', 'multisim_view.html')
ON CONFLICT (tool_name) DO NOTHING;
