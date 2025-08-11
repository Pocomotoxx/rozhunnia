CREATE TABLE IF NOT EXISTS terapiak (
    id INTEGER PRIMARY KEY,
    patient TEXT,
    type TEXT,
    status TEXT
);
CREATE TABLE IF NOT EXISTS gyogyszerek (
    id INTEGER PRIMARY KEY,
    name TEXT,
    stock INTEGER
);
CREATE TABLE IF NOT EXISTS ertesitesek (
    id INTEGER PRIMARY KEY,
    text TEXT,
    urgent INTEGER
);
CREATE TABLE IF NOT EXISTS betegek (
    id TEXT PRIMARY KEY,
    name TEXT,
    medications TEXT,
    diseases TEXT,
    therapies TEXT,
    caregiver TEXT
);
CREATE TABLE IF NOT EXISTS caregivers (
    patient_id TEXT PRIMARY KEY,
    caregiver TEXT
);
CREATE TABLE IF NOT EXISTS vacation (
    user TEXT PRIMARY KEY,
    flag INTEGER
);
