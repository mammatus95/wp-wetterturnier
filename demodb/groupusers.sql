CREATE TABLE %table% (
    userID SMALLINT UNSIGNED NOT NULL,
    groupID SMALLINT UNSIGNED NOT NULL,
    since TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    until TIMESTAMP,
    active TINYINT DEFAULT 1,
    PRIMARY KEY (userID, groupID)
);
INSERT INTO %table% (`userID`,`groupID`) VALUES
(1,1),(1,2); 