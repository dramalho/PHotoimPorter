<?php


//*********************************************************************
//** Instructions
//**
//** Script that organizes photo files acording to their EXIF tags
//**
//** -h --help			Help screen
//** -r --recursive		Searches sub-directories for image files
//** -s --source		Source path
//** -d --destination	Destination Path
//**
//**********************************************************************

//-- Setup stuff -------------------------------------------------------

$lStrings['help'] = <<<EOS
**********************************************************************
** Script that organizes photo files acording to their EXIF tags
**
** -h --help			Help screen
** -r --recursive		Searches sub-directories for image files
** -s --source		Source path
** -d --destination	Destination Path
**
** Author : David Ramalho <dramalho@gmail.com>
**
**********************************************************************
EOS;

$lSettings['recursive'] = false;
$lSettings['source']	= './';
$lSettings['destination'] = './';

//--
//----------------------------------------------------------------------
//-- Analise the command options
if ( $_SERVER['argc'] <= 1 ) {	//-- No arguments were passed
	echo $lStrings['help'];
	exit(0);
} else {
	//-- Get rid of first argument
	array_shift( $_SERVER['argv'] );

	//-- Go through the rest of them
	while( $lArgument = array_shift( $_SERVER['argv'] ) ) {
		switch( $lArgument ) {
			//-------------------------
			//-- Help argument
			//--
			//-- No further data
			case '-h':
			case '--help':
				echo $lStrings['help'];
				exit(0);
				break;
			//-------------------------
			//-- Recursive flag
			//--
			//-- No further data
			case '-r':
			case '--recursive':
				$lSettings['recursive'] = true;
				break;
			//-------------------------
			//-- Source argument
			//--
			//-- source path
			case '-s':
			case '--source':
				$lSettings['source'] = array_shift( $_SERVER['argv'] );
				break;
			//-------------------------
			//-- Destination argument
			//--
			//-- Destination path
			case '-d':
			case '--destination':
				$lSettings['destination'] = array_shift( $_SERVER['argv'] );
				break;
		}
	}
}

//--
//----------------------------------------------------------------------
//-- Check out the settings

//-- Is the source directory valid?
if ( !is_dir( $lSettings['source'] ) ) {
	echo $lStrings['help'];
	echo "\n\nError: Invalid source directory.";
	exit(10);
}

//-- Is the destination directory valid?
if ( !is_dir( $lSettings['destination'] ) ) {
	echo $lStrings['help'];
	echo "\n\nError: Invalid destination directory.";
	exit(10);
}

//--
//----------------------------------------------------------------------
//-- Perform magic please

//-- We need the exifer lib, please ;)
if ( (!@include './lib/exifer1_5/exif.php') || (!@include './lib/PI_functions.php') ) {
	echo "Error : Can't find some VIF (very important files) .. what's up?";
	echo(20);
}

//-- Status message
echo "Scanning source directory...\n";

//-- Search the files we know (this will grow with time, promisse ;) )
$lResult = fetch_file_list( $lSettings['source'], $lSettings['recursive'] );

//-- Work on the results
if ( count( $lResult ) == 0 ) {
	//-- Status message
	echo "No suitable files found ... maybe next time huh?";
	exit(0);
} else {
	echo "Found ".count( $lResult )." files ... going into transfer mode\n";
	$lProblemCounter = 0;

	foreach( $lResult as $lFile ) {
		$lPathComponent = Array();
		//var_dump( $lFile );
		$lPathComponent[] = date( 'Y-m-d', strtotime( $lFile['exif']['DateTime'] ) );
		$lPathComponent[] = trim( $lFile['exif']['Make'] );
		$lPathComponent[] = trim( $lFile['exif']['Model'] );

		$lDestinationPath = realpath( $lSettings['destination'] ).DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $lPathComponent).DIRECTORY_SEPARATOR.basename( $lFile['path'] );
		//$lDestinationPath = str_ireplace( '/', '\\', $lDestinationPath);

		@mkdir( dirname( $lDestinationPath ), 0777, true );
		//-- Status Message
		echo $lDestinationPath;
		if ( !@copy( $lFile['path'], $lDestinationPath ) ) {
			echo " [problem]";
			++$lProblemCounter;
		} else {
			echo " [ok]";
		}
		echo "\n";

		//echo "I'll put this file (".$lFile['path'].") into (".realpath( $lSettings['destination'] ).'/'.implode('/', $lPathComponent).'/'.basename( $lFile['path'] ).")\n";
	}
}

//-- Status Message
echo "My work is done, thank you for your time\n";
echo "Problems found: ".$lProblemCounter;
?>
