INSERT IGNORE INTO activity_info (id, source_id, title, activity_type, description, date, time, end_time, price, venue, table_name, image, status)
SELECT id, source_id, title, activity_type, description, date, time, end_time, price, venue, table_name, image, status
FROM activity_table WHERE record_type = 'event';

DELETE FROM activity_table WHERE id = 21 AND record_type = 'registration' AND email = 'wdawda@fefsef.com' AND activity_id IS NULL;

DELETE FROM activity_table WHERE record_type = 'event';

ALTER TABLE activity_table
    DROP COLUMN record_type,
    DROP COLUMN source_id,
    DROP COLUMN title,
    DROP COLUMN activity_type,
    DROP COLUMN description,
    DROP COLUMN end_time,
    DROP COLUMN price,
    DROP COLUMN venue,
    DROP COLUMN table_name,
    DROP COLUMN image;
