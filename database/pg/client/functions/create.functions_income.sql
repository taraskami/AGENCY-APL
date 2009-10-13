CREATE OR REPLACE FUNCTION income_verify() RETURNS TRIGGER AS '
DECLARE
	iid INTEGER;
BEGIN
	SELECT INTO iid income_id FROM income WHERE income.client_id=NEW.client_id AND income.income_date_end IS NULL;
	IF iid IS NOT NULL THEN
		RAISE EXCEPTION ''Client % has a current income record (income_id: %).'',NEW.client_id, iid;
	END IF;	
	RETURN NEW;
END;' LANGUAGE 'PLPGSQL';

CREATE TRIGGER verify_income_record  BEFORE INSERT
    ON tbl_income FOR EACH ROW
    EXECUTE PROCEDURE income_verify();

