-- Read-only check for ODPPMES deletion foreign keys.
-- Run against odppmes_db after a reviewed migration. Do not edit these statements into UPDATE or DELETE.
-- Expected after 2026_10_17_091500_restrict_protected_deletion_foreign_keys:
--   RESTRICT: projects.mda_id, project_mda.mda_id,
--             monitorings.project_id, monitorings.user_id,
--             project_documents.project_id, project_documents.uploaded_by,
--             project_photos.project_id, project_photos.uploaded_by
--   SET NULL: users.mda_id, audit_logs.user_id, projects.development_partner_id
--   CASCADE:  project_mda.project_id

SELECT
    rc.TABLE_NAME,
    kcu.COLUMN_NAME,
    rc.CONSTRAINT_NAME,
    rc.REFERENCED_TABLE_NAME,
    kcu.REFERENCED_COLUMN_NAME,
    rc.DELETE_RULE,
    rc.UPDATE_RULE,
    t.ENGINE
FROM information_schema.REFERENTIAL_CONSTRAINTS rc
JOIN information_schema.KEY_COLUMN_USAGE kcu
    ON kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
   AND kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
   AND kcu.TABLE_NAME = rc.TABLE_NAME
JOIN information_schema.TABLES t
    ON t.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA
   AND t.TABLE_NAME = rc.TABLE_NAME
WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
ORDER BY rc.TABLE_NAME, kcu.COLUMN_NAME;

-- Existing rows must already satisfy the keys. A non-zero count means
-- the restrictive migration must not be applied until the row is repaired.
SELECT 'projects.mda_id' AS relationship, COUNT(*) AS orphan_rows
FROM projects p LEFT JOIN mdas m ON m.id = p.mda_id WHERE m.id IS NULL
UNION ALL
SELECT 'projects.development_partner_id', COUNT(*)
FROM projects p LEFT JOIN development_partners d ON d.id = p.development_partner_id
WHERE p.development_partner_id IS NOT NULL AND d.id IS NULL
UNION ALL
SELECT 'project_mda.project_id', COUNT(*)
FROM project_mda x LEFT JOIN projects p ON p.id = x.project_id WHERE p.id IS NULL
UNION ALL
SELECT 'project_mda.mda_id', COUNT(*)
FROM project_mda x LEFT JOIN mdas m ON m.id = x.mda_id WHERE m.id IS NULL
UNION ALL
SELECT 'monitorings.project_id', COUNT(*)
FROM monitorings x LEFT JOIN projects p ON p.id = x.project_id WHERE p.id IS NULL
UNION ALL
SELECT 'monitorings.user_id', COUNT(*)
FROM monitorings x LEFT JOIN users u ON u.id = x.user_id WHERE u.id IS NULL
UNION ALL
SELECT 'project_documents.project_id', COUNT(*)
FROM project_documents x LEFT JOIN projects p ON p.id = x.project_id WHERE p.id IS NULL
UNION ALL
SELECT 'project_documents.uploaded_by', COUNT(*)
FROM project_documents x LEFT JOIN users u ON u.id = x.uploaded_by WHERE u.id IS NULL
UNION ALL
SELECT 'project_photos.project_id', COUNT(*)
FROM project_photos x LEFT JOIN projects p ON p.id = x.project_id WHERE p.id IS NULL
UNION ALL
SELECT 'project_photos.uploaded_by', COUNT(*)
FROM project_photos x LEFT JOIN users u ON u.id = x.uploaded_by WHERE u.id IS NULL
UNION ALL
SELECT 'users.mda_id', COUNT(*)
FROM users u LEFT JOIN mdas m ON m.id = u.mda_id
WHERE u.mda_id IS NOT NULL AND m.id IS NULL
UNION ALL
SELECT 'audit_logs.user_id', COUNT(*)
FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id
WHERE a.user_id IS NOT NULL AND u.id IS NULL;
