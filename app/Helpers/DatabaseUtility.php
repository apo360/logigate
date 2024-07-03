<?

// app/Utilities/DatabaseUtility.php

namespace App\Utilities;

use Illuminate\Support\Facades\DB;

class DatabaseUtility
{
    public static function exportData($tableName)
    {
        // Export data from the specified table
        $data = DB::table($tableName)->get();

        // You can process and format the data as needed here

        return $data;
    }

    public static function importData($tableName, $data)
    {
        // Truncate the table to remove existing data
        DB::table($tableName)->truncate();

        // Insert the new data into the table
        DB::table($tableName)->insert($data);

        // You can add error handling or validation as needed

        return true; // Indicate success
    }
}
