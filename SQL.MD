### C

### CREATE DATABASE
```sql
CREATE DATABASE IF NOT EXISTS attendence_system_test;  
USE attendence_system_test;
```

```sql
CREATE TABLE IF NOT EXISTS boysblock1(ID varchar(255), NAME varchar(255),BLOCK varchar(255),ROOMNUM VARCHAR(255));
CREATE TABLE IF NOT EXISTS boysblock2(ID varchar(255), NAME varchar(255),BLOCK varchar(255),ROOMNUM VARCHAR(255));
CREATE TABLE IF NOT EXISTS boysblock3(ID varchar(255), NAME varchar(255),BLOCK varchar(255),ROOMNUM VARCHAR(255));
CREATE TABLE IF NOT EXISTS girlsblock1(ID varchar(255), NAME varchar(255),BLOCK varchar(255),ROOMNUM VARCHAR(255));

CREATE TABLE IF NOT EXISTS turnstile(ID varchar(255),Time varchar(255), Date varchar(255),Person_Group varchar(255),Attendance_Check_Point varchar(255),STATUS varchar(255));
CREATE TABLE IF NOT EXISTS leaves(ID varchar(255),STATUS varchar(255));

CREATE TABLE attendance (
    BLOCK VARCHAR(255),
    Name VARCHAR(255),
    Date DATE,
    ID VARCHAR(255),
    Leave_Status VARCHAR(255)
);
```


#### Add testing data
```sql

TRUNCATE TABLE boysblock3;
INSERT INTO attendence_system_test.boysblock3(ID, NAME, BLOCK,ROOMNUM)
VALUES
    ('21MIM10035', 'VAASU','BoysHostel3','B 304'),
    ('21BAS10131', 'ARYAN','BoysHostel3','B 304'),
    ('21BAI10131', 'RAMI','BoysHostel3','B 304'),
    ('21MIM10002', 'RAHUL','BoysHostel3','A232'),
    ('21MIM10033', 'VIRAT','BoysHostel3','A202'),
    ('21MIM10036','UTTHAN','BoysHostel3','A232');


TRUNCATE TABLE boysblock1;
INSERT INTO attendence_system_test.boysblock1(ID, NAME, BLOCK,ROOMNUM)
VALUES
    ('21MIM10010', 'ANAND','BoysHostel1','176'),
    ('21BAS10139', 'VANSH','BoysHostel1','220'),
    ('21MEI10000', 'ANAND','BoysHostel1','222'),
    ('21MIM10049', 'SIDDHART','BoysHostel1','625'),
    ('21MIM10026', 'PRANAV','BoysHostel1','625');


TRUNCATE TABLE boysblock2;
INSERT INTO attendence_system_test.boysblock2(ID, NAME, BLOCK,ROOMNUM)
VALUES
    ('20MIM10010', 'ABC','BoysHostel2','A 176'),
    ('20BAS10139', 'XYQ','BoysHostel2','A 220'),
    ('20MEI10000', 'DSF','BoysHostel2','B 222'),
    ('20MIM10049', 'LKJ','BoysHostel2','C 625'),
    ('20MIM10026', 'CDS','BoysHostel2','D 625');


TRUNCATE TABLE turnstile;
INSERT INTO attendence_system_test.turnstile(ID,Time,Date,Person_Group,Attendance_Check_Point)
VALUES
	('21MIM10035','07:25','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('21MIM10035','08:25','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21BAS10131','07:35','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('21BAS10131','07:15','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21MIM10002','19:25','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21MIM10002','12:25','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21MIM10002','22:25','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('21MIM10002','08:25','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('21MIM10002','15:25','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21BAI10131','16:25','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('21BAI10131','09:25','21/07/2023','All Persons/BLOCK 2','BHBL02-FRT-ENTRY01-Cardreader 01');

TRUNCATE TABLE leaves;
INSERT INTO attendence_system_test.leaves(ID, STATUS)
VALUES
    ('21MIM10036', 'ON LEAVE'),
	('21MIM10002', 'ON LEAVE'),
    ('21BAS10131', 'REPORTED');
```

# MAIN QUERY
```sql
DECLARE @date DATE;
SET @date = CURRENT_DATE;

INSERT INTO attendance (
    BLOCK,
    Name,
    Date,
    ID,
    Leave_Status
)
SELECT
    m.BLOCK,
    m.Name,
    DATE(@date),
    m.ID,
    COALESCE(l.STATUS, CASE WHEN latest_turnstile.STATUS IS NOT NULL THEN latest_turnstile.STATUS ELSE NULL END, 'ENTRY')
FROM boysBlock3 AS m
LEFT JOIN (
    SELECT t.ID,
           CASE
               WHEN t.Attendance_Check_Point LIKE '%EXIT%' THEN 'ABSENT'
               ELSE 'PRESENT'
           END AS STATUS
    FROM turnstile AS t
    INNER JOIN (
        SELECT ID, MAX(Time) AS LatestTime
        FROM turnstile
        GROUP BY ID
    ) AS latest_time
    ON t.ID = latest_time.ID AND t.Time = latest_time.LatestTime
) AS latest_turnstile
ON m.ID = latest_turnstile.ID
LEFT JOIN leaves AS l ON m.ID = l.ID;
```
