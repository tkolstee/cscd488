PRAGMA foreign_keys = ON;

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
  upassword TEXT,
  blueID INTEGER REFERENCES blueteam(blueID),
  redID INTEGER REFERENCES redteam(redID)
);
CREATE TABLE blueteam (
  blueID INTEGER PRIMARY KEY AUTOINCREMENT,
  blueName TEXT UNIQUE,
  leaderID INTEGER UNIQUE,
  revenue INTEGER DEFAULT 0,
  reputation INTEGER,
  available INTEGER
);
CREATE TABLE blueteam (
  blueID INTEGER PRIMARY KEY AUTOINCREMENT,
  blueName TEXT UNIQUE,
  leaderID INTEGER UNIQUE,
  revenue INTEGER DEFAULT 0,
  reputation INTEGER,
  available INTEGER
);
CREATE TABLE blue-inventory (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  blueID INTEGER REFERENCES blueteam(blueID),
  pluginID INTEGER REFERENCES plugins(id),
  qty INTEGER,
  exp-use INTEGER
);
CREATE TABLE redteam (
  redID INTEGER PRIMARY KEY AUTOINCREMENT,
  redName TEXT UNIQUE,
  money INTEGER DEFAULT 0,
  reputation INTEGER
);
CREATE TABLE red-inventory (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  redID INTEGER REFERENCES redteam(redID),
  pluginID INTEGER REFERENCES plugins(id),
  qty INTEGER,
  exp-time TEXT,
  exp-use INTEGER
);
CREATE TABLE persistent-access (
  redID INTEGER NOT NULL REFERENCES redteam(redID),
  blueID INTEGER NOT NULL REFERENCES blueteam(blueID),
  level INTEGER,
  primary key (redID, blueID)
);
CREATE TABLE plugins (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  typeID INTEGER REFERENCES plugtypes(id),
  name TEXT UNIQUE,
  description TEXT,
  price INTEGER,
  affect TEXT
); 
CREATE TABLE plugtypes (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT UNIQUE,
  defensive INTEGER
);
CREATE TABLE prereqs (
  attackID INTEGER REFERENCES attacks(id),
  pluginID INTEGER REFERENCES plugins(id)
  primary key (attackID, pluginID)
);
CREATE TABLE attacks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT UNIQUE,
  minigame INTEGER
);