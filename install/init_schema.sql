CREATE TABLE config (
  name  TEXT UNIQUE,
  value TEXT
);
INSERT INTO config (name, value) VALUES ('install_date', strftime('%Y-%m-%d','now'));
CREATE TABLE activity_log (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  timestamp DATETIME,
  message TEXT
);
CREATE TABLE users (
  uid INTEGER PRIMARY KEY AUTOINCREMENT,
  uname TEXT UNIQUE,
  upassword TEXT
);
CREATE TABLE blueteam (
  blueID INTEGER PRIMARY KEY AUTOINCREMENT,
  blueName TEXT UNIQUE,
  leaderID INTEGER UNIQUE
);
CREATE TABLE redteam (
  redID INTEGER PRIMARY KEY AUTOINCREMENT,
  redName TEXT UNIQUE
);
