--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: melindas_ball; Type: COMMENT; Schema: -; Owner: melindas_ball
--

COMMENT ON DATABASE melindas_ball IS 'Melindas Backups Football Pool Competitions Database';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: competition; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE competition (
    description character varying(100),
    condition text,
    administrator integer,
    open boolean DEFAULT false NOT NULL,
    cid integer NOT NULL,
    pp_deadline bigint DEFAULT 0 NOT NULL,
    gap bigint DEFAULT 3600 NOT NULL
);


ALTER TABLE public.competition OWNER TO alan;

--
-- Name: COLUMN competition.description; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition.description IS 'This is the name that appears in the header for the competition';


--
-- Name: COLUMN competition.condition; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition.condition IS 'This is the text that a user has to agree to in order to register himself for the competition';


--
-- Name: COLUMN competition.administrator; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition.administrator IS 'The uid of the administrator';


--
-- Name: COLUMN competition.open; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition.open IS 'Says whether a user may register for the competion or not';


--
-- Name: COLUMN competition.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition.cid IS 'Competition ID';


--
-- Name: COLUMN competition.pp_deadline; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition.pp_deadline IS 'Playoff Selection Deadline 0 if no selection';


--
-- Name: COLUMN competition.gap; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition.gap IS 'Seconds to go before match to make pick deadline';


--
-- Name: competition_conference_wildcards; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE competition_conference_wildcards (
    cid integer NOT NULL,
    confid character(3) NOT NULL,
    wild1 character(3),
    wild2 character(3)
);


ALTER TABLE public.competition_conference_wildcards OWNER TO alan;

--
-- Name: TABLE competition_conference_wildcards; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE competition_conference_wildcards IS 'Defines who where the playoff wildcards for a conference in a competition';


--
-- Name: COLUMN competition_conference_wildcards.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition_conference_wildcards.cid IS 'Competition ID';


--
-- Name: COLUMN competition_conference_wildcards.confid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition_conference_wildcards.confid IS 'Conference ID';


--
-- Name: COLUMN competition_conference_wildcards.wild1; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition_conference_wildcards.wild1 IS 'Wild Team 1 making playoff';


--
-- Name: COLUMN competition_conference_wildcards.wild2; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN competition_conference_wildcards.wild2 IS 'Wild Team 2 making playoff';


--
-- Name: conference; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE conference (
    confid character(3) NOT NULL,
    name character varying(30)
);


ALTER TABLE public.conference OWNER TO alan;

--
-- Name: COLUMN conference.confid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN conference.confid IS 'Conference 3 letter acronym';


--
-- Name: default_competition; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE default_competition (
    cid integer
);


ALTER TABLE public.default_competition OWNER TO alan;

--
-- Name: TABLE default_competition; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE default_competition IS 'This will have a single row containing the key of the default competition';


--
-- Name: div_winner; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE div_winner (
    cid integer NOT NULL,
    confid character(3) NOT NULL,
    did character(1) NOT NULL,
    tid character(3)
);


ALTER TABLE public.div_winner OWNER TO alan;

--
-- Name: TABLE div_winner; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE div_winner IS 'Winner of the division - makes the playoff';


--
-- Name: COLUMN div_winner.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN div_winner.cid IS 'Competition ID';


--
-- Name: COLUMN div_winner.confid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN div_winner.confid IS 'Conference ID';


--
-- Name: COLUMN div_winner.did; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN div_winner.did IS 'Divisional ID';


--
-- Name: COLUMN div_winner.tid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN div_winner.tid IS 'Team ID';


--
-- Name: div_winner_pick; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE div_winner_pick (
    cid integer NOT NULL,
    confid character(3) NOT NULL,
    divid character(1) NOT NULL,
    team character(3),
    uid integer NOT NULL,
    submit_time integer NOT NULL
);


ALTER TABLE public.div_winner_pick OWNER TO alan;

--
-- Name: TABLE div_winner_pick; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE div_winner_pick IS 'User Pick of each division winner';


--
-- Name: COLUMN div_winner_pick.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN div_winner_pick.cid IS 'Conference ID';


--
-- Name: COLUMN div_winner_pick.confid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN div_winner_pick.confid IS 'Conference ID';


--
-- Name: COLUMN div_winner_pick.divid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN div_winner_pick.divid IS 'Division ID';


--
-- Name: COLUMN div_winner_pick.team; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN div_winner_pick.team IS 'Team who will win division';


--
-- Name: COLUMN div_winner_pick.uid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN div_winner_pick.uid IS 'User ID';


--
-- Name: COLUMN div_winner_pick.submit_time; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN div_winner_pick.submit_time IS 'Time of submission';


--
-- Name: division; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE division (
    divid character(1) NOT NULL,
    name character varying(6)
);


ALTER TABLE public.division OWNER TO alan;

--
-- Name: TABLE division; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE division IS 'Football Conference Division';


--
-- Name: match; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

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


ALTER TABLE public.match OWNER TO alan;

--
-- Name: COLUMN match.rid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.rid IS 'round id';


--
-- Name: COLUMN match.hid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.hid IS 'home team id';


--
-- Name: COLUMN match.aid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.aid IS 'Away Team ID';


--
-- Name: COLUMN match.comment; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.comment IS 'Administrator Comment for this Match';


--
-- Name: COLUMN match.ascore; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.ascore IS 'Away Team Score';


--
-- Name: COLUMN match.hscore; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.hscore IS 'Home Team Score';


--
-- Name: COLUMN match.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.cid IS 'Competition ID';


--
-- Name: COLUMN match.combined_score; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.combined_score IS 'Value of Combined Score for an over/under question (add 0.5 to this for the question)';


--
-- Name: COLUMN match.open; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.open IS 'True if Match is set up and ready';


--
-- Name: COLUMN match.match_time; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.match_time IS 'Time match is due to be played';


--
-- Name: option; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE option (
    cid integer NOT NULL,
    rid integer NOT NULL,
    oid integer NOT NULL,
    label character varying(14)
);


ALTER TABLE public.option OWNER TO alan;

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
-- Name: COLUMN option.oid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option.oid IS 'Option ID';


--
-- Name: COLUMN option.label; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option.label IS 'Simple Label for this option';


--
-- Name: option_pick; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE option_pick (
    uid integer NOT NULL,
    comment text,
    cid integer NOT NULL,
    rid integer NOT NULL,
    oid integer,
    submit_time bigint
);


ALTER TABLE public.option_pick OWNER TO alan;

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
-- Name: COLUMN option_pick.oid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN option_pick.oid IS 'ID of Question Option Selected as Correct if multichoice, else value of answer (only if multichoice)';


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


ALTER TABLE public.pick OWNER TO alan;

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
    agree_time bigint
);


ALTER TABLE public.registration OWNER TO alan;

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


ALTER TABLE public.round OWNER TO alan;

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

COMMENT ON COLUMN round.answer IS 'If not null an answer to a numeric question or oid of mutichoice question';


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
-- Name: team; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE team (
    tid character(3) NOT NULL,
    name character varying(50) NOT NULL,
    logo character varying(80),
    url character varying(100),
    confid character(3),
    div character(1)
);


ALTER TABLE public.team OWNER TO alan;

--
-- Name: COLUMN team.confid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN team.confid IS 'Conference Team Plays in ';


--
-- Name: COLUMN team.div; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN team.div IS 'division team plays in';


--
-- Name: team_in_competition; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE team_in_competition (
    tid character(3) NOT NULL,
    cid integer NOT NULL,
    made_playoff boolean DEFAULT false NOT NULL
);


ALTER TABLE public.team_in_competition OWNER TO alan;

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
-- Name: user; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "user" (
    uid integer NOT NULL,
    name character varying,
    email character varying
);


ALTER TABLE public."user" OWNER TO alan;

--
-- Name: wildcard_pick; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE wildcard_pick (
    cid integer NOT NULL,
    confid character(3) NOT NULL,
    uid integer NOT NULL,
    wild1 character(3),
    wild2 character(3),
    submit_time bigint
);


ALTER TABLE public.wildcard_pick OWNER TO alan;

--
-- Name: TABLE wildcard_pick; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE wildcard_pick IS 'Users Pick of WildCard Entries for each conference';


--
-- Name: COLUMN wildcard_pick.submit_time; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN wildcard_pick.submit_time IS 'Time of Submission';


--
-- Name: competition_cid_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE competition_cid_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.competition_cid_seq OWNER TO alan;

--
-- Name: competition_cid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--

ALTER SEQUENCE competition_cid_seq OWNED BY competition.cid;


--
-- Name: competition_cid_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT pg_catalog.setval('competition_cid_seq', 5, true);


--
-- Name: div_winner_pick_cid_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE div_winner_pick_cid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.div_winner_pick_cid_seq OWNER TO alan;

--
-- Name: div_winner_pick_cid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--

ALTER SEQUENCE div_winner_pick_cid_seq OWNED BY div_winner_pick.cid;


--
-- Name: div_winner_pick_cid_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT pg_catalog.setval('div_winner_pick_cid_seq', 1, false);


--
-- Name: div_winner_pick_submit_time_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE div_winner_pick_submit_time_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.div_winner_pick_submit_time_seq OWNER TO alan;

--
-- Name: div_winner_pick_submit_time_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--

ALTER SEQUENCE div_winner_pick_submit_time_seq OWNED BY div_winner_pick.submit_time;


--
-- Name: div_winner_pick_submit_time_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT pg_catalog.setval('div_winner_pick_submit_time_seq', 1, false);


--
-- Name: cid; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE competition ALTER COLUMN cid SET DEFAULT nextval('competition_cid_seq'::regclass);


--
-- Name: submit_time; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE div_winner_pick ALTER COLUMN submit_time SET DEFAULT nextval('div_winner_pick_submit_time_seq'::regclass);


--
-- Data for Name: competition; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY competition (description, condition, administrator, open, cid, pp_deadline, gap) FROM stdin;
\.


--
-- Data for Name: competition_conference_wildcards; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY competition_conference_wildcards (cid, confid, wild1, wild2) FROM stdin;
\.


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

COPY default_competition (cid) FROM stdin;
\.


--
-- Data for Name: div_winner; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY div_winner (cid, confid, did, tid) FROM stdin;
\.


--
-- Data for Name: div_winner_pick; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY div_winner_pick (cid, confid, divid, team, uid, submit_time) FROM stdin;
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
-- Data for Name: match; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY match (rid, hid, aid, comment, ascore, hscore, cid, combined_score, open, match_time) FROM stdin;
\.


--
-- Data for Name: option; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY option (cid, rid, oid, label) FROM stdin;
\.


--
-- Data for Name: option_pick; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY option_pick (uid, comment, cid, rid, oid, submit_time) FROM stdin;
\.


--
-- Data for Name: pick; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY pick (uid, comment, cid, rid, hid, pid, over, submit_time) FROM stdin;
\.


--
-- Data for Name: registration; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY registration (uid, cid, agree_time) FROM stdin;
\.


--
-- Data for Name: round; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY round (rid, cid, question, valid_question, answer, value, name, ou_round, deadline) FROM stdin;
\.


--
-- Data for Name: team; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY team (tid, name, logo, url, confid, div) FROM stdin;
NE 	New England Patriots			AFC	E
NYG	New York Giants			NFC	E
TEN	Tennessee Titans			AFC	S
IND	Indianapolis Colts			AFC	S
DAL	Dallas Cowboys			NFC	E
WAS	Washington Redskins			NFC	E
SEA	Seattle Seahawks			NFC	W
ATL	Atlanta Falcons			NFC	S
CIN	Cincinnati Bengals			AFC	N
MIA	Miami Dolphins			AFC	E
CAR	Carolina Panthers			NFC	S
TB 	Tampa Bay Buccaneers			NFC	S
BUF	Buffalo Bills			AFC	E
PHI	Philadelphia Eagles			NFC	E
NO 	New Orleans Saints			NFC	S
CHI	Chicago			NFC	N
JAC	Jacksonville Jaguars			AFC	S
HOU	Houston Texans			AFC	S
SF 	San Francisco 49ers			NFC	W
CLE	Cleveland Browns			AFC	N
PIT	Pittsburgh Steelers			AFC	N
BAL	Baltimore Ravens			AFC	N
DET	Detroit Lions			NFC	N
GB 	Green Bay Packers			NFC	N
SD 	San Diego Chargers			AFC	W
OAK	Oakland Raiders			AFC	W
MIN	Minnesota Vikings			NFC	N
DEN	Denver Broncos			AFC	W
STL	St. Louis Rams			NFC	W
NYJ	New York Jets			AFC	E
ARI	Arizona Cardinals			NFC	W
KC 	Kansas City Chiefs			AFC	W
\.


--
-- Data for Name: team_in_competition; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY team_in_competition (tid, cid, made_playoff) FROM stdin;
\.


--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY "user" (uid, name, email) FROM stdin;
\.


--
-- Data for Name: wildcard_pick; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY wildcard_pick (cid, confid, uid, wild1, wild2, submit_time) FROM stdin;
\.


--
-- Name: answer_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY option_pick
    ADD CONSTRAINT answer_pkey PRIMARY KEY (uid, cid, rid);


--
-- Name: competition_conference_wildcards_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY competition_conference_wildcards
    ADD CONSTRAINT competition_conference_wildcards_pkey PRIMARY KEY (cid, confid);


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
-- Name: div_winner_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY div_winner
    ADD CONSTRAINT div_winner_pkey PRIMARY KEY (cid, confid, did);


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
-- Name: pick_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY pick
    ADD CONSTRAINT pick_pkey PRIMARY KEY (cid, rid, hid, uid);


--
-- Name: question_option_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY option
    ADD CONSTRAINT question_option_pkey PRIMARY KEY (cid, rid, oid);


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
-- Name: user_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (uid);


--
-- Name: wildcard_pick_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY wildcard_pick
    ADD CONSTRAINT wildcard_pick_pkey PRIMARY KEY (cid, confid, uid);


--
-- Name: answer_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY option_pick
    ADD CONSTRAINT answer_cid_fkey FOREIGN KEY (cid, rid) REFERENCES round(cid, rid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: answer_uid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY option_pick
    ADD CONSTRAINT answer_uid_fkey FOREIGN KEY (uid) REFERENCES "user"(uid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: competition_conference_wildcards_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY competition_conference_wildcards
    ADD CONSTRAINT competition_conference_wildcards_cid_fkey FOREIGN KEY (cid, wild1) REFERENCES team_in_competition(cid, tid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: competition_conference_wildcards_cid_fkey1; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY competition_conference_wildcards
    ADD CONSTRAINT competition_conference_wildcards_cid_fkey1 FOREIGN KEY (cid, wild2) REFERENCES team_in_competition(cid, tid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: competition_conference_wildcards_confid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY competition_conference_wildcards
    ADD CONSTRAINT competition_conference_wildcards_confid_fkey FOREIGN KEY (confid) REFERENCES conference(confid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: default_competition_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY default_competition
    ADD CONSTRAINT default_competition_cid_fkey FOREIGN KEY (cid) REFERENCES competition(cid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: div_winner_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY div_winner
    ADD CONSTRAINT div_winner_cid_fkey FOREIGN KEY (cid, tid) REFERENCES team_in_competition(cid, tid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: div_winner_confid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY div_winner
    ADD CONSTRAINT div_winner_confid_fkey FOREIGN KEY (confid) REFERENCES conference(confid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: div_winner_did_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY div_winner
    ADD CONSTRAINT div_winner_did_fkey FOREIGN KEY (did) REFERENCES division(divid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: div_winner_pick_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY div_winner_pick
    ADD CONSTRAINT div_winner_pick_cid_fkey FOREIGN KEY (cid, team) REFERENCES team_in_competition(cid, tid) ON UPDATE CASCADE ON DELETE CASCADE;


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
    ADD CONSTRAINT div_winner_pick_uid_fkey FOREIGN KEY (uid) REFERENCES "user"(uid) ON UPDATE RESTRICT ON DELETE CASCADE;


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


--
-- Name: pick_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY pick
    ADD CONSTRAINT pick_cid_fkey FOREIGN KEY (cid, rid, hid) REFERENCES match(cid, rid, hid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: pick_uid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY pick
    ADD CONSTRAINT pick_uid_fkey FOREIGN KEY (uid) REFERENCES "user"(uid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: registration_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY registration
    ADD CONSTRAINT registration_cid_fkey FOREIGN KEY (cid) REFERENCES competition(cid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: registration_uid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY registration
    ADD CONSTRAINT registration_uid_fkey FOREIGN KEY (uid) REFERENCES "user"(uid) ON UPDATE RESTRICT ON DELETE CASCADE;


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
    ADD CONSTRAINT team_div_fkey FOREIGN KEY (div) REFERENCES division(divid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: team_in_competition_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY team_in_competition
    ADD CONSTRAINT team_in_competition_cid_fkey FOREIGN KEY (cid) REFERENCES competition(cid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: team_in_competition_tid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY team_in_competition
    ADD CONSTRAINT team_in_competition_tid_fkey FOREIGN KEY (tid) REFERENCES team(tid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: wildcard_pick_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY wildcard_pick
    ADD CONSTRAINT wildcard_pick_cid_fkey FOREIGN KEY (cid, wild2) REFERENCES team_in_competition(cid, tid) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: wildcard_pick_cid_fkey2; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY wildcard_pick
    ADD CONSTRAINT wildcard_pick_cid_fkey2 FOREIGN KEY (cid, wild1) REFERENCES team_in_competition(cid, tid) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;
GRANT ALL ON SCHEMA public TO melindas_ball;


--
-- PostgreSQL database dump complete
--

