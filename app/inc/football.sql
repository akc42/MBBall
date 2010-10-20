-- 	Copyright (c) 2010 Alan Chandler
--  This file is part of MBBall, an American Football Results Picking
--  Competition Management software suite.
--   MBBall is free software: you can redistribute it and/or modify
--  it under the terms of the GNU General Public License as published by
--  the Free Software Foundation, either version 3 of the License, or
--  (at your option) any later version.
--
--  MBBall is distributed in the hope that it will be useful,
--  but WITHOUT ANY WARRANTY; without even the implied warranty of
--  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
--  GNU General Public License for more details.
--
--  You should have received a copy of the GNU General Public License
--  along with MBBall (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.

BEGIN EXCLUSIVE;

CREATE TABLE config (
	default_competition text DEFAULT '' NOT NULL, -- default competition = name of database without the .db extension
	extn_auth text DEFAULT '' NOT NULL, -- the php path to smf SSI.php
	max_rounds_display integer, --no of rounds to display before starting a new page
	admin_key text DEFAULT '', --global admin must have a key that matches this - [SPECIAL] set to NULL to allow reset
	dversion integer DEFAULT 0 NOT NULL --database version to use when creating a database
);

INSERT INTO config VALUES('','',18,'',1);  -- single record which will hold the data

COMMIT;

-- set it all up as Write Ahead Log for max performance and minimum contention with other users.
PRAGMA journal_mode=WAL;


