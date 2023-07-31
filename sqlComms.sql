TRUNCATE b1master;
TRUNCATE b2master;
TRUNCATE b3master;
TRUNCATE ghmaster;
TRUNCATE onleave;


SET @tables = NULL;
SELECT GROUP_CONCAT(table_schema, '.', table_name, ' ') INTO @tables FROM
(select * from
 information_schema.tables 
  WHERE table_schema = 'hostel_attendance' AND  (table_name LIKE 'report%' OR table_name LIKE 'turnstile%' OR table_name LIKE 'newentry%')
  LIMIT 10) TT;

SET @tables = CONCAT('DROP TABLE ', @tables);
select @tables;
PREPARE stmt1 FROM @tables;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;


INSERT INTO hostel_attendance.b1master(ID, NAME)
VALUES
    ('21MIM10010','ANAND'),
    ('21MIM10039','VANSH'),
    ('21MIM10007','AARAV'),
    ('21MIM10049','SIDDHART'),
    ('21MIM10035','VAASU'),
    ('21MIM10026','PRANAV');

INSERT INTO hostel_attendance.turnstileb131072023(ID,Name,Time,Date,Attendance_Check_Point)
VALUES
	('21MIM10035','abc','07:25','21/07/2023','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('21MIM10035','abc','08:25','21/07/2023','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21MIM10039','abc','07:35','21/07/2023','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('21MIM10039','abc','07:15','21/07/2023','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21MIM10007','abc','19:25','21/07/2023','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21MIM10007','abc','12:25','21/07/2023','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21MIM10007','abc','22:25','21/07/2023','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('21MIM10007','abc','08:25','21/07/2023','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('21MIM10007','abc','15:25','21/07/2023','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21MIM10026','abc','16:25','21/07/2023','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('21MIM10026','abc','09:25','21/07/2023','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('NEWENTRY_P','abc','16:25','21/07/2023','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('NEWENTRY_P','abc','06:25','21/07/2023','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('NEWENTRY_A','abc','06:25','21/07/2023','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('NEWENTRY_A','abc','19:25','21/07/2023','BHBL02-FRT-EXIT02-Cardreader 01'),
    ('NEWENTRY_A','abc','16:25','21/07/2023','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('NEWENTRY_L','abc','11:25','21/07/2023','BHBL02-FRT-ENTRY01-Cardreader 01'),
    ('NEWENTRY_L','abc','14:25','21/07/2023','BHBL02-FRT-EXIT02-Cardreader 01');


INSERT INTO hostel_attendance.onleave(ID, STATUS)
VALUES
    ('21MIM10035','LEAVE'),
    ('21MIM10039','REPORTED'),
    ('NEWENTRY_L','LEAVE');