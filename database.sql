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
-- Name: answer; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE answer (
    uid integer NOT NULL,
    comment text,
    submit_date timestamp with time zone,
    cid integer NOT NULL,
    rid integer NOT NULL,
    oid smallint NOT NULL
);


ALTER TABLE public.answer OWNER TO alan;

--
-- Name: COLUMN answer.submit_date; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN answer.submit_date IS 'Date Time Submitted answer';


--
-- Name: COLUMN answer.oid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN answer.oid IS 'ID of Question Option Selected as Coorect';


--
-- Name: competition; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE competition (
    description character varying(100),
    condition text,
    administrator integer,
    open boolean DEFAULT false NOT NULL,
    cid integer NOT NULL,
    pp_deadline timestamp with time zone
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

COMMENT ON COLUMN competition.pp_deadline IS 'Playoff Prediction Deadline';


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
    match_time timestamp with time zone,
    comment text,
    status smallint DEFAULT 0 NOT NULL,
    ascore integer,
    hscore integer,
    cid integer NOT NULL,
    combined_score integer,
    value smallint DEFAULT 1 NOT NULL
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
-- Name: COLUMN match.match_time; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.match_time IS 'Time when match will be played';


--
-- Name: COLUMN match.comment; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.comment IS 'Administrator Comment for this Match';


--
-- Name: COLUMN match.status; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.status IS '0=inprep,1=picks,2=awaitingresult,3=results available';


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

COMMENT ON COLUMN match.combined_score IS 'Value of Combined Score for an over/under question';


--
-- Name: COLUMN match.value; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN match.value IS 'Points awarded for a correct pick';


--
-- Name: pick; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE pick (
    uid integer NOT NULL,
    comment text,
    submit_date timestamp with time zone,
    cid integer NOT NULL,
    rid integer NOT NULL,
    hid character(3) NOT NULL,
    pid character(3),
    over boolean
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
-- Name: COLUMN pick.submit_date; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN pick.submit_date IS 'Date Time Submitted Pick';


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
-- Name: playoff_prediction; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE playoff_prediction (
    cid integer NOT NULL,
    uid integer NOT NULL,
    team1 character(3),
    team2 character(3),
    team3 character(3),
    team4 character(3),
    team5 character(3),
    team6 character(3)
);


ALTER TABLE public.playoff_prediction OWNER TO alan;

--
-- Name: TABLE playoff_prediction; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE playoff_prediction IS 'Playoff Predictions';


--
-- Name: COLUMN playoff_prediction.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN playoff_prediction.cid IS 'Competition ID';


--
-- Name: COLUMN playoff_prediction.uid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN playoff_prediction.uid IS 'User UD';


--
-- Name: COLUMN playoff_prediction.team1; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN playoff_prediction.team1 IS 'Team selected for Playoff';


--
-- Name: COLUMN playoff_prediction.team2; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN playoff_prediction.team2 IS 'Team selected for Playoff';


--
-- Name: COLUMN playoff_prediction.team3; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN playoff_prediction.team3 IS 'Team selected for Playoff';


--
-- Name: COLUMN playoff_prediction.team4; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN playoff_prediction.team4 IS 'Team selected for Playoff';


--
-- Name: COLUMN playoff_prediction.team5; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN playoff_prediction.team5 IS 'Team selected for Playoff';


--
-- Name: COLUMN playoff_prediction.team6; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN playoff_prediction.team6 IS 'Team selected for Playoff';


--
-- Name: question_option; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE question_option (
    cid integer NOT NULL,
    rid integer NOT NULL,
    oid smallint NOT NULL,
    option integer NOT NULL
);


ALTER TABLE public.question_option OWNER TO alan;

--
-- Name: TABLE question_option; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE question_option IS 'Holds one possible answer to the bonus question';


--
-- Name: COLUMN question_option.cid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN question_option.cid IS 'Competition ID';


--
-- Name: COLUMN question_option.rid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN question_option.rid IS 'Round ID';


--
-- Name: COLUMN question_option.oid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN question_option.oid IS 'Option ID';


--
-- Name: COLUMN question_option.option; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN question_option.option IS 'Text of potential Answer to Bonus Question';


--
-- Name: registration; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE registration (
    uid integer NOT NULL,
    cid integer NOT NULL,
    agree_date timestamp with time zone
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
-- Name: COLUMN registration.agree_date; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN registration.agree_date IS 'Date Time Agreed with Conditions of Registration';


--
-- Name: round; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE round (
    rid integer NOT NULL,
    comment text,
    deadline timestamp with time zone,
    cid integer NOT NULL,
    question text,
    valid_question boolean DEFAULT false
);


ALTER TABLE public.round OWNER TO alan;

--
-- Name: COLUMN round.rid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.rid IS 'Round Number';


--
-- Name: COLUMN round.deadline; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN round.deadline IS 'Deadline for answering Bonus Question';


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
    cid integer NOT NULL
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
-- Name: user; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "user" (
    uid integer NOT NULL,
    name character varying,
    email character varying
);


ALTER TABLE public."user" OWNER TO alan;

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
-- Name: question_option_option_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE question_option_option_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.question_option_option_seq OWNER TO alan;

--
-- Name: question_option_option_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--

ALTER SEQUENCE question_option_option_seq OWNED BY question_option.option;


--
-- Name: question_option_option_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT pg_catalog.setval('question_option_option_seq', 1, false);


--
-- Name: cid; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE competition ALTER COLUMN cid SET DEFAULT nextval('competition_cid_seq'::regclass);


--
-- Name: option; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE question_option ALTER COLUMN option SET DEFAULT nextval('question_option_option_seq'::regclass);


--
-- Data for Name: answer; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY answer (uid, comment, submit_date, cid, rid, oid) FROM stdin;
\.


--
-- Data for Name: competition; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY competition (description, condition, administrator, open, cid, pp_deadline) FROM stdin;
		\N	f	1	\N
		\N	t	2	\N
		\N	f	3	\N
		\N	t	4	\N
		\N	f	5	\N
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

COPY match (rid, hid, aid, match_time, comment, status, ascore, hscore, cid, combined_score, value) FROM stdin;
\.


--
-- Data for Name: pick; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY pick (uid, comment, submit_date, cid, rid, hid, pid, over) FROM stdin;
\.


--
-- Data for Name: playoff_prediction; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY playoff_prediction (cid, uid, team1, team2, team3, team4, team5, team6) FROM stdin;
\.


--
-- Data for Name: question_option; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY question_option (cid, rid, oid, option) FROM stdin;
\.


--
-- Data for Name: registration; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY registration (uid, cid, agree_date) FROM stdin;
\.


--
-- Data for Name: round; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY round (rid, comment, deadline, cid, question, valid_question) FROM stdin;
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

COPY team_in_competition (tid, cid) FROM stdin;
\.


--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: alan
--

COPY "user" (uid, name, email) FROM stdin;
\.


--
-- Name: answer_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY answer
    ADD CONSTRAINT answer_pkey PRIMARY KEY (cid, rid, oid, uid);


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
-- Name: playoff_prediction_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY playoff_prediction
    ADD CONSTRAINT playoff_prediction_pkey PRIMARY KEY (cid, uid);


--
-- Name: question_option_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY question_option
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
-- Name: answer_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY answer
    ADD CONSTRAINT answer_cid_fkey FOREIGN KEY (cid, rid) REFERENCES round(cid, rid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: answer_uid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY answer
    ADD CONSTRAINT answer_uid_fkey FOREIGN KEY (uid) REFERENCES "user"(uid) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: default_competition_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY default_competition
    ADD CONSTRAINT default_competition_cid_fkey FOREIGN KEY (cid) REFERENCES competition(cid) ON UPDATE RESTRICT ON DELETE CASCADE;


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
-- Name: playoff_prediction_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY playoff_prediction
    ADD CONSTRAINT playoff_prediction_cid_fkey FOREIGN KEY (cid, team1) REFERENCES team_in_competition(cid, tid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: playoff_prediction_cid_fkey1; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY playoff_prediction
    ADD CONSTRAINT playoff_prediction_cid_fkey1 FOREIGN KEY (cid, team2) REFERENCES team_in_competition(cid, tid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: playoff_prediction_cid_fkey2; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY playoff_prediction
    ADD CONSTRAINT playoff_prediction_cid_fkey2 FOREIGN KEY (cid, team3) REFERENCES team_in_competition(cid, tid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: playoff_prediction_cid_fkey3; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY playoff_prediction
    ADD CONSTRAINT playoff_prediction_cid_fkey3 FOREIGN KEY (cid, team4) REFERENCES team_in_competition(cid, tid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: playoff_prediction_cid_fkey4; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY playoff_prediction
    ADD CONSTRAINT playoff_prediction_cid_fkey4 FOREIGN KEY (cid, team5) REFERENCES team_in_competition(cid, tid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: playoff_prediction_cid_fkey5; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY playoff_prediction
    ADD CONSTRAINT playoff_prediction_cid_fkey5 FOREIGN KEY (cid, team6) REFERENCES team_in_competition(cid, tid) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- Name: playoff_prediction_uid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: alan
--

ALTER TABLE ONLY playoff_prediction
    ADD CONSTRAINT playoff_prediction_uid_fkey FOREIGN KEY (uid) REFERENCES "user"(uid) ON UPDATE CASCADE ON DELETE CASCADE;


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

