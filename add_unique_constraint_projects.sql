-- Add unique constraint to project_id in projects table
-- This script ensures that project_id is unique across all projects

-- For PostgreSQL
-- First, check if there are any duplicate project_id values
-- SELECT project_id, COUNT(*) FROM projects GROUP BY project_id HAVING COUNT(*) > 1;

-- Add unique constraint to project_id
ALTER TABLE projects ADD CONSTRAINT uk_projects_project_id UNIQUE (project_id);

-- For MySQL (if using MySQL instead)
-- ALTER TABLE projects ADD UNIQUE KEY uk_projects_project_id (project_id);

-- Create index for better performance (if not exists)
CREATE INDEX IF NOT EXISTS idx_projects_project_id ON projects(project_id);

-- Verify the constraint was added
-- SELECT constraint_name, constraint_type FROM information_schema.table_constraints 
-- WHERE table_name = 'projects' AND constraint_type = 'UNIQUE';
