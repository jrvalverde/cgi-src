<?php
//
// Customization section:
//
//      Set these values appropriately for your environment
//

$app_name='Glimmer 1.02';
$app_dir = '/Services/MolBio/glimmer1';
$maintainer='netadmin@es.embnet.org';

/**
 * Base system location of the user data and result files
 *
 *      they should be WWW accessible, but we need
 *      to specify their location relative to the system /
 *      while doing internal processing.
 */
$system_tmp_root = '/data/www/EMBnet/tmp';

/**
 * Base WWW location of user data and result files:
 *
 *      This is the root used to generate the URLs that the
 *      user will access to see his results.
 *
 * @global string http_tmproot
 */
$http_tmp_root = '/tmp';

/// set to 0 for no debug output, or select a debug level
$debug=0;

/**
 * Location of Glimmer-1.02 run script
 *
 * @global string distdna
 */
$driver = '/opt/genomics/glimmer1.02/run-glimmer';


//
// End of configuration section
//

/**
 * Start the display of a www page
 *
 * We have it as a function so we can customise all pages generated as
 * needed. This routine will open HTML, create the page header, and 
 * include any needed style sheets (if any) to provide a common 
 * look-and-feel for all pages generated.
 *
 *  @param integer $reload  number of seconds until next reload (0 = no reload)
 */
function set_header($reload, $session_id)
{
    global $app_name, $app_dir, $http_tmp_root;

    $my_url="http://"$_SERVER['SERVER_NAME']."/".$_SERVER['PHP_SELF'];

    // Start HTML vx.xx output
    echo "<html>";
    // Print headers
    echo "<head>\n";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\">\n";
    echo "<meta name=\"description\" content=\"a web interface to run Glimmer\">\n";
    echo "<meta name=\"author\" content=\"EMBnet/CNB\">\n";
    echo "<meta name=\"copyright\" content=\"(c) 2006 by CSIC - Open Source Software\">\n";
    echo "<meta name=\"generator\" content=\"$app_name\">\n";
    if ($reload > 0)
    	echo "<meta http-equiv=\"Refresh\" content=\"$reload\" ".
	"url=\"$my_url?sess=$session_id\">\n";
    echo "<link rel=\"stylesheet\" href=\"$app_dir/style/style.css\" type=\"text/css\"/>\n";
    echo "<link rel=\"shortcut icon\" href=\"$app_dir/img/favicon.ico\"/>\n";
    echo "<title=\"$app_name\">\n";
    echo "</head>";
    // Prepare body
    echo "<body bgcolor=\"white\" background=\"$app_dir/img/background_42.jpg\" link=\"darkblue\" VLINK=\"blue\" ALINK=\"#4682b4\">\n";
}

/**
 * close a web page
 *
 * Make sure we end the page with all the appropriate formulisms:
 * close the body, include copyright notice, state creator and
 * any needed details, and close the page.
 */
function set_footer()
{
	global $maintainer, $app_dir;
	// close body
	echo "</body><hr>";
	// footer
	echo "<center><table border=\"0\" width=\"90%\"><tr>";
	    // Copyright and author
	    echo "<td><a href=\"$app_dir/c/gpl.txt\">&copy;</a>EMBnet/CNB</td>";
    	    // contact info
	    echo "<td align=\"right\"><a href=\"mailto:$maintainer\">$maintainer</a></td>";
	echo "</tr></table></center>";
	// Page
	echo "</html>";
}

/**
 * print a warning
 *
 * Prints a warning in a separate pop-up window.
 * A warning is issued when a non-critical problem has been detected.
 * Execution can be resumed using some defaults, but the user should
 * be notified. In order to not disrupt the web page we are displaying
 * we use a JavaScript pop-up alert to notify the user.
 *
 * @param msg the warning message to send the user
 */
function warning($msg)
{
    // TODO ECMAscript
    echo "<script language=\"JavaScript\">";
    echo "alert(\"WARNING:\n $msg\");";
    echo "</script>";
}

/**
 * print an error message and exit
 *
 * Whenever we detect something wrong, we must tell the user. This function
 * will take an error message as its argument, format it suitably and
 * spit it out.
 *
 * @note This might look nicer using javascript to pop up a nice window with
 * the error message. Style sheets would be nice too.
 *
 * @param where the name of the caller routine or the process where the
 * 		error occurred
 * @param what  a description of the abnormal condition that triggered
 *  		the error
 */

function error($where, $what)
{
	// format the message
	echo "<p></p><center><table border=\"2\">\n";
	echo "<tr><td><center><font color=\"red\"><strong>\n";
	echo "ERROR - HORROR\n";
	echo "</strong></font></center></td></tr>\n";
	echo "<tr><td><center><b>$where</b></center></td></tr>\n";
	echo "<tr><td><center>$what</center></td></tr>\n";
	echo "</table></center><p></p>\n";
}

/**
 * print a letal error message and die
 *
 * This function is called whenever a letal error (one that prevents
 * further processing) is detected. The function will spit out an
 * error message, close the page and exit the program.
 * It should seldomly be used, since it may potentially disrupt the
 * page layout (e.g. amid a table) by not closing open tags of which
 * it is unaware.
 * Actually it is a wrapper for error + terminate.
 *
 * @param where location (physical or logical) where the error was
 * detected: a physical location (routine name/line number) may be
 * helpful for debugging, a logical location (during which part of
 * the processing it happened) will be more helful to the user.
 *
 * @param where the name of the caller routine or the process where the
 * 		error occurred
 * @param what  a description of the abnormal condition that triggered
 *  		the error
 */
function letal($where, $what)
{
    	set_header(0,0);
	error($where, $what);
	set_footer();
    	exit();
}

/**
 *  Generate a random number using srand()/rand()
 *
 *  @return integer random number
 */
function random_number()
{
    /**
     * Random values generation
     * srand -- Seed the random number generator
     * rand -- Generate a random integer
     */
     srand((double)microtime()*10000);
     $random_value = rand();
     return $random_value;
}

/**
 *  Generate a session directory to be used for doing all our work
 *
 *  We will create a session directory on the system scratch space
 * to perform all our subsequent work.
 *
 *  We need to know the systemwide scratch/tmp directory.
 *
 *  We do all our work on the system scratch space, but to avoid clashes
 * between simultaneous instances of this same service, we generate a
 * unique session_ID and use it to ensure that we are using a namespace that
 * is not being used by anyone else.
 *
 *  To generate the unique name, we use the fact that mkdir(2) should be
 * atomic and return an error (EEXIST) if the specified pathname already
 * exists.
 *
 *  To avoid an infinite loop in the event of an error, we allow a maximum
 * number of tries.
 *
 *  @return string a unique name for the sandbox. This name should be
 *  	    	unique or we risk colliding with other simultaneous
 *  	    	runs.
 */
function activate_new_sandbox()
{
    
    global $system_tmp_root;
    global $debug;
    
    if ($debug)
    	echo "<h3>activating a new sandbox</h3>";

    $i = 0;
    do {
    	if ($i > 10) 
	    letal("Activate a new sandbox", 
	    	"Could not create a sandbox on $system_tmp_root after 10 tries\n");
    	$i++;
        $session_id = random_number();
	if ($debug) echo "<p>$i: Trying $session_id</p>\n";
    } while (!mkdir("$system_tmp_root/$session_id", 0700));
    
    chdir("$system_tmp_root/$session_id");
    if ($debug) {
    	$cwd = getcwd();
	echo "<p>Working on $cwd (session is $session_id)</p>\n";
    }

    return $session_id;
    
}

/**
 *  Upload a file to the current directory
 *
 *  Get a user file submitted under a specific 'tag': for the user to
 * submit a file, it must be associated with a specific input field in
 * the submission form. This input field in turn will have an identifying
 * tag in the form. What we are actually doing here is to get the file
 * submitted under the input field associated to the tag provided.
 *
 *  @param  string tag	Tag identifying the file input box in the form.
 *
 *  @return string filename The original name of the file in the user's box.
 */
function upload_file($tag)
{
    global $debug;
    global $local_tmp_path;
    
    $dir = getcwd(); // bring it to the current directory
    
    if ($debug) echo "<p>Upload file</p>\n";

    
    $new_file = $_FILES[$tag]['tmp_name'];
    $new_filename = $_FILES[$tag]['name'];
    $new_filesize = $_FILES[$tag]['size'];
    $uploadfile = getcwd() .'/'. basename($new_filename); 

    if ($debug) echo "<blockquote>Temporary name: $new_file<br />\n".
    	    	     "File name: $new_filename<br />\n".
		     "File size: $new_filesize<br />\n".
		     "Saved as: $uploadfile</blockquote><br />\n";
    
    if ($new_file == 'none' || $new_file == '')
    {	
    	// letal prints an error and dies
    	letal("Upload file", "No file uploaded");
    }
    
    if ($new_filesize == 0)
    {
    	letal("Upload file", "Uploaded file $new_filename has zero length");
    }

    // seems OK, get it
    if (move_uploaded_file($new_file, $uploadfile)) 
    {
    	return basename($new_filename);	// original user file name
    } 
    else 
    {
    	letal("Upload file", "Could not upload your file $new_filename");
    }
    {
    	echo "<pre>";
    	system("ls -l /tmp");
    }
}

// Emulate PHP5 file_put_contents

define('FILE_APPEND', 1);

function file_put_content($n, $d, $flag = false) {
   $mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
   $f = @fopen($n, $mode);
   if ($f === false) {
       return 0;
   } else {
       if (is_array($d)) $d = implode($d);
       $bytes_written = fwrite($f, $d);
       fclose($f);
       return $bytes_written;
   }
}


// Should change the logic to
//  1. Test if session exists
//  2. If it does not exist submit job (use timed_header)
//  3. Check centinel finished file
//     3.1 if it does not exist show wait page (use timed header)
//     3.2 if it does exits show results (use fixed header)

// Check if we are being called with a session ID
$sess=$_POST['sess'];

// if this is a new session
if ($sess == '0') {
    //
    // Create a sandbox to work in and jump into it
    //

    $session_id = activate_new_sandbox();

    //
    //  	    	    Get the data set
    //
    $txseq = FALSE;

    if (isset($_POST['inputdata'])) {
	$inputdata = $_POST['inputdata'];
    }

    if ($inputdata != '') {
	// dump $psetdata to a file and set $psetfile to its name
	$inputfile = 'input.nt';
	if (file_put_content($inputfile, $inputdata) < strlen($inputdata)) {
    	    $txseq = FALSE;
	}
	else {
    	    $txset = TRUE;
	}
    }

    // if no explicit data try a file
    if ($txseq == FALSE) {
	if ((isset($_FILES['inputfile'])) && ($_FILES['inputfile']['size'] != 0)) {
    	    $inputfile = upload_file('inputfile');
	    $filseq = TRUE;
	} else {
    	    letal("No data set to analyze has been received");
	    $filseq = FALSE;
	}
    }
    // at this point inputfile contains a file name and either txseq or filesq is TRUE
    set_header(15, $session_id);
    echo "<center><table border=\"0\" width=\"80%\">".
    "<tr>\n".
    "<td align=\"left\"><img src=\"img/openphotonet_SAILING_32.JPG\" width=\"154\" height=\"115\"></td>\n".
    "<td align=\"right\"><font color=\"darkblue\">\n".
    "<h1><a href=\"\">Glimmer 1.02".
    "</a></h1></font></td>\n".
    "</tr></table></center>\n";
    
    echo "<center><h2>Web interface developed at the Scientific Computing Service<br />".
	"EMBnet/CNB by <i>Jos&eacute; R. Valverde &copy; 2009</h2></center>\n";
    echo "<blockquote>    
    <b>For publication of results, please cite:</b><br />
    Valverde, J. R. (2009) http://www.es.embnet.org/Services/MolBio/distdna/
    </blockquote>\n";
    if (txseq) {
    	    echo "Using your provided <a href=\"$http_tmp_root/$session_id/$inputfile\">data set</a>:<br />\n";
    //	.
    //	    "<center><table border=\"1\" width=\"90%\">\n".
    //	    "<tr><td><pre>".file_get_contents($inputfile)."</pre></tr></td>\n".
    //	    "</table></center>\n";
    } else {
	    echo "Using the data set from file <a href=\"$http_tmp_root/$session_id/$inputfile\">$inputfile</a>:<br />\n".
        	"\n";
    //	    "<center><table border=\"1\" width=\"90%\">\n".
    //	    "<tr><td><pre>".file_get_contents($inputfile)."</pre></tr></td>\n".
    //	    "</table></center>\n";
    }
    //
    //  	    	OK, ready to build the command and run:
    //

    $command = "$driver $inputfile $inputfile";

    echo "Glimmer will be run with the following command line:<br />\n".
	"<b>$command</b><br /><br />\nResults are:<br />\n<hr><pre>";

    system("$command");
    echo "<hr /><center>Your job $sess is currenly executing. Please wait or reload this page</center><hr />\n";
    echo "</pre></hr>";

    set_footer();
}
else
{
    // we were called with a session number: 
    //	    check for valid session
    if (! isdir("$system_tmp_root/$sess") letal("Invalide session ".$sess);
    //	    check for finished job
    if (! isfile("$system_tmp_root/$sess/done") {
    //	    if not finished output wait page
    	set_header(15, $sess);
	echo "<center><table border=\"0\" width=\"80%\">".
	"<tr>\n".
	"<td align=\"left\"><img src=\"img/openphotonet_SAILING_32.JPG\" width=\"154\" height=\"115\"></td>\n".
	"<td align=\"right\"><font color=\"darkblue\">\n".
	"<h1><a href=\"\">Glimmer 1.02".
	"</a></h1></font></td>\n".
	"</tr></table></center>\n";

	echo "<center><h2>Web interface developed at the Scientific Computing Service<br />".
	    "EMBnet/CNB by <i>Jos&eacute; R. Valverde &copy; 2009</h2></center>\n";
	echo "<blockquote>    
	<b>For publication of results, please cite:</b><br />
	Valverde, J. R. (2009) http://www.es.embnet.org/Services/MolBio/distdna/
	</blockquote>\n";
    	echo "<hr /><center>Your job $sess is currenly executing. Please wait or reload this page</center><hr />\n";

    }
    else {
    //	    if finished show results
    	if (! isfile("$system_tmp_root/$sess/$inputfile.coord")) letal("Error: no long ORF coordinates");
	if (! isfile("$system_tmp_root/$sess/$inputfile.train")) letal("Error: no training set");
	if (! isfile("$system_tmp_root/$sess/$inputfile.out")) letal("Error: no predicted genes");
	set_header(0,$sess);
	echo "<center><table border=\"0\" width=\"80%\">".
	"<tr>\n".
	"<td align=\"left\"><img src=\"img/openphotonet_SAILING_32.JPG\" width=\"154\" height=\"115\"></td>\n".
	"<td align=\"right\"><font color=\"darkblue\">\n".
	"<h1><a href=\"\">Glimmer 1.02".
	"</a></h1></font></td>\n".
	"</tr></table></center>\n";

	echo "<center><h2>Web interface developed at the Scientific Computing Service<br />".
	    "EMBnet/CNB by <i>Jos&eacute; R. Valverde &copy; 2009</h2></center>\n";
	echo "<blockquote>    
	<b>For publication of results, please cite:</b><br />
	Valverde, J. R. (2009) http://www.es.embnet.org/Services/MolBio/distdna/
	</blockquote>\n";
    	echo "<ul>\n<li><a href=\"$http_tmp_root/$sess/$inputfile\">Input data:</td><td>
    }
}








//----------------------------------------------------------------------

?>
