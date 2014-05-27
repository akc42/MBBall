BEGIN EXCLUSIVE;

ALTER TABLE round ADD bvalue smallint DEFAULT 1 NOT NULL;

UPDATE round SET bvalue = 2,results_cache = NULL WHERE cid = 11;
UPDATE competition SET results_cache = NULL WHERE cid = 11;



DROP VIEW bonus_score;
CREATE VIEW bonus_score AS
    SELECT r.cid,r.rid, u.uid, (CASE WHEN p.uid IS NULL THEN 0 ELSE 1 END * r.bvalue) AS score
	FROM ((registration u JOIN round r USING(cid) )
	LEFT JOIN option_pick p ON ((((p.cid = r.cid) AND (p.rid = r.rid) AND (p.uid = u.uid) AND (p.opid = r.answer)) AND (r.valid_question = 1))))
	WHERE r.open = 1 ;
	

COMMIT;
