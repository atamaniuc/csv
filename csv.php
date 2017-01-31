<?php
/**
 * Write a piece of software that will look for all .csv files
 * under a specific path in a Linux based environment and will delete lines
 * having both value in column 2 equals value of given CLI argument #1 AND column 5 equals value of given CLI argument #2
 * code provided can be written in the language or scripting language of your choice.
 */

// Entry Point
if ( count( $argv ) > 1 ) { // arguments were provided
	$directory         = getArgumentValueByName( 'directory', $argv );
	$recursive         = getArgumentValueByName( 'recursive', $argv );
	$secondColumnValue = getArgumentValueByName( 'second_column_value', $argv );
	$fifthColumnValue  = getArgumentValueByName( 'fifth_column_value', $argv );

	if ( $directory ) {
		$directoryIterator     = $recursive
			? new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $directory ) )
			: new DirectoryIterator( $directory );
		$filesWithDeletedLines = [];
		// loop trough directory items
		foreach ( $directoryIterator as $file ) {
			// skipping folders and unnecessary file extensions
			if ( $file->isDir() || $file->getExtension() !== 'csv' ) {
				continue;
			}
			// open csv-file as array
			$lines = file( $file->getPathname(), FILE_IGNORE_NEW_LINES );
			// the number of lines found
			$matches = 0;
			// loop trough lines
			foreach ( $lines as $index => $line ) {
				$rows = str_getcsv( $line );
				// delete lines by selected criteria
				if ( isset( $rows[1], $rows[4] ) && $rows[1] === $secondColumnValue && $rows[4] === $fifthColumnValue ) {
					unset( $lines[ $index ] );
					$matches ++;
					$filesWithDeletedLines[] = $file->getPathname();
				}
			}
			// Rewrite the file if values has been successfully searched
			if ( $matches ) {
				file_put_contents( $file->getPathname(), implode( "\n", $lines ) );
			}
		}
		// print list of files with deleted liens
		if ( count( $filesWithDeletedLines ) ) {
			$filesWithDeletedLines = implode( "\n", array_unique( $filesWithDeletedLines ) );
			echo "Lines from files: \n" . $filesWithDeletedLines . "\nhas been deleted !";
		}
	}
} else { // no arguments were provided - print script usage info
	echo "Script finds and removes rows from .csv files having both value in column 2 equals value of second_column_value " .
	     "AND column 5 equals value of given fifth_column_value\n" .
	     "\nArguments usage:\n\n--directory=/var/www/test/files - Directory with .csv files" .
	     "\n--recursive=true (default: false)" .
	     "\n--second_column_value=exampleVal1 - value to search in first column" .
	     "\n--fifth_column_value=exampleVal2 - value to search in fifth column\n\n Example:\n\e[32m php csv.php --directory=/var/www/test/files " .
	     "--recursive=true --second_column_value=89031883088 --fifth_column_value=112 \033[37m\r\n";
}

/**
 * Parse argument's value from $argv by argument's name
 *
 * @param string $argumentName
 * @param array $argv
 *
 * @return bool|string
 */
function getArgumentValueByName( $argumentName, array $argv ) {
	$argumentValue = '';
	// loop trough arguments list
	foreach ( $argv as $index => $arg ) {
		// skipping csv.php filename
		if ( $index === 0 ) {
			continue;
		}
		// check if argument exists
		if ( strpos( $arg, "--{$argumentName}=" ) !== false ) {
			$argumentValue = explode( '=', $arg )[1];
			// arguments validation
			if ( $argumentValue ) {
				switch ( $argumentName ) {
					case 'directory':
						$argumentValue = is_dir( $argumentValue ) ? $argumentValue : '';
						break;
					case 'recursive':
						$argumentValue = $argumentValue === 'true';
						break;
					case 'second_column_value':
					case 'fifth_column_value':
						$argumentValue = $argumentValue ?: '';
						break;
				}
			}
		}
	}

	return $argumentValue;
}