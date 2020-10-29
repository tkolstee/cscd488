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
  blue-id INTEGER,
  red-id INTEGER,
  FOREIGN KEY (blue-id)
    REFERENCES blueteam (blueID),
  FOREIGN KEY (red-id)
    REFERENCES redteam (redID)
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
  blueID INTEGER,
  pluginID INTEGER,
  qty INTEGER,
  exp-use INTEGER,
  FOREIGN KEY (blueID)
    REFERENCES blueteam (blueID),
  FOREIGN KEY (pluginID)
    REFERENCES plugins (id)
);
CREATE TABLE redteam (
  redID INTEGER PRIMARY KEY AUTOINCREMENT,
  redName TEXT UNIQUE,
  money INTEGER DEFAULT 0,
  reputation INTEGER
);
CREATE TABLE red-inventory (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  redID INTEGER,
  pluginID INTEGER,
  qty INTEGER,
  exp-time TEXT,
  exp-use INTEGER,
  FOREIGN KEY (redID)
    REFERENCES redteam (redID),
  FOREIGN KEY (pluginID)
    REFERENCES plugins (id)
);
CREATE TABLE persistent-access (
  redID INTEGER NOT NULL,
  blueID INTEGER NOT NULL,
  level INTEGER,
  primary key (redID, blueID),
  FOREIGN KEY (redID)
    REFERENCES redteam(redID),
  FOREIGN KEY (blueID)
    REFERENCES blueteam(blueID)
);
CREATE TABLE plugins (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  typeID INTEGER,
  name TEXT UNIQUE,
  description TEXT,
  price INTEGER,
  affect TEXT,
  FOREIGN KEY (typeID)
    REFERENCES plugtypes (id)
); 