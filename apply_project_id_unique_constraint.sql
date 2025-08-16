-- Add unique constraint to project_id in projects table
-- This ensures that project_id is unique across all projects at the database level

-- Check if constraint already exists (PostgreSQL)
SELECT constraint_name 
FROM information_schema.table_constraints 
WHERE table_name = 'projects' 
AND constraint_name = 'uk_projects_project_id';

-- If the above query returns no rows, then add the constraint:
ALTER TABLE projects ADD CONSTRAINT uk_projects_project_id UNIQUE (project_id);

-- Create index for better performance
CREATE INDEX IF NOT EXISTS idx_projects_project_id ON projects(project_id);

-- For MySQL (if using MySQL instead):
-- ALTER TABLE projects ADD UNIQUE KEY uk_projects_project_id (project_id);