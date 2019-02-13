<?php
//
// Customization section:
//
//      Set these values appropriately for your environment
//

$app_name='ANN-Spec';
$app_dir = '/Services/MolBio/ANN-Spec';
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
 * Location of ANN-Spec
 *
 * @global string ann_spec
 */
$ann_spec = '/opt/genomics/bin/ann-spec';

$genseq = '/opt/sequence/bin/genseq';

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

    // Start HTML vx.xx output
    echo "<html>";
    // Print headers
    echo "<head>\n";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\">\n";
    echo "<meta name=\"description\" content=\"a web interface to run ANN-Spec v1.0\">\n";
    echo "<meta name=\"author\" content=\"EMBnet/CNB\">\n";
    echo "<meta name=\"copyright\" content=\"(c) 2006 by CSIC - Open Source Software\">\n";
    echo "<meta name=\"generator\" content=\"$app_name\">\n";
    if ($reload > 0)
    	echo "<meta http-equiv=\"Refresh\" content=\"$reload\" ".
	"url=\"".$http_tmp_root."/$session_id/\"2>\n";
    echo "<link rel=\"stylesheet\" href=\"$app_dir/style/style.css\" type=\"text/css\"/>\n";
    echo "<link rel=\"shortcut icon\" href=\"$app_dir/images/favicon.ico\"/>\n";
    echo "<title=\"$app_name\">\n";
    echo "</head>";
    // Prepare body
    echo "<body bgcolor=\"white\" background=\"$app_dir/images/neuron_bg.jpg\" link=\"ffc600\" VLINK=\"#cc9900\" ALINK=\"#4682b4\">\n";
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
	    echo "<td><a href=\"$app_dir/c/copyright.html\">&copy;</a>EMBnet/CNB</td>";
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

function file_put_contents($n, $d, $flag = false) {
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


//
// There we go
//
set_header(0, 0);

echo "<center><table border=\"0\" width=\"80%\">".
    "<tr>\n".
    "<td align=\"left\"><img src=\"images/brain.jpg\"></td>\n".
    "<td align=\"right\"><font color=\"darkblue\">\n".
    "<h1><a href=\"http://www.cbs.dtu.dk/~workman/ann-spec/\">ANN-Spec ".
    "v1.0</a></h1></font></td>\n".
    "</tr></table></center>\n";
    
echo "<center><h2>Web interface developed at EMBnet/CNB by <i>Jos&eacute; ".
     "R. Valverde &copy; 2006</h2></center>\n";
echo "<blockquote>    
    <b>For publication of results, please cite:</b><br />
    Workman, C. and Stormo, G.D. (2000) ANN-Spec: A method for discovering 
    transcription factor binding sites with improved specificity. Proc. 
    Pacific Symposium on Biocomputing 2000.<br /><br />
    Heumann,J.M., Lapedes,A.S. and Stormo,G.D., Neural networks for 
    determining protein specificity and multiple alignment of binding 
    sites. Proccedings for the Intelligent Systems for Molecular Biology 
    (ISMB), 1994;2:188-194. PMID: 7584389; UI: 96039019.<br />
    </blockquote>\n";

//
// Create a sandbox to work in and jump into it
//

$session_id = activate_new_sandbox();

//
// Get input data
//
//  CAUTION: from here on this is spaghetti code in bad need of a major rewrite.
//  I know, I know, and as soon as I can find some spare time, I'll do it.
//

//
//  	    	    Get the positive set
//
$pset = FALSE;

if (isset($_POST['psetdata'])) {
    $psetdata = $_POST['psetdata'];
}

if ($psetdata != '') {
    // dump $psetdata to a file and set $psetfile to its name
    $psetfile = 'positive_set';
    if (file_put_contents($psetfile, $psetdata) != strlen($psetdata))
    	$pset = FALSE;
    else {
    	$pset = TRUE;
    	echo "Using your provided positive set:<br />\n".
	    "<center><table border=\"1\" width=\"90%\">\n".
	    "<tr><td><pre>".file_get_contents($psetfile)."</pre></tr></td>\n".
	    "</table></center>\n";
    }
}

// if no explicit data try a file
if ($pset == FALSE) {
    if ((isset($_FILES['psetfile'])) && ($_FILES['psetfile']['size'] != 0)) {
    	$psetfile = upload_file('psetfile');
	$pset = TRUE;
	echo "Using the positive set from file $psetfile:<br />\n".
	    "<center><table border=\"1\" width=\"90%\">\n".
	    "<tr><td><pre>".file_get_contents($psetfile)."</pre></tr></td>\n".
	    "</table></center>\n";
    } else {
    	letal("No positive set to analyze has been provided");
	$pset = FALSE;
    }
}
// at this point psetfile contains a file name and pset is TRUE

//
//  	    	    	Get the negative set
//
$nset == FALSE;
if (isset($_POST['nsetdata'])) {
    $nsetdata = $_POST['nsetdata'];
}

if ($nsetdata != '') {
    // dump $psetdata to a file and set $psetfile to its name
    $nsetfile = 'negative_set';
    if (file_put_contents($nsetfile, $nsetdata) != strlen($nsetdata))
    	$nset = FALSE;
    else {
    	$nset = TRUE;
    	echo "Using your provided negative set:\n".
	    "<center><table border=\"1\" width=\"90%\">\n".
	    "<tr><td><pre>".file_get_contents($nsetfile)."</pre></tr></td>\n".
	    "</table></center>\n";
    }
} 

// if no explicit data try a file
if (! $nset) {
    if ((isset($_FILES['nsetfile'])) && ($_FILES['nsetfile']['size'] != 0)) {
    	$nsetfile = upload_file('nsetfile');
	$nset = TRUE;
	echo "Using the negatve set from file $nsetfile:<br />\n".
	    "<center><table border=\"1\" width=\"90%\">\n".
	    "<tr><td><pre>".file_get_contents($nsetfile)."</pre></tr></td>\n".
	    "</table></center>\n";
    }
}

// if no explicit data and no file, check if we must generate one
if (! $nset) {
    $donset = $_POST['donset']; 	    	// y|n (only used if no negative set)
    if ($donset == 'y') {
    	// generate a random set of 3000 sequences
    	$nsetfile = 'generated_negative_set';
	system("$genseq -n 3000 -s 3000 -t ".$_POST['seqtype']." > $nsetfile");
	$nset = TRUE;
	echo "Using 3000 sequences of 3000 residues each generated at ".
	    "random:<br />\n".
	    "<center><table border=\"1\" width=\"90%\">\n".
	    "<tr><td><pre>".file_get_contents($nsetfile)."</pre></tr></td>\n".
	    "</table></center>\n";
    }
    else {
    	echo "No negative set will be used.<br />\n".
	    "Usage of the analytical partition function will be forced<br />\n";
    }
}
// at this point if nset is TRUE, nsetfile contains a filename


//
//  	    	    	Get sequence info
//
$seqtype = $_POST['seqtype']; 	    	// d(na) | p(rot)

$bothstrands = $_POST['bothstrands'];	// y|n (only used if $seqtype == 'dna')

//
//  	    	    	Get pattern info
//
$nsites = (int) trim(escapeshellcmd($_POST['nsites']));

$patlen = (int) trim(escapeshellcmd($_POST['patlen']));

//
//  	    	    	Get partition function
//
// default|analytical|random|all
if ($_POST['partition'] == 'default') {
    	// compute sensible defauls
    	// default = analytical if no nset
	// default = random if nset
    if ($nset) $partition = 'random';
    else $partition = 'analytical';
} else
    $partition = $_POST['partition'];

//
//  	    	OK, ready to build the command and run:
//

$command = "$ann_spec -i 2000 -m 100 ";

$verbose = 'n'; if ($verbose == 'y') $command .= '-t 111111 ';

if ($seqtype == 'd') $command .= '-a=DNA ';
else $command .= '-a=PROTEIN ';

if (($bothstrands = 'y') && ($seqtype == 'd')) $command .= '-c ';

$command .= "-P $nsites ";

$command .= "-l $patlen ";

if (($partition == 'analytical') || ($nset == FALSE)) $command .= '-y 0 ';
else if ($partition == 'all') $command .= '-y 2 ';
else $command .= '-y 1 ';

$command .= "-p $psetfile ";

#if ($nset == TRUE) $command .= "-n $nsetfile ";

echo "ANN-Spec will be run with the following command line:<br />\n".
    "<b>$command</b><br />\n<hr><pre>";
passthru("$command");
echo "</pre></hr>";

set_footer();

chdir('..');
system("rm -rf $session_id");

?>
