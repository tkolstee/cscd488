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
  uname TEXT UNIQUE NOT NULL,
  upassword TEXT NOT NULL,
  blueID INTEGER REFERENCES blueteam(blueID),
  redID INTEGER REFERENCES redteam(redID)
);
CREATE TABLE blueteam (
  blueID INTEGER PRIMARY KEY AUTOINCREMENT,
  blueName TEXT UNIQUE NOT NULL,
  leaderID INTEGER UNIQUE NOT NULL,
  revenue INTEGER DEFAULT 0,
  reputation INTEGER,
  available INTEGER
);
CREATE TABLE blue-inventory (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  blueID INTEGER NOT NULL REFERENCES blueteam(blueID),
  pluginID INTEGER NOT NULL REFERENCES plugins(id),
  qty INTEGER,
  exp_use INTEGER
);
CREATE TABLE redteam (
  redID INTEGER PRIMARY KEY AUTOINCREMENT,
  redName TEXT NOT NULL UNIQUE,
  money INTEGER NOT NULL DEFAULT 0,
  reputation INTEGER
);
CREATE TABLE red_inventory (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  redID INTEGER NOT NULL REFERENCES redteam(redID),
  pluginID INTEGER NOT NULL REFERENCES plugins(id),
  qty INTEGER,
  exp_time TEXT,
  exp_use INTEGER
);
CREATE TABLE persistent_access (
  redID INTEGER NOT NULL REFERENCES redteam(redID),
  blueID INTEGER NOT NULL REFERENCES blueteam(blueID),
  level INTEGER,
  primary key (redID, blueID)
);
CREATE TABLE plugins (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  typeID INTEGER NOT NULL REFERENCES plugtypes(id),
  name TEXT NOT NULL UNIQUE,
  description NOT NULL TEXT,
  price NOT NULL INTEGER DEFAULT 0,
  affect TEXT
); 
CREATE TABLE plugtypes (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL UNIQUE,
  defensive INTEGER
);
CREATE TABLE prereqs (
  attackID INTEGER REFERENCES attacks(id),
  pluginID INTEGER REFERENCES plugins(id),
  primary key (attackID, pluginID)
);
CREATE TABLE attacks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL UNIQUE,
  minigame INTEGER
);
INSERT INTO redteam (redName) VALUES ("test");
INSERT INTO blueteam (blueName) VALUES ("test");