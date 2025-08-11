# Migration Guide: Add Cancel Status to Activity List

## Overview
This guide explains how to resolve the database error when trying to use the new "Cancel" status in the Activity List.

## Error Description
```
Fatal error: Uncaught PDOException: SQLSTATE[22P02]: Invalid text representation: 7 
ERROR: invalid input value for enum activity_status: "Cancel"
```

This error occurs because the database ENUM type `activity_status` doesn't yet include the "Cancel" value.

## Solution Options

### Option 1: Run Migration Script (Recommended)
Use the automated migration script that will detect your database type and run the appropriate migration.

1. **Update database configuration** in `run_migration.php`:
   ```php
   $host = 'localhost';
   $dbname = 'your_database_name';  // Change this
   $username = 'your_username';      // Change this  
   $password = 'your_password';      // Change this
   ```

2. **Run the migration script**:
   ```bash
   php run_migration.php
   ```

3. **Verify the migration**:
   The script will show the current ENUM values after migration.

### Option 2: Manual PostgreSQL Migration
If you prefer to run the migration manually in PostgreSQL:

```sql
-- Connect to your PostgreSQL database and run:
ALTER TYPE activity_status ADD VALUE 'Cancel';

-- Verify the change:
SELECT enumlabel FROM pg_enum 
WHERE enumtypid = (SELECT oid FROM pg_type WHERE typname = 'activity_status') 
ORDER BY enumsortorder;
```

### Option 3: Manual MySQL Migration
If you're using MySQL:

```sql
-- Connect to your MySQL database and run:
ALTER TABLE activities MODIFY COLUMN status 
ENUM('Open', 'On Progress', 'Need Requirement', 'Done', 'Cancel');

-- Verify the change:
SHOW COLUMNS FROM activities LIKE 'status';
```

## Migration Files Created

1. **`migration_add_cancel_status.sql`** - PostgreSQL migration script
2. **`migration_add_cancel_status_mysql.sql`** - MySQL migration script  
3. **`run_migration.php`** - Automated migration script (detects database type)
4. **`README_MIGRATION.md`** - This guide

## Troubleshooting

### Issue: "Value already exists" error
**Solution**: This means the migration has already been run. You can safely ignore this error.

### Issue: Permission denied
**Solution**: Make sure your database user has ALTER privileges on the database.

### Issue: Alternative method needed for older PostgreSQL
**Solution**: The automated script will try alternative methods for older PostgreSQL versions.

### Issue: Connection failed
**Solution**: Check your database credentials and connection settings in the migration script.

## Verification

After running the migration, you should see:
- ✅ Status "Cancel" appears in all status dropdowns
- ✅ Filter "Active (Default)" works correctly (excludes Done and Cancel)
- ✅ Badge styling for "Cancel" status displays correctly
- ✅ No more database errors when using "Cancel" status

## Rollback (If Needed)

If you need to remove the "Cancel" status:

**PostgreSQL**:
```sql
-- Note: Removing ENUM values is complex in PostgreSQL
-- Consider recreating the ENUM type without 'Cancel'
```

**MySQL**:
```sql
ALTER TABLE activities MODIFY COLUMN status 
ENUM('Open', 'On Progress', 'Need Requirement', 'Done');
```

## Support

If you encounter issues:
1. Check the error messages from the migration script
2. Verify your database credentials
3. Ensure your database user has sufficient privileges
4. Check if the migration has already been run

## Next Steps

After successful migration:
1. Refresh your Activity List page
2. Test creating an activity with "Cancel" status
3. Test filtering by "Cancel" status
4. Verify the badge styling displays correctly

The "Cancel" status should now work properly in your Activity List! 🎉
