<?php

include('../config.inc');
include('../utils.php');

//
// Customization section:
//	Set these values appropriately for your environment
//

// Location of the user data and result files:
//	they should be WWW accessible, therefore we need
//	to specify their location relative to the system /
//	and to the www root (for the URLs):
$system_wwwroot = "/data/www/EMBnet";
$http_tmproot = "/tmp";
$system_tmproot = $system_wwwroot . $http_tmproot;
$datafile = "RBDB.dat";

// set to 0 for no debug output, or select a debug level
$debug = 0;

//
// End of configuration section
//


/**
 * Create working directory and move to it
 *
 *  The goal is to go to the working directory. If it does not
 * exist, we create it (it shouldn't) and move inside.
 *
 *  Ideally we would also create an .htaccess file and a .htpasswd
 * with a random password to return to the user. Should that be done
 * here?
 *
 * @note The working directory should not exist!
 *
 * @param $user_wd_path the _absolute_ path to the local directory where 
 *  	    	we will be storing user data.
 */
function go_to_work($user_wd_path, $options)
{
	
	// create working directory in the local hierarchy
	if (!mkdir("$user_wd_path", 0750))
	{
		echo "ERROR, HORROR: cannot generate a working directory<br /></body></html>\n";
		exit;
	}
	// mkdir seems to not handle properly the permissions
	chmod( "$user_wd_path", 0750 );
	
	// copy over our toolbox to the workspace
	if (! copy("./db_update.sh", "$user_wd_path/db_update.sh")) {
	    echo "<h1>ERROR, HORROR: can't copy toolbox to workspace</h1>";
	    exit;
	}
	chmod("$user_wd_path/db_update.sh", 0750);

    	// and go there
    	chdir("$user_wd_path");	
}

//
// Get the DATA file
//
function upload_data_file($upfile)
{
    $userfile = $_FILES['infile']['tmp_name'];
    $userfile_name = $_FILES['infile']['name'];
    $userfile_size = $_FILES['infile']['size'];
    
    if ($_FILES['infile']['tmp_name']=="none" || 
    	$_FILES['infile']['tmp_name']=="")
    {
    	    echo "<h1>Problem: no file uploaded</h1>";
    	    exit;
    }

    if ($_FILES['infile']['size']==0)
    {
    	    echo "<h1>Problem: uploaded file has zero length</h1>";
    	    exit;
    }
    
    if ( !move_uploaded_file($userfile, $upfile)) 
    {
    	echo "<h1>Problem: Could not move file $userfile to $upfile</h1>"; 
    	exit;
    }
}

//
// Start processing
//

echo "<HTML>\n<HEAD><title>RBDB Update</title></HEAD>\n<BODY bgcolor=\"#ddddff\">\n";

echo "<CENTER><H1>RBDB UPDATE</H1></CENTER>\n";

// select a random name for the tmp dir and uploaded file
$random_str = rand(1000000, 9999999);
$work_dir = $system_tmproot . "/rbdb-upd-$random_str";
$upfile = $work_dir . "/" . $datafile;

// create workspace and go there carrying with us our toolbox
go_to_work($work_dir);

// we are now on $work_dir. Let us get the data
// upload CSV database file
// We need a hard-coded filename so the script knows its name.
upload_data_file($upfile);

// do the work
echo "<hr>\n<table border=\"1\">\n<tr><th>Update results</th></tr>\n<tr><td><pre>\n";

// XXX JR XXX -- SECURITY THREAT --
// Yes, I know, this is a security threat if not run on a secure server.
// It would be better to execute all commands directly and finally pipe
// the parameters to mysql...
passthru("$work_dir/db_update.sh $db_host $db_user $db_password $upfile", $status);

echo "</pre></td></tr>\n";
echo "<tr><td><strong>Status: ";
if ($status == 0) 
    echo "OK";
else
    echo "Failed</strong></td></tr></table>\n<hr>";

// should output a footer
exit;

?>
