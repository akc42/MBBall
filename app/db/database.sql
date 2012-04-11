
-- 	Copyright (c) 2008,2009,2010 Alan Chandler
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

--
-- Database version 1 (See copy of data to default_competition below) using sqlite
--

BEGIN EXCLUSIVE;

CREATE TABLE competition (
    cid text, --Competition ID - this will be set when created using uniqid() function in php
    description character varying(100),--This is the name that appears in the header for the competition
    condition text,	--This is the text that a user has to agree to in order to register himself for the competition
    administrator integer, --The uid of the administrator
    make_global boolean DEFAULT 0 NOT NULL, --if set make administrator a global admin next time they log on.
    open boolean DEFAULT 0 NOT NULL, --Says whether a user may register for the competion or not
    pp_deadline bigint DEFAULT 0 NOT NULL, --Playoff Selection Deadline 0 if no selection
    gap integer DEFAULT 300 NOT NULL, --Seconds to go before match to make pick deadline
    bb_approval boolean DEFAULT 0 NOT NULL, --Set if BB''s Need Approval after registering to play
    creation_date bigint DEFAULT (strftime('%s','now')) NOT NULL, --Date Competition Created
    dversion integer -- database version of this database
);


CREATE TABLE conference (
    confid character(3) PRIMARY KEY, --Conference 3 letter acronym
    name character varying(30)
);


-- User Pick of each division winner
CREATE TABLE div_winner_pick (
    uid integer NOT NULL REFERENCES participant(uid) ON UPDATE CASCADE ON DELETE CASCADE, --User ID
    confid character(3) NOT NULL REFERENCES conference(confid) ON UPDATE CASCADE ON DELETE CASCADE, --Conference ID
    divid character(1) NOT NULL REFERENCES division(divid) ON UPDATE CASCADE ON DELETE CASCADE, --Division ID
    tid character(3) NOT NULL REFERENCES team(tid) ON UPDATE CASCADE ON DELETE CASCADE, --Team who will win division
    submit_time bigint DEFAULT (strftime('%s','now')) NOT NULL, --Time of submission
    primary key (uid,confid,divid)
);

--Football Conference Division
CREATE TABLE division (
    divid character(1) PRIMARY KEY,
    name character varying(6)
);


CREATE TABLE match (
    rid integer NOT NULL REFERENCES round(rid) ON UPDATE CASCADE ON DELETE CASCADE, --Round ID
    hid character(3) NOT NULL REFERENCES team(tid) ON UPDATE CASCADE ON DELETE CASCADE, -- Home Team ID
    aid character(3) REFERENCES team(tid) ON UPDATE CASCADE ON DELETE SET NULL, --Away Team ID
    comment text, --Administrators Comment for the Match
    ascore integer, --Away Team Score
    hscore integer, --Home Team Score
    combined_score integer, --Value of Combined Score for an over/under question (add 0.5 to this for the question)
    open boolean DEFAULT 0 NOT NULL, --True if Match is set up and ready
    match_time bigint , --Time match is due to be played
    PRIMARY KEY (rid,hid)
);

-- Holds one possible answer to the round question
CREATE TABLE option (
    rid integer NOT NULL REFERENCES round(rid) ON UPDATE CASCADE ON DELETE CASCADE, --Round ID
    opid integer NOT NULL, --Option ID
    label character varying, --Simple Label for this Option
    PRIMARY KEY(rid,opid)
);

CREATE TABLE option_pick (
    uid integer NOT NULL REFERENCES participant(uid) ON UPDATE CASCADE ON DELETE CASCADE, --User ID
    rid integer NOT NULL REFERENCES round(rid) ON UPDATE CASCADE ON DELETE CASCADE, --Round ID
    opid integer NOT NULL , --ID of Question Option Selected as Correct if multichoice, else value of answer (only if multichoice)
    comment text, --General Comment from user about the round
    submit_time bigint DEFAULT (strftime('%s','now')) NOT NULL, --Time of Submission
    PRIMARY KEY (uid,rid)
);

--forum user who will participate in one or more competitions
CREATE TABLE participant (
    uid integer PRIMARY KEY,
    name character varying,
    email character varying,
    password character varying, --stores md5 of password to enable login if cookie lost
    last_logon bigint DEFAULT 0 NOT NULL, --last time user connected
    admin_experience boolean DEFAULT 0 NOT NULL,--Set true if user has ever been administrator
    is_guest boolean DEFAULT false NOT NULL, --user is a guest and will need approving (baby backup from Melinda's Backups)
    agree_time bigint DEFAULT (strftime('%s','now')) , --Time Agreed to Competition Conditions (null if not yet agreed)
    approved boolean DEFAULT 0 NOT NULL --Set if has been approved to play (non guests will be automatically approved)
);

CREATE TABLE pick (
    uid integer NOT NULL REFERENCES participant(uid) ON UPDATE CASCADE ON DELETE CASCADE, --User ID
    rid integer NOT NULL REFERENCES round(rid) ON UPDATE CASCADE ON DELETE CASCADE, --Round ID
    hid character(3) NOT NULL REFERENCES team(tid) ON UPDATE CASCADE ON DELETE CASCADE, -- Home Team ID
    comment text, --Comment on the pick and why it was chosen
    pid character(3), --ID of Team Picked to Win (NULL for Draw)
    over_selected boolean, --true (=1) if over score is selected
    submit_time bigint DEFAULT (strftime('%s','now')) NOT NULL, --Time of submission
    PRIMARY KEY (uid,rid,hid)
);

-- Round in Competition
CREATE TABLE round (
    rid integer PRIMARY KEY, --Round Number
    question text, --Bonus Question Text
    valid_question boolean DEFAULT 0, --Set once a valid bonus question has been set up
    answer integer, --If not null an answer to a numeric question or opid of mutichoice question
    value smallint DEFAULT 1 NOT NULL, --Value given for a correct pick or answer
    name character varying(14), --Name of the Round
    ou_round boolean DEFAULT 0 NOT NULL, --set if over underscores are requested for this round
    deadline bigint, --Time Deadline for submitting answers to bonus questions
    open boolean DEFAULT 0 NOT NULL --says whether round is availble for display
);

CREATE TABLE team (
    tid character(3) PRIMARY KEY,
    name character varying(50) NOT NULL,
    logo character varying(80) DEFAULT NULL,
    url character varying(100) DEFAULT NULL, 
    confid character(3) NOT NULL REFERENCES conference(confid) ON UPDATE CASCADE ON DELETE CASCADE, --Conference ID Team Plays In
    divid character(1) NOT NULL REFERENCES division(divid) ON UPDATE CASCADE ON DELETE CASCADE, --Division ID Team Plays In
    in_competition boolean DEFAULT 0 NOT NULL, --True if team is in competition
    made_playoff boolean DEFAULT 0 NOT NULL --True if team made playoffs
);

--Users Pick of WildCard Entries for each conference
CREATE TABLE wildcard_pick (
    uid integer NOT NULL REFERENCES participant(uid) ON UPDATE CASCADE ON DELETE CASCADE, --User ID
    confid character(3) NOT NULL REFERENCES conference(confid) ON UPDATE CASCADE ON DELETE CASCADE, --Conference ID
    opid smallint DEFAULT 1 NOT NULL, -- Either 1 or 2 depending on which wildcard pick for the conference it is
    tid character(3) NOT NULL REFERENCES team(tid) ON UPDATE CASCADE ON DELETE CASCADE, --Pick
    submit_time bigint DEFAULT (strftime('%s','now')) NOT NULL, --Time of Submission
    PRIMARY KEY(uid,confid,opid)
);

-- END OF TABLES -------------------------------------------------------------------------------------------------

-- START VIEWS ----------------------------------------------------

--points user scored in a match from the pick and over/under question (if present)
CREATE VIEW match_score AS
 SELECT m.rid, m.hid, u.uid, 
        CASE
            WHEN p.uid IS NULL THEN 0
            ELSE 1
        END * r.value AS pscore, 
        CASE
            WHEN o.uid IS NULL THEN 0
            ELSE 1
        END * r.value AS oscore
   FROM participant u
   JOIN match m 
   JOIN round r USING (rid)
   LEFT JOIN pick p ON  p.rid = m.rid AND p.hid = m.hid AND p.uid = u.uid
	AND ((m.hscore >= m.ascore AND p.pid = m.hid) OR (m.hscore <= m.ascore AND p.pid = m.aid))
   LEFT JOIN pick o ON  o.rid = m.rid AND o.hid = m.hid AND o.uid = u.uid AND r.ou_round = 1
 	AND (CAST((m.hscore + m.ascore) AS REAL) > (CAST( m.combined_score AS REAL) + 0.5)) == o.over_selected 
  WHERE r.open = 1 AND m.open = 1;

-- Points scored in round by user answering the bonus question
CREATE VIEW bonus_score AS
    SELECT r.rid, u.uid, (CASE WHEN p.uid IS NULL THEN 0 ELSE 1 END * r.value) AS score
	FROM ((participant u JOIN round r )
	LEFT JOIN option_pick p ON (((((p.rid = r.rid)) AND (p.uid = u.uid) AND (p.opid = r.answer)) AND (r.valid_question = 1))))
	WHERE r.open = 1 ;
	
--used to identify teams a user has picked correctly
CREATE VIEW playoff_picks AS
	SELECT wildcard_pick.tid, wildcard_pick.uid, wildcard_pick.confid
		FROM wildcard_pick
	UNION
	SELECT div_winner_pick.tid, div_winner_pick.uid, div_winner_pick.confid
		FROM div_winner_pick;

-- Score user makes in correctly guessing the playoffs
CREATE VIEW playoff_score AS
	SELECT u.uid, count(p.uid) AS score, p.confid
		FROM participant u
		LEFT JOIN (playoff_picks p JOIN team t ON p.tid = t.tid AND t.made_playoff = 1) AS p
			USING (uid)
		GROUP BY u.uid, p.confid;

--  Get total score for the round by user 
CREATE VIEW round_score AS
SELECT r.rid, r.uid, sum(
        CASE
            WHEN m.pscore IS NULL THEN 0
            ELSE m.pscore
        END) AS pscore, sum(
        CASE
            WHEN m.oscore IS NULL THEN 0
            ELSE m.oscore
        END) AS oscore, sum(
        CASE
            WHEN m.pscore IS NULL THEN 0
            ELSE m.pscore
        END + 
        CASE
            WHEN m.oscore IS NULL THEN 0
            ELSE m.oscore
        END) AS mscore, r.score AS bscore, sum(
        CASE
            WHEN m.pscore IS NULL THEN 0
            ELSE m.pscore
        END + 
        CASE
            WHEN m.oscore IS NULL THEN 0
            ELSE m.oscore
        END) + r.score AS score
   FROM bonus_score r 
   LEFT JOIN match_score m USING (rid, uid)
  GROUP BY r.rid, r.uid, r.score;

-- END OF VIEWS ------------------------------------------------------------------

INSERT INTO conference(confid, name) VALUES ('AFC','American Football Conference');
INSERT INTO conference(confid, name) VALUES ('NFC','National Football Conference');

INSERT INTO division (divid, name)  VALUES ('N','North');
INSERT INTO division (divid, name)  VALUES ('E','East');
INSERT INTO division (divid, name)  VALUES ('S','South');
INSERT INTO division (divid, name)  VALUES ('W','West');

INSERT INTO team (tid, name, logo,  confid, divid) VALUES('NE ','New England Patriots','NE_logo-50x50.gif','AFC','E');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('NYG','New York Giants','NYG_logo-50x50.gif','NFC','E');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('TEN','Tennessee Titans','TEN_logo-50x50.gif','AFC','S');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('IND','Indianapolis Colts','IND_logo-50x50.gif','AFC','S');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('DAL','Dallas Cowboys','DAL_logo-50x50.gif','NFC','E');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('WAS','Washington Redskins','WAS_logo-50x50.gif','NFC','E');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('SEA','Seattle Seahawks','SEA_logo-50x50.gif','NFC','W');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('ATL','Atlanta Falcons','ATL_logo-50x50.gif','NFC','S');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('CIN','Cincinnati Bengals','CIN_logo-50x50.gif','AFC','N');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('MIA','Miami Dolphins','MIA_logo-50x50.gif','AFC','E');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('CAR','Carolina Panthers','CAR_logo-50x50.gif','NFC','S');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('TB ','Tampa Bay Buccaneers','TB_logo-50x50.gif','NFC','S');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('BUF','Buffalo Bills','BUF_logo-50x50.gif','AFC','E');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('PHI','Philadelphia Eagles','PHI_logo-50x50.gif','NFC','E');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('NO ','New Orleans Saints','NO_logo-50x50.gif','NFC','S');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('CHI','Chicago','CHI_logo-50x50.gif','NFC','N');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('JAC','Jacksonville Jaguars','JAC_logo-50x50.gif','AFC','S');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('HOU','Houston Texans','HOU_logo-50x50.gif','AFC','S');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('SF ','San Francisco 49ers','SF_logo-50x50.gif','NFC','W');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('CLE','Cleveland Browns','CLE_logo-50x50.gif','AFC','N');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('PIT','Pittsburgh Steelers','PIT_logo-50x50.gif','AFC','N');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('BAL','Baltimore Ravens','BAL_logo-50x50.gif','AFC','N');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('DET','Detroit Lions','DET_logo-50x50.gif','NFC','N');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('GB ','Green Bay Packers','GB_logo-50x50.gif','NFC','N');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('SD ','San Diego Chargers','SD_logo-50x50.gif','AFC','W');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('OAK','Oakland Raiders','OAK_logo-50x50.gif','AFC','W');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('MIN','Minnesota Vikings','MIN_logo-50x50.gif','NFC','N');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('DEN','Denver Broncos','DEN_logo-50x50.gif','AFC','W');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('STL','St. Louis Rams','STL_logo-50x50.gif','NFC','W');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('NYJ','New York Jets','NYJ_logo-50x50.gif','AFC','E');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('ARI','Arizona Cardinals','ARI_logo-50x50.gif','NFC','W');
INSERT INTO team (tid, name, logo,  confid, divid) VALUES('KC ','Kansas City Chiefs','KC_logo-50x50.gif','AFC','W');

-- END OF DATA ----------------------------------------------------------
-- INDEXES --------------------------------------------------------------

CREATE INDEX div_tid_idx ON div_winner_pick (tid);
CREATE INDEX wild_tid_idx ON wildcard_pick (tid);
CREATE INDEX tid_mp_idx ON team (made_playoff);

CREATE INDEX round_open_idx ON round(open);
CREATE INDEX match_open_idx ON match (open);

CREATE INDEX match_time_idx ON match (match_time);
CREATE INDEX answer_rid_idx ON option_pick (rid);
CREATE INDEX pick_rid_idx ON pick(rid);

-- END OF INDEXES -------------------------------------------------------

COMMIT;
-- set it all up as Write Ahead Log for max performance and minimum contention with other users.
PRAGMA journal_mode=WAL;

