<?php

/**
 * SQL Backup Splitter
 *
 * This script reads a large SQL backup file and splits it into individual
 * files for each table, prefixing each new file with a 16-line header
 * from the original file.
 */

// --- CONFIGURATION ---

// The main file to read from
$inputFile = 'backup.sql';

// The directory to write new SQL files to
$outputDir = './sql_split/';

// The number of lines from the top of the input file to use as a header
$headerLines = 16;

// The string that triggers a new file split
$triggerString = '-- Table structure for table';

// ---------------------

echo "Starting SQL backup split...\n";

// --- Step 1: Ensure output directory exists ---
if ( ! is_dir( $outputDir ) ) {
    if ( ! mkdir( $outputDir, 0755, true ) ) {
        die( "Error: Could not create output directory: $outputDir\n" );
    }
    echo "Created output directory: $outputDir\n";
}

// --- Step 2: Open the input file ---
$fileHandle = @fopen( $inputFile, 'r' );
if ( ! $fileHandle ) {
    die( "Error: Could not open input file: $inputFile\n" );
}
echo "Opened input file: $inputFile\n";

// --- Step 3: Read and store the header ---
$headerContent = '';
for ( $i = 0; $i < $headerLines; $i++ ) {
    $line = fgets( $fileHandle );
    if ( $line === false ) {
        break; // Stop if file is shorter than 16 lines
    }
    $headerContent .= $line;
}
echo "Header content (first $headerLines lines) has been captured.\n";

// --- Step 4: Process the rest of the file ---
$currentFileHandle = null;
$currentTableName  = '';
$fileCount         = 0;

while ( ( $line = fgets( $fileHandle ) ) !== false ) {
    
    // Check if the line is our trigger (must be at the *start* of the line)
    if ( strpos( $line, $triggerString ) === 0 ) {
        
        // 1. Close the previous file, if one is open
        if ( $currentFileHandle ) {
            fclose( $currentFileHandle );
            echo " -> Finished table: $currentTableName\n";
        }

        // 2. Extract the new table name
        // This regex finds the content between the first pair of backticks
        if ( preg_match( '/`([^`]+)`/', $line, $matches ) ) {
            $currentTableName = $matches[1];
            $outputFilename   = $outputDir . $currentTableName . '.sql';
            
            // 3. Open the new file for writing
            $currentFileHandle = @fopen( $outputFilename, 'w' );
            if ( ! $currentFileHandle ) {
                echo "Error: Could not create new file: $outputFilename\n";
                // Keep reading, but we won't be able to write
                $currentFileHandle = null; 
                continue;
            }
            
            $fileCount++;
            echo "($fileCount) Creating new file: $outputFilename\n";

            // 4. Write the captured header to the new file
            fwrite( $currentFileHandle, $headerContent );

        } else {
            // Couldn't parse a table name, so don't write this line
            echo "Warning: Found trigger string but could not parse table name in line: $line";
            $currentFileHandle = null; // Stop writing until next valid trigger
        }
    }

    // 5. Write the current line to the open file
    // (This includes the trigger line itself, right after the header)
    if ( $currentFileHandle ) {
        fwrite( $currentFileHandle, $line );
    }
}

// --- Step 5: Clean up ---
if ( $currentFileHandle ) {
    fclose( $currentFileHandle );
    echo " -> Finished last table: $currentTableName\n";
}

fclose( $fileHandle );

echo "\n--- Split Complete ---\n";
echo "Total files created: $fileCount\n";

?>