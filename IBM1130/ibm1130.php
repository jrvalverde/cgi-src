<?php

$local_tmp_path="/data/www/EMBnet/tmp/ibm1130";
$www_tmp_path="/tmp/ibm1130";

$ibm1130="/opt/emul/bin/ibm1130";

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
 *  We need to know the systemwide scratch/tmp directory (which is a
 * globally defined variable).
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
    global $local_tmp_path;
    global $debug;

    if ($debug==1)
    	echo '<h3>activating a new sandbox</h3>';

    $i = 0;
    do {
    	if ($i > 10) {
	    echo("WARNING: Activate a new sandbox " . 
	    	"Could not create a sandbox on $local_tmp_path after 10 tries\n");
	    exit;
	}
    	$i++;
        $session_id = random_number();
	if ($debug) echo "<p>$i: Trying $session_id</p>\n";
    } while (!mkdir("$local_tmp_path/$session_id", 0700));
    
    chdir("$local_tmp_path/$session_id");
    if ($debug) {
    	$cwd = getcwd();
	echo "<p>Working on $cwd (session is $session_id)</p>\n";
    }

    return $session_id;
}


$job="";

$job = $_POST["job"];

// create sandbox
$session = activate_new_sandbox();
copy(dirname($_SERVER['SCRIPT_FILENAME']).
     "/dms.dsk", "$local_tmp_path/$session/dms.dsk");
copy(dirname($_SERVER['SCRIPT_FILENAME']).
     "/job", "$local_tmp_path/$session/job");

// create input files
$p = fopen("session.job", "w");
    fwrite($p, $job);
fclose($p);


// execute program
$p = popen("$ibm1130 > output.txt", "w");
    fwrite($p, "do job session\n");
    fwrite($p, "q\n");
fclose($p);

// just in case
touch("job.lst");

echo <<< ENDREPORT
<center><h1>IBM1130 run results</h1></center>

<p>Our engineers keypunched your job and loaded the 1130 with the card
deck to run it. 
After running the job, we have collected the output 
which you may recover from the tray below</p>

<center><h2>Original job</h2></center>

<p>The job you submitted was <a href="$www_tmp_path/$session/session.job" 
target="new">here</a></p>

<center><h2>Output</h2></center>

<p>The output produced by the IBM 1130 while running your jobs is
<a href="$www_tmp_path/$session/output.txt" target="new">here</a></p>
<p>The listings generated on the printer are
<a href="$www_tmp_path/$session/job.lst" target="new">here</a></p>

<center><h3>Thanks</h3></center>

<p>We wish to thank you for using our service and hope you are satisfied
with the results.</p>

ENDREPORT

?>
