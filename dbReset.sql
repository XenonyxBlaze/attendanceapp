TRUNCATE b1master;
TRUNCATE b2master;
TRUNCATE b3master;
TRUNCATE ghmaster;
TRUNCATE onleave;


SET @tables = NULL;
SELECT GROUP_CONCAT(table_schema, '.`', table_name, '`') INTO @tables FROM
(select * from
 information_schema.tables 
  WHERE table_schema = 'myDatabase' AND  (table_name LIKE 'report%' OR table_name LIKE 'turnstile%')
  LIMIT 10) TT;

SET @tables = CONCAT('DROP TABLE ', @tables);
select @tables;
PREPARE stmt1 FROM @tables;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;