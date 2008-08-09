COMMENT ON DATABASE melindas_ball IS 'Melindas Backups Football Pool Competitions Database';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;


CREATE TABLE competition (
    description character varying(100),
    condition text,
    administrator integer,
    open boolean DEFAULT false NOT NULL,
    cid integer NOT NULL,
    pp_deadline bigint DEFAULT 0 NOT NULL,
    gap bigint DEFAULT 3600 NOT NULL,
    bb_approval boolean DEFAULT false NOT NULL,
    creation_date date DEFAULT now() NOT NULL
);

COMMENT ON COLUMN competition.description IS 'This is the name that appears in the header for the competition';
COMMENT ON COLUMN competition.condition IS 'This is the text that a user has to agree to in order to register himself for the competition';
COMMENT ON COLUMN competition.administrator IS 'The uid of the administrator';
COMMENT ON COLUMN competition.open IS 'Says whether a user may register for the competion or not';
COMMENT ON COLUMN competition.cid IS 'Competition ID';
COMMENT ON COLUMN competition.pp_deadline IS 'Playoff Selection Deadline 0 if no selection';
COMMENT ON COLUMN competition.gap IS 'Seconds to go before match to make pick deadline';
COMMENT ON COLUMN competition.bb_approval IS 'Set if BB''s Need Approval after registering to play';
COMMENT ON COLUMN competition.creation_date IS 'Date Competition Created';

CREATE TABLE conference (
    confid character(3) NOT NULL,
    name character varying(30)
);

COMMENT ON COLUMN conference.confid IS 'Conference 3 letter acronym';

CREATE TABLE default_competition (
    cid integer,
    version character varying
);

COMMENT ON TABLE default_competition IS 'This will have a single row containing the key of the default competition';
COMMENT ON COLUMN default_competition.version IS 'Version no of system';

CREATE TABLE div_winner_pick (
    cid integer NOT NULL,
    confid character(3) NOT NULL,
    divid character(1) NOT NULL,
    tid character(3),
    uid integer NOT NULL,
    submit_time bigint
);

COMMENT ON TABLE div_winner_pick IS 'User Pick of each division winner';
COMMENT ON COLUMN div_winner_pick.cid IS 'Conference ID';
COMMENT ON COLUMN div_winner_pick.confid IS 'Conference ID';
COMMENT ON COLUMN div_winner_pick.divid IS 'Division ID';
COMMENT ON COLUMN div_winner_pick.tid IS 'Team who will win division';
COMMENT ON COLUMN div_winner_pick.uid IS 'User ID';
COMMENT ON COLUMN div_winner_pick.submit_time IS 'Time of submission';

CREATE TABLE division (
    divid character(1) NOT NULL,
    name character varying(6)
);

COMMENT ON TABLE division IS 'Football Conference Division';

CREATE TABLE match (
    rid integer NOT NULL,
    hid character(3) NOT NULL,
    aid character(3) NOT NULL,
    comment text,
    ascore integer,
    hscore integer,
    cid integer NOT NULL,
    combined_score integer,
    open boolean DEFAULT false NOT NULL,
    match_time bigint
);

COMMENT ON COLUMN match.rid IS 'round id';
COMMENT ON COLUMN match.hid IS 'home team id';
COMMENT ON COLUMN match.aid IS 'Away Team ID';
COMMENT ON COLUMN match.comment IS 'Administrator Comment for this Match';
COMMENT ON COLUMN match.ascore IS 'Away Team Score';
COMMENT ON COLUMN match.hscore IS 'Home Team Score';
COMMENT ON COLUMN match.cid IS 'Competition ID';
COMMENT ON COLUMN match.combined_score IS 'Value of Combined Score for an over/under question (add 0.5 to this for the question)';
COMMENT ON COLUMN match.open IS 'True if Match is set up and ready';
COMMENT ON COLUMN match.match_time IS 'Time match is due to be played';

CREATE TABLE option_pick (
    uid integer NOT NULL,
    comment text,
    cid integer NOT NULL,
    rid integer NOT NULL,
    opid integer,
    submit_time bigint
);


--
-- Name: COLUMN option_pick.uid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option_pick.uid IS 'User ID';


--
-- Name: COLUMN option_pick.comment; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option_pick.comment IS 'General Comment from user about the round';


--
-- Name: COLUMN option_pick.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option_pick.cid IS 'Competition ID';


--
-- Name: COLUMN option_pick.rid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option_pick.rid IS 'Round ID';


--
-- Name: COLUMN option_pick.opid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option_pick.opid IS 'ID of Question Option Selected as Correct if multichoice, else value of answer (only if multichoice)';


--
-- Name: COLUMN option_pick.submit_time; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option_pick.submit_time IS 'Time of Submission';

--
-- Name: pick; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE pick (
    uid integer NOT NULL,
    comment text,
    cid integer NOT NULL,
    rid integer NOT NULL,
    hid character(3) NOT NULL,
    pid character(3),
    over boolean,
    submit_time bigint
);



--
-- Name: COLUMN pick.uid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN pick.uid IS 'User ID';


--
-- Name: COLUMN pick.comment; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN pick.comment IS 'Comment on the pick and why it was chosen';


--
-- Name: COLUMN pick.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN pick.cid IS 'Competition ID';


--
-- Name: COLUMN pick.rid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN pick.rid IS 'Round ID';


--
-- Name: COLUMN pick.hid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN pick.hid IS 'Home Team ID';


--
-- Name: COLUMN pick.pid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN pick.pid IS 'ID of Team Picked to Win (NULL for Draw)';


--
-- Name: COLUMN pick.over; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN pick.over IS 'true if over score is selected';


--
-- Name: COLUMN pick.submit_time; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN pick.submit_time IS 'Time of submission';

--
-- Name: registration; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE registration (
    uid integer NOT NULL,
    cid integer NOT NULL,
    agree_time bigint,
    bb_approved boolean DEFAULT false NOT NULL
);


--
-- Name: TABLE registration; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE registration IS 'Record of users registerd with a competition';


--
-- Name: COLUMN registration.uid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN registration.uid IS 'User ID';


--
-- Name: COLUMN registration.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN registration.cid IS 'Competition ID';


--
-- Name: COLUMN registration.agree_time; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN registration.agree_time IS 'Time Agreed to Competition Conditions';


--
-- Name: COLUMN registration.bb_approved; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN registration.bb_approved IS 'Set if BB has been approved to play';


--
-- Name: round; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE round (
    rid integer NOT NULL,
    cid integer NOT NULL,
    question text,
    valid_question boolean DEFAULT false,
    answer integer,
    value smallint DEFAULT 1 NOT NULL,
    name character varying(14),
    ou_round boolean DEFAULT false NOT NULL,
    deadline bigint
);


--
-- Name: TABLE round; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE round IS 'Round in Competition';


--
-- Name: COLUMN round.rid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.rid IS 'Round Number';


--
-- Name: COLUMN round.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.cid IS 'Competition ID';


--
-- Name: COLUMN round.question; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.question IS 'Bonus Question Text';


--
-- Name: COLUMN round.valid_question; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.valid_question IS 'Set once a valid bonus question has been set up';


--
-- Name: COLUMN round.answer; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.answer IS 'If not null an answer to a numeric question or opid of mutichoice question';


--
-- Name: COLUMN round.value; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.value IS 'Value given for a correct pick or answer';


--
-- Name: COLUMN round.name; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.name IS 'Name of the Round';


--
-- Name: COLUMN round.ou_round; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.ou_round IS 'set if over underscores are requested for this round';


--
-- Name: COLUMN round.deadline; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.deadline IS 'Deadline for submitting answers to bonus questions';




--
-- Name: match_score; Type: VIEW; Schema: public; Owner: alan
--

CREATE VIEW match_score AS
    SELECT m.cid, m.rid, m.hid, u.uid, ((((p.uid IS NOT NULL))::integer + ((o.uid IS NOT NULL))::integer) * r.value) AS score FROM ((((registration u JOIN match m USING (cid)) JOIN round r USING (cid, rid)) LEFT JOIN pick p ON ((((((p.cid = m.cid) AND (p.rid = m.rid)) AND (p.hid = m.hid)) AND (p.uid = u.uid)) AND (((m.hscore >= m.ascore) AND (p.pid = m.hid)) OR ((m.hscore <= m.ascore) AND (p.pid = m.aid)))))) LEFT JOIN pick o ON (((((((o.cid = m.cid) AND (o.rid = m.rid)) AND (o.hid = m.hid)) AND (o.uid = u.uid)) AND (r.ou_round IS TRUE)) AND (((((m.hscore + m.ascore))::numeric > ((m.combined_score)::numeric + 0.5)) AND (o.over IS TRUE)) OR ((((m.hscore + m.ascore))::numeric < ((m.combined_score)::numeric + 0.5)) AND (o.over IS NOT TRUE)))))) WHERE (m.open IS TRUE);



--
-- Name: VIEW match_score; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON VIEW match_score IS 'points user scored in a match from the pick and over/under question (if present)';


--
-- Name: option; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE option (
    cid integer NOT NULL,
    rid integer NOT NULL,
    opid integer NOT NULL,
    label character varying(14)
);



--
-- Name: TABLE option; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE option IS 'Holds one possible answer to the round question';


--
-- Name: COLUMN option.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option.cid IS 'Competition ID';


--
-- Name: COLUMN option.rid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option.rid IS 'Round ID';


--
-- Name: COLUMN option.opid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option.opid IS 'Option ID';


--
-- Name: COLUMN option.label; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option.label IS 'Simple Label for this option';


--
-- Name: participant; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE participant (
    uid integer NOT NULL,
    name character varying,
    email character varying,
    last_logon date DEFAULT now() NOT NULL,
    admin_experience boolean DEFAULT false NOT NULL,
    is_bb boolean DEFAULT false NOT NULL
);



--
-- Name: TABLE participant; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE participant IS 'forum user who will participate in one or more competitions';


--
-- Name: COLUMN participant.last_logon; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN participant.last_logon IS 'last time user connected';


--
-- Name: COLUMN participant.admin_experience; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN participant.admin_experience IS 'Set true if user has ever been administrator';


--
-- Name: COLUMN participant.is_bb; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN participant.is_bb IS 'user is a baby backup';



--
-- Name: team; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE team (
    tid character(3) NOT NULL,
    name character varying(50) NOT NULL,
    logo character varying(80) DEFAULT NULL,
    url character varying(100) DEFAULT NULL, 
    confid character(3),
    divid character(1)
);



--
-- Name: COLUMN team.confid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN team.confid IS 'Conference Team Plays in ';


--
-- Name: COLUMN team.divid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN team.divid IS 'division team plays in';



--
-- Name: team_in_competition; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE team_in_competition (
    tid character(3) NOT NULL,
    cid integer NOT NULL,
    made_playoff boolean DEFAULT false NOT NULL
);



--
-- Name: COLUMN team_in_competition.tid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN team_in_competition.tid IS 'Team ID';


--
-- Name: COLUMN team_in_competition.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN team_in_competition.cid IS 'Competition ID';


--
-- Name: COLUMN team_in_competition.made_playoff; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN team_in_competition.made_playoff IS 'True if team made playoffs';

--
-- Name: wildcard_pick; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE wildcard_pick (
    cid integer NOT NULL,
    confid character(3) NOT NULL,
    uid integer NOT NULL,
    tid character(3),
    submit_time bigint,
    opid smallint DEFAULT 1 NOT NULL
);



--
-- Name: TABLE wildcard_pick; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE wildcard_pick IS 'Users Pick of WildCard Entries for each conference';


--
-- Name: COLUMN wildcard_pick.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN wildcard_pick.cid IS 'Competition ID';


--
-- Name: COLUMN wildcard_pick.confid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN wildcard_pick.confid IS 'Conference ID';


--
-- Name: COLUMN wildcard_pick.uid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN wildcard_pick.uid IS 'User ID';


--
-- Name: COLUMN wildcard_pick.tid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN wildcard_pick.tid IS 'Pick';


--
-- Name: COLUMN wildcard_pick.submit_time; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN wildcard_pick.submit_time IS 'Time of Submission';


--
-- Name: COLUMN wildcard_pick.opid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN wildcard_pick.opid IS 'Either 1 or 2 depending on which wildcard pick for the conference it is';


-- END OF TABLES -------------------------------------------------------------------------------------------------

--
-- Name: match_score; Type: VIEW; Schema: public; Owner: alan
--

CREATE VIEW match_score AS
    SELECT m.cid, m.rid, m.hid, u.uid, ((CASE p.uid WHEN NULL THEN 0 ELSE 1 END + CASE o.uid WHEN NULL THEN 0 ELSE 1 END) * r.value) AS score FROM ((((registration u JOIN match m USING (cid)) JOIN round r USING (cid, rid)) LEFT JOIN pick p ON ((((((p.cid = m.cid) AND (p.rid = m.rid)) AND (p.hid = m.hid)) AND (p.uid = u.uid)) AND (((m.hscore >= m.ascore) AND (p.pid = m.hid)) OR ((m.hscore <= m.ascore) AND (p.pid = m.aid)))))) LEFT JOIN pick o ON (((((((o.cid = m.cid) AND (o.rid = m.rid)) AND (o.hid = m.hid)) AND (o.uid = u.uid)) AND (r.ou_round IS TRUE)) AND (((((m.hscore + m.ascore))::numeric > ((m.combined_score)::numeric + 0.5)) AND (o.over IS TRUE)) OR ((((m.hscore + m.ascore))::numeric < ((m.combined_score)::numeric + 0.5)) AND (o.over IS NOT TRUE)))))) WHERE (m.open IS TRUE);



--
-- Name: VIEW match_score; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON VIEW match_score IS 'points user scored in a match from the pick and over/under question (if present)';


--
-- Name: bonus_score; Type: VIEW; Schema: public; Owner: alan
--

CREATE VIEW bonus_score AS
    SELECT r.cid, r.rid, u.uid, (CASE p.uid WHEN NULL THEN 0 ELSE 1 END * r.value) AS score FROM ((registration u JOIN round r USING (cid)) LEFT JOIN option_pick p ON (((((p.cid = r.cid) AND (p.rid = r.rid)) AND (p.opid = r.answer)) AND (r.valid_question IS TRUE))));



--
-- Name: VIEW bonus_score; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON VIEW bonus_score IS 'Points scored in round by user answering the bonus question';


--
-- Name: playoff_picks; Type: VIEW; Schema: public; Owner: alan
--

CREATE VIEW playoff_picks AS
    SELECT wildcard_pick.cid, wildcard_pick.tid, wildcard_pick.uid FROM wildcard_pick UNION SELECT div_winner_pick.cid, div_winner_pick.tid, div_winner_pick.uid FROM div_winner_pick;



--
-- Name: VIEW playoff_picks; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON VIEW playoff_picks IS 'used to identify teams a user has picked correctly';



--
-- Name: playoff_score; Type: VIEW; Schema: public; Owner: alan
--

CREATE VIEW playoff_score AS
    SELECT u.cid, u.uid, count(p.uid) AS score FROM (registration u LEFT JOIN (playoff_picks p JOIN team_in_competition t ON ((((p.cid = t.cid) AND (p.tid = t.tid)) AND (t.made_playoff IS TRUE)))) ON (((p.cid = u.cid) AND (p.uid = u.uid)))) GROUP BY u.cid, u.uid;



--
-- Name: VIEW playoff_score; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON VIEW playoff_score IS 'Score user makes in correctly guessing the playoffs';


--
-- Name: round_score; Type: VIEW; Schema: public; Owner: alan
--

CREATE VIEW round_score AS
    SELECT r.cid, r.rid, r.uid, sum(m.score) AS mscore, r.score AS bscore, (sum(m.score) + r.score) AS score FROM (match_score m RIGHT JOIN bonus_score r USING (cid, rid, uid)) GROUP BY r.cid, r.rid, r.uid, r.score;



--
-- Name: VIEW round_score; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON VIEW round_score IS 'Get total score for the round by user';

-- END OF VIEWS ------------------------------------------------------------------

--
-- Name: competition_cid_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE competition_cid_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: competition_cid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--


--
-- Name: competition_cid_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT setval('competition_cid_seq', 1, true);


--
-- Name: cid; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE competition ALTER COLUMN cid SET DEFAULT nextval('competition_cid_seq');

-- END OF SEQUENCE ------------------------------------------------------------------
--
-- Data for Name: conference; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY conference (confid, name) FROM stdin;
AFC	American Football Conference
NFC	National Football Conference
\.


--
-- Data for Name: default_competition; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY default_competition (cid, version) FROM stdin;
\N	v0.1
\.



--
-- Data for Name: division; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY division (divid, name) FROM stdin;
N	North
E	East
S	South
W	West
\.


--
-- Data for Name: team; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY team (tid, name, logo, url, confid, divid) FROM stdin;
NE 	New England Patriots	NE_logo-50x50.gif		AFC	E
NYG	New York Giants	NYG_logo-50x50.gif		NFC	E
TEN	Tennessee Titans	TEN_logo-50x50.gif		AFC	S
IND	Indianapolis Colts	IND_logo-50x50.gif		AFC	S
DAL	Dallas Cowboys	DAL_logo-50x50.gif		NFC	E
WAS	Washington Redskins	WAS_logo-50x50.gif		NFC	E
SEA	Seattle Seahawks	SEA_logo-50x50.gif		NFC	W
ATL	Atlanta Falcons	ATL_logo-50x50.gif		NFC	S
CIN	Cincinnati Bengals	CIN_logo-50x50.gif		AFC	N
MIA	Miami Dolphins	MIA_logo-50x50.gif		AFC	E
CAR	Carolina Panthers	CAR_logo-50x50.gif		NFC	S
TB 	Tampa Bay Buccaneers	TB_logo-50x50.gif		NFC	S
BUF	Buffalo Bills	BUF_logo-50x50.gif		AFC	E
PHI	Philadelphia Eagles	PHI_logo-50x50.gif		NFC	E
NO 	New Orleans Saints	NO_logo-50x50.gif		NFC	S
CHI	Chicago	CHI_logo-50x50.gif		NFC	N
JAC	Jacksonville Jaguars	JAC_logo-50x50.gif		AFC	S
HOU	Houston Texans	HOU_logo-50x50.gif		AFC	S
SF 	San Francisco 49ers	SF_logo-50x50.gif		NFC	W
CLE	Cleveland Browns	CLE_logo-50x50.gif		AFC	N
PIT	Pittsburgh Steelers	PIT_logo-50x50.gif		AFC	N
BAL	Baltimore Ravens	BAL_logo-50x50.gif		AFC	N
DET	Detroit Lions	DET_logo-50x50.gif		NFC	N
GB 	Green Bay Packers	GB_logo-50x50.gif		NFC	N
SD 	San Diego Chargers	SD_logo-50x50.gif		AFC	W
OAK	Oakland Raiders	OAK_logo-50x50.gif		AFC	W
MIN	Minnesota Vikings	MIN_logo-50x50.gif		NFC	N
DEN	Denver Broncos	DEN_logo-50x50.gif		AFC	W
STL	St. Louis Rams	STL_logo-50x50.gif		NFC	W
NYJ	New York Jets	NYJ_logo-50x50.gif		AFC	E
ARI	Arizona Cardinals	ARI_logo-50x50.gif		NFC	W
KC 	Kansas City Chiefs	KC_logo-50x50.gif		AFC	W
\.

-- END OF DATA ----------------------------------------------------------



--
-- Name: competition_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY competition
    ADD CONSTRAINT competition_pkey PRIMARY KEY (cid);


--
-- Name: conference_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY conference
    ADD CONSTRAINT conference_pkey PRIMARY KEY (confid);


--
-- Name: div_winner_pick_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY div_winner_pick
    ADD CONSTRAINT div_winner_pick_pkey PRIMARY KEY (cid, confid, divid, uid);


--
-- Name: division_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY division
    ADD CONSTRAINT division_pkey PRIMARY KEY (divid);


--
-- Name: match_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY match
    ADD CONSTRAINT match_pkey PRIMARY KEY (rid, hid, cid);


--
-- Name: question_option_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY option
    ADD CONSTRAINT question_option_pkey PRIMARY KEY (cid, rid, opid);


--
-- Name: answer_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY option_pick
    ADD CONSTRAINT answer_pkey PRIMARY KEY (uid, cid, rid);


--
-- Name: user_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY participant
    ADD CONSTRAINT user_pkey PRIMARY KEY (uid);


--
-- Name: pick_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY pick
    ADD CONSTRAINT pick_pkey PRIMARY KEY (cid, rid, hid, uid);


--
-- Name: registration_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY registration
    ADD CONSTRAINT registration_pkey PRIMARY KEY (cid, uid);


--
-- Name: round_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY round
    ADD CONSTRAINT round_pkey PRIMARY KEY (cid, rid);


--
-- Name: team_in_competition_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY team_in_competition
    ADD CONSTRAINT team_in_competition_pkey PRIMARY KEY (cid, tid);


--
-- Name: team_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY team
    ADD CONSTRAINT team_pkey PRIMARY KEY (tid);


--
-- Name: wildcard_pick_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY wildcard_pick
    ADD CONSTRAINT wildcard_pick_pkey PRIMARY KEY (cid, confid, uid, opid);

--
-- Name: default_competition_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY default_competition
    ADD CONSTRAINT default_competition_cid_fkey FOREIGN KEY (cid) REFERENCES competition(cid) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: div_winner_pick_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY div_winner_pick
    ADD CONSTRAINT div_winner_pick_cid_fkey FOREIGN KEY (cid, tid) REFERENCES team_in_competition(cid, tid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: div_winner_pick_confid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY div_winner_pick
    ADD CONSTRAINT div_winner_pick_confid_fkey FOREIGN KEY (confid) REFERENCES conference(confid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: div_winner_pick_divid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY div_winner_pick
    ADD CONSTRAINT div_winner_pick_divid_fkey FOREIGN KEY (divid) REFERENCES division(divid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: div_winner_pick_uid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY div_winner_pick
    ADD CONSTRAINT div_winner_pick_uid_fkey FOREIGN KEY (uid) REFERENCES participant(uid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: match_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY match
    ADD CONSTRAINT match_cid_fkey FOREIGN KEY (cid, rid) REFERENCES round(cid, rid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: match_cid_fkey1; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY match
    ADD CONSTRAINT match_cid_fkey1 FOREIGN KEY (cid, aid) REFERENCES team_in_competition(cid, tid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: match_cid_fkey2; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY match
    ADD CONSTRAINT match_cid_fkey2 FOREIGN KEY (cid, hid) REFERENCES team_in_competition(cid, tid) ON UPDATE CASCADE ON DELETE CASCADE;


ALTER TABLE ONLY option
    ADD CONSTRAINT option_cid_fkey FOREIGN KEY (cid,rid)  REFERENCES round(cid,rid) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: answer_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY option_pick
    ADD CONSTRAINT answer_cid_fkey FOREIGN KEY (cid, rid,opid) REFERENCES option(cid, rid,opid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: answer_uid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY option_pick
    ADD CONSTRAINT answer_uid_fkey FOREIGN KEY (uid) REFERENCES participant(uid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: pick_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY pick
    ADD CONSTRAINT pick_cid_fkey FOREIGN KEY (cid, rid, hid) REFERENCES match(cid, rid, hid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: pick_uid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY pick
    ADD CONSTRAINT pick_uid_fkey FOREIGN KEY (uid) REFERENCES participant(uid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: registration_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY registration
    ADD CONSTRAINT registration_cid_fkey FOREIGN KEY (cid) REFERENCES competition(cid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: registration_uid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY registration
    ADD CONSTRAINT registration_uid_fkey FOREIGN KEY (uid) REFERENCES participant(uid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: round_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY round
    ADD CONSTRAINT round_cid_fkey FOREIGN KEY (cid) REFERENCES competition(cid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: team_confid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY team
    ADD CONSTRAINT team_confid_fkey FOREIGN KEY (confid) REFERENCES conference(confid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: team_div_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY team
    ADD CONSTRAINT team_div_fkey FOREIGN KEY (divid) REFERENCES division(divid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: team_in_competition_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY team_in_competition
    ADD CONSTRAINT team_in_competition_cid_fkey FOREIGN KEY (cid) REFERENCES competition(cid) ON UPDATE RESTRICT ON DELETE CASCADE;
ALTER TABLE ONLY team_in_competition
    ADD CONSTRAINT team_in_competition_tid_fkey FOREIGN KEY (tid) REFERENCES team(tid) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY wildcard_pick
    ADD CONSTRAINT wildcard_pick_cid_fkey FOREIGN KEY (cid, uid) REFERENCES registration(cid, uid) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY wildcard_pick
    ADD CONSTRAINT wildcard_pick_cid_fkey1 FOREIGN KEY (cid, tid) REFERENCES team_in_competition(cid, tid) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY wildcard_pick
    ADD CONSTRAINT wildcard_pick_confid_fkey FOREIGN KEY (confid) REFERENCES conference(confid) ON UPDATE CASCADE ON DELETE CASCADE;

-- END OF FOREIGN KEY CONSTRAINTS ---------------------------------------------------

GRANT USAGE ON SCHEMA public TO melindas_ball;

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE competition TO melindas_ball;
GRANT SELECT ON TABLE conference TO melindas_ball;
GRANT SELECT,UPDATE ON TABLE default_competition TO melindas_ball;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE div_winner_pick TO melindas_ball;
GRANT SELECT ON TABLE division TO melindas_ball;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE match TO melindas_ball;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE option TO melindas_ball;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE option_pick TO melindas_ball;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE participant TO melindas_ball;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE pick TO melindas_ball;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE registration TO melindas_ball;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE round TO melindas_ball;
GRANT SELECT ON TABLE team TO melindas_ball;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE team_in_competition TO melindas_ball;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE wildcard_pick TO melindas_ball;


GRANT SELECT ON TABLE bonus_score TO melindas_ball;
GRANT SELECT ON TABLE match_score TO melindas_ball;
GRANT SELECT ON TABLE playoff_picks TO melindas_ball;
GRANT SELECT ON TABLE playoff_score TO melindas_ball;
GRANT SELECT ON TABLE round_score TO melindas_ball;

-- Appears Postgres 7.4 does not have control on access to sequences
-- GRANT SELECT,UPDATE ON SEQUENCE competition_cid_seq TO melindas_ball;


--
-- PostgreSQL database dump complete
--

