<?php

function fetch_file_list( $pPath, $pRecursive = false ) {

	$lResult = Array();

	$pPath = realpath( $pPath );

	//-- Search the given directory
	$lPathContents = scandir( $pPath );

	echo "Scanning : ".$pPath." - ".count($lPathContents)."\n";

	foreach( $lPathContents as $lFile ) {

		//-- Exclude the '.' and '..' stuff
		if ( ($lFile == '.') || ($lFile == '..') ) {
			continue;
		}

		$lFile = realpath ( $pPath ).DIRECTORY_SEPARATOR.$lFile ;
		//$lFile = str_ireplace( '/', '\\', $lFile);

		//-- Is is a file ?
		if ( is_file( $lFile ) ) {
			//-- Get is extension
			$lFileInfo = pathinfo( $lFile );

			switch( strtoupper( $lFileInfo['extension'] ) ) {
				case 'JPG':
				case 'THM':
				case 'CR2':
					//-- Try to get the information we need
					$lExifInfo = read_exif_data_raw( $lFile, false );

					// Is it a valid JPEG (acording to mister Exifer ? )
					if( $lExifInfo['ValidEXIFData'] ) {
						$lResult[] = Array( 'path' => $lFile, 'exif' => $lExifInfo );
					}

					//-- One small extra thing
					if ( strtoupper( $lFileInfo['extension'] ) == 'THM' ) {
						//-- Let's find the pair file on out current list of file
						for( $i = 0; $i < count( $lPathContents ); ++$i ) {
							$lSearchFileInfo = pathinfo( $lPathContents[$i] );

							//echo "\t".stristr( $lSearchFileInfo['basename'], basename( $lFile, '.THM' ) )."  |  ".$lSearchFileInfo['extension']."\n";

							if ( stristr( $lSearchFileInfo['basename'], basename( $lFile, '.THM' ) ) && ( $lSearchFileInfo['extension'] != 'THM') ) {
								$lResult[] = Array( 'path' => dirname( $lFile ).DIRECTORY_SEPARATOR.basename( $lFile, '.THM' ).'.'.$lSearchFileInfo['extension'], 'exif' => $lExifInfo );
							}
						}
					}
			}
		} elseif ( is_dir( $lFile ) ) {

			if ( $pRecursive ) {
				$lResult = array_merge( $lResult, fetch_file_list( $lFile, $pRecursive ) );
			}
		}
	}

	return $lResult;
}
?>
