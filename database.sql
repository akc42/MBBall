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
-- Name: Administrator; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Administrator" (
    "UID" integer,
    "CID" character(10)
);


ALTER TABLE public."Administrator" OWNER TO alan;

--
-- Name: TABLE "Administrator"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Administrator" IS 'Defines administrators for each competition';


--
-- Name: COLUMN "Administrator"."UID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Administrator"."UID" IS 'User ID';


--
-- Name: COLUMN "Administrator"."CID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Administrator"."CID" IS 'Competition ID';


--
-- Name: Agreement; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Agreement" (
    "UID" integer,
    "CID" character(10),
    "AgreeDate" date
);


ALTER TABLE public."Agreement" OWNER TO alan;

--
-- Name: TABLE "Agreement"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Agreement" IS 'Users Agreement to Competition Conditions';


--
-- Name: COLUMN "Agreement"."UID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Agreement"."UID" IS 'User ID';


--
-- Name: COLUMN "Agreement"."CID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Agreement"."CID" IS 'Competition ID';


--
-- Name: COLUMN "Agreement"."AgreeDate"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Agreement"."AgreeDate" IS 'Date Agreement Made';


--
-- Name: Bonus Question; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Bonus Question" (
    "BQID" integer NOT NULL,
    "RID" integer,
    "CID" character(10),
    "Question" text
);


ALTER TABLE public."Bonus Question" OWNER TO alan;

--
-- Name: TABLE "Bonus Question"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Bonus Question" IS 'Additional Question to be asked in a Round';


--
-- Name: COLUMN "Bonus Question"."RID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Bonus Question"."RID" IS 'Round ID';


--
-- Name: COLUMN "Bonus Question"."CID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Bonus Question"."CID" IS 'Competition ID';


--
-- Name: Bonus Question Option; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Bonus Question Option" (
    "BQOID" integer NOT NULL,
    "BQID" integer,
    "Option" text
);


ALTER TABLE public."Bonus Question Option" OWNER TO alan;

--
-- Name: TABLE "Bonus Question Option"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Bonus Question Option" IS 'One of the options for this question';


--
-- Name: COLUMN "Bonus Question Option"."Option"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Bonus Question Option"."Option" IS 'Text Describing this Option';


--
-- Name: Competition; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Competition" (
    "CID" character(10) NOT NULL,
    "Description" character varying(100),
    "Condition" text,
    start timestamp with time zone,
    incomplete boolean DEFAULT true NOT NULL
);


ALTER TABLE public."Competition" OWNER TO alan;

--
-- Name: COLUMN "Competition"."CID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Competition"."CID" IS 'Use 10 chars to briefly describe - suggest year as first 4';


--
-- Name: COLUMN "Competition"."Description"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Competition"."Description" IS 'User Friendly Description of Competition (to appear on header section of page)';


--
-- Name: COLUMN "Competition"."Condition"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Competition"."Condition" IS 'Condition to which Users have to agree to compete';


--
-- Name: COLUMN "Competition".start; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Competition".start IS 'Point at which Competition Opens to accept users signup';


--
-- Name: COLUMN "Competition".incomplete; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Competition".incomplete IS 'Indication whether competion has finished';


--
-- Name: Current Competion; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Current Competion" (
    "CID" character(10) NOT NULL
);


ALTER TABLE public."Current Competion" OWNER TO alan;

--
-- Name: TABLE "Current Competion"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Current Competion" IS 'Table contains single record with ID of current (prime) competition';


--
-- Name: COLUMN "Current Competion"."CID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Current Competion"."CID" IS 'Single record with ID of current competion';


--
-- Name: Global Pick; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Global Pick" (
    "UID" integer NOT NULL,
    "QID" integer NOT NULL,
    "Answer" integer NOT NULL,
    "Comment" text,
    submitdate timestamp with time zone
);


ALTER TABLE public."Global Pick" OWNER TO alan;

--
-- Name: TABLE "Global Pick"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Global Pick" IS 'Users selection of option for global question';


--
-- Name: COLUMN "Global Pick"."UID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Pick"."UID" IS 'User ID';


--
-- Name: COLUMN "Global Pick"."QID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Pick"."QID" IS 'Question ID for which this is the answer';


--
-- Name: COLUMN "Global Pick"."Answer"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Pick"."Answer" IS 'QOID that corresponds to Answer';


--
-- Name: COLUMN "Global Pick"."Comment"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Pick"."Comment" IS 'User Comment';


--
-- Name: COLUMN "Global Pick".submitdate; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Pick".submitdate IS 'When Answer Submitted';


--
-- Name: Global Question; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Global Question" (
    "QID" integer NOT NULL,
    "CID" character(10),
    "Question" text,
    "Deadline" timestamp with time zone
);


ALTER TABLE public."Global Question" OWNER TO alan;

--
-- Name: TABLE "Global Question"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Global Question" IS 'Global Question that has to be answered for a competition';


--
-- Name: COLUMN "Global Question"."QID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Question"."QID" IS 'Question ID';


--
-- Name: COLUMN "Global Question"."CID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Question"."CID" IS 'Competition ID';


--
-- Name: COLUMN "Global Question"."Question"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Question"."Question" IS 'Text of Question';


--
-- Name: COLUMN "Global Question"."Deadline"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Question"."Deadline" IS 'Deadline for Answer';


--
-- Name: Global Question Option; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Global Question Option" (
    "QOID" integer NOT NULL,
    "QID" integer,
    "Option" text
);


ALTER TABLE public."Global Question Option" OWNER TO alan;

--
-- Name: TABLE "Global Question Option"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Global Question Option" IS 'One of the options for a Global Question';


--
-- Name: COLUMN "Global Question Option"."QID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Question Option"."QID" IS 'Question ID';


--
-- Name: COLUMN "Global Question Option"."Option"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Global Question Option"."Option" IS 'Option text';


--
-- Name: Match; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Match" (
    "MID" integer NOT NULL,
    "RID" integer NOT NULL,
    "HID" character(3),
    "AID" character(3),
    "CID" character(10),
    "Deadline" timestamp with time zone,
    "Comment" text
);


ALTER TABLE public."Match" OWNER TO alan;

--
-- Name: COLUMN "Match"."HID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Match"."HID" IS 'Home team ID';


--
-- Name: COLUMN "Match"."AID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Match"."AID" IS 'Away team ID';


--
-- Name: COLUMN "Match"."CID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Match"."CID" IS 'Competition ID';


--
-- Name: COLUMN "Match"."Deadline"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Match"."Deadline" IS 'Optional Time Pick must be in by';


--
-- Name: COLUMN "Match"."Comment"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Match"."Comment" IS 'Free form comment about this match by administrator';


--
-- Name: Pick; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Pick" (
    "UID" integer,
    "MID" integer,
    "Result" smallint,
    "Comment" text,
    submitdate timestamp with time zone
);


ALTER TABLE public."Pick" OWNER TO alan;

--
-- Name: TABLE "Pick"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Pick" IS 'Users Pick for a Match';


--
-- Name: COLUMN "Pick"."UID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Pick"."UID" IS 'User ID';


--
-- Name: COLUMN "Pick"."MID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Pick"."MID" IS 'Match ID';


--
-- Name: COLUMN "Pick"."Result"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Pick"."Result" IS '0=draw, 1=home,2=away';


--
-- Name: COLUMN "Pick"."Comment"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Pick"."Comment" IS 'Comment by User on his pick';


--
-- Name: COLUMN "Pick".submitdate; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Pick".submitdate IS 'When submitted';


--
-- Name: Round; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Round" (
    "RID" integer NOT NULL,
    "CID" character(10) NOT NULL,
    "Comment" text,
    deadline timestamp with time zone
);


ALTER TABLE public."Round" OWNER TO alan;

--
-- Name: TABLE "Round"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Round" IS 'Rounds in a competition';


--
-- Name: COLUMN "Round"."RID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Round"."RID" IS 'Incrementing sequence restarted at one for each competition';


--
-- Name: COLUMN "Round"."Comment"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Round"."Comment" IS 'Comments about the round';


--
-- Name: COLUMN "Round".deadline; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Round".deadline IS 'deadline for picks for this round';


--
-- Name: Team; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Team" (
    tid character(3) NOT NULL,
    "Name" character varying(100) NOT NULL,
    logo character varying(80),
    url character varying(100)
);


ALTER TABLE public."Team" OWNER TO alan;

--
-- Name: COLUMN "Team".tid; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Team".tid IS 'Team ID';


--
-- Name: COLUMN "Team"."Name"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Team"."Name" IS 'Team Name';


--
-- Name: COLUMN "Team".logo; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Team".logo IS 'Relative URL of Logo image';


--
-- Name: COLUMN "Team".url; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "Team".url IS 'url of team web site';


--
-- Name: Team in Competition; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "Team in Competition" (
    "CID" character(10) NOT NULL,
    "TID" character(3) NOT NULL
);


ALTER TABLE public."Team in Competition" OWNER TO alan;

--
-- Name: TABLE "Team in Competition"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "Team in Competition" IS 'Defines that the team is in a competition';


--
-- Name: User; Type: TABLE; Schema: public; Owner: alan; Tablespace: 
--

CREATE TABLE "User" (
    "UID" integer NOT NULL,
    "NAME" character varying,
    "EMAIL" character varying
);


ALTER TABLE public."User" OWNER TO alan;

--
-- Name: TABLE "User"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON TABLE "User" IS 'User from Forum';


--
-- Name: COLUMN "User"."UID"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "User"."UID" IS 'User ID from Forum';


--
-- Name: COLUMN "User"."NAME"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "User"."NAME" IS 'Display Name from Forum';


--
-- Name: COLUMN "User"."EMAIL"; Type: COMMENT; Schema: public; Owner: alan
--

COMMENT ON COLUMN "User"."EMAIL" IS 'E-Mail address for notification of due picks';


--
-- Name: Bonus Question Option_BQOID_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE "Bonus Question Option_BQOID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public."Bonus Question Option_BQOID_seq" OWNER TO alan;

--
-- Name: Bonus Question Option_BQOID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--

ALTER SEQUENCE "Bonus Question Option_BQOID_seq" OWNED BY "Bonus Question Option"."BQOID";


--
-- Name: Bonus Question Option_BQOID_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT pg_catalog.setval('"Bonus Question Option_BQOID_seq"', 1, false);


--
-- Name: Bonus Question_BQID_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE "Bonus Question_BQID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public."Bonus Question_BQID_seq" OWNER TO alan;

--
-- Name: Bonus Question_BQID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--

ALTER SEQUENCE "Bonus Question_BQID_seq" OWNED BY "Bonus Question"."BQID";


--
-- Name: Bonus Question_BQID_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT pg_catalog.setval('"Bonus Question_BQID_seq"', 1, false);


--
-- Name: Global Question Option_QOID_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE "Global Question Option_QOID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public."Global Question Option_QOID_seq" OWNER TO alan;

--
-- Name: Global Question Option_QOID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--

ALTER SEQUENCE "Global Question Option_QOID_seq" OWNED BY "Global Question Option"."QOID";


--
-- Name: Global Question Option_QOID_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT pg_catalog.setval('"Global Question Option_QOID_seq"', 1, false);


--
-- Name: Global Question_QID_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE "Global Question_QID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public."Global Question_QID_seq" OWNER TO alan;

--
-- Name: Global Question_QID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--

ALTER SEQUENCE "Global Question_QID_seq" OWNED BY "Global Question"."QID";


--
-- Name: Global Question_QID_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT pg_catalog.setval('"Global Question_QID_seq"', 1, false);


--
-- Name: Match_MID_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE "Match_MID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public."Match_MID_seq" OWNER TO alan;

--
-- Name: Match_MID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--

ALTER SEQUENCE "Match_MID_seq" OWNED BY "Match"."MID";


--
-- Name: Match_MID_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT pg_catalog.setval('"Match_MID_seq"', 1, false);


--
-- Name: Round_RID_seq; Type: SEQUENCE; Schema: public; Owner: alan
--

CREATE SEQUENCE "Round_RID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public."Round_RID_seq" OWNER TO alan;

--
-- Name: Round_RID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: alan
--

ALTER SEQUENCE "Round_RID_seq" OWNED BY "Round"."RID";


--
-- Name: Round_RID_seq; Type: SEQUENCE SET; Schema: public; Owner: alan
--

SELECT pg_catalog.setval('"Round_RID_seq"', 1, false);


--
-- Name: BQID; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE "Bonus Question" ALTER COLUMN "BQID" SET DEFAULT nextval('"Bonus Question_BQID_seq"'::regclass);


--
-- Name: BQOID; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE "Bonus Question Option" ALTER COLUMN "BQOID" SET DEFAULT nextval('"Bonus Question Option_BQOID_seq"'::regclass);


--
-- Name: QID; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE "Global Question" ALTER COLUMN "QID" SET DEFAULT nextval('"Global Question_QID_seq"'::regclass);


--
-- Name: QOID; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE "Global Question Option" ALTER COLUMN "QOID" SET DEFAULT nextval('"Global Question Option_QOID_seq"'::regclass);


--
-- Name: MID; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE "Match" ALTER COLUMN "MID" SET DEFAULT nextval('"Match_MID_seq"'::regclass);


--
-- Name: RID; Type: DEFAULT; Schema: public; Owner: alan
--

ALTER TABLE "Round" ALTER COLUMN "RID" SET DEFAULT nextval('"Round_RID_seq"'::regclass);


--
-- Data for Name: Administrator; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Agreement; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Bonus Question; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Bonus Question Option; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Competition; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Current Competion; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Global Pick; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Global Question; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Global Question Option; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Match; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Pick; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Round; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Team; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: Team in Competition; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Data for Name: User; Type: TABLE DATA; Schema: public; Owner: alan
--



--
-- Name: Bonus Question Option_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Bonus Question Option"
    ADD CONSTRAINT "Bonus Question Option_pkey" PRIMARY KEY ("BQOID");


--
-- Name: Bonus Question_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Bonus Question"
    ADD CONSTRAINT "Bonus Question_pkey" PRIMARY KEY ("BQID");


--
-- Name: Competition_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Competition"
    ADD CONSTRAINT "Competition_pkey" PRIMARY KEY ("CID");


--
-- Name: Current Competion_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Current Competion"
    ADD CONSTRAINT "Current Competion_pkey" PRIMARY KEY ("CID");


--
-- Name: Global Pick_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Global Pick"
    ADD CONSTRAINT "Global Pick_pkey" PRIMARY KEY ("UID", "QID");


--
-- Name: Global Question Option_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Global Question Option"
    ADD CONSTRAINT "Global Question Option_pkey" PRIMARY KEY ("QOID");


--
-- Name: Global Question_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Global Question"
    ADD CONSTRAINT "Global Question_pkey" PRIMARY KEY ("QID");


--
-- Name: Match_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Match"
    ADD CONSTRAINT "Match_pkey" PRIMARY KEY ("MID");


--
-- Name: Round_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Round"
    ADD CONSTRAINT "Round_pkey" PRIMARY KEY ("RID", "CID");


--
-- Name: Team in Competition_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Team in Competition"
    ADD CONSTRAINT "Team in Competition_pkey" PRIMARY KEY ("CID", "TID");


--
-- Name: Team_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "Team"
    ADD CONSTRAINT "Team_pkey" PRIMARY KEY (tid);


--
-- Name: User_pkey; Type: CONSTRAINT; Schema: public; Owner: alan; Tablespace: 
--

ALTER TABLE ONLY "User"
    ADD CONSTRAINT "User_pkey" PRIMARY KEY ("UID");


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;
GRANT ALL ON SCHEMA public TO melindas_ball;


--
-- Name: Match; Type: ACL; Schema: public; Owner: alan
--

REVOKE ALL ON TABLE "Match" FROM PUBLIC;
REVOKE ALL ON TABLE "Match" FROM alan;
GRANT ALL ON TABLE "Match" TO alan;
GRANT ALL ON TABLE "Match" TO melindas_ball;


--
-- Name: Team; Type: ACL; Schema: public; Owner: alan
--

REVOKE ALL ON TABLE "Team" FROM PUBLIC;
REVOKE ALL ON TABLE "Team" FROM alan;
GRANT ALL ON TABLE "Team" TO alan;
GRANT ALL ON TABLE "Team" TO melindas_ball;


--
-- PostgreSQL database dump complete
--

