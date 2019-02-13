<?php

$local_tmp_path="/data/www/EMBnet/tmp/eniac";
$www_tmp_path="/tmp/eniac";

$eas="/opt/emul/bin/eas";
$i2bin="/opt/emul/bin/i2bin";
$bin2i="/opt/emul/bin/bin2i";
$bin2h="/opt/emul/bin/bin2h";
$eniac="/opt/emul/bin/eniac";

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


$program="";
$data="";

$program = $_POST["program"];
$data = $_POST["data"];

// create sandbox
$session = activate_new_sandbox();

// create input files
$p = fopen("program.eas", "w");
    fwrite($p, $program);
fclose($p);
$d = fopen("data.in", "w");
    fwrite($d, $data);
fclose($d);


// translate program
system("$eas program.eas program.bin program.lis");

// translate input
system("cat data.in | $i2bin > data.bin");

// execute program
system("$eniac -x program.bin -i data.bin -o output.bin");

// translate output
system("$bin2i output.bin > output.out");
system("cat output.bin | tr -d \\\\000 > output.txt");

echo <<< ENDREPORT
<center><h1>ENIAC run results</h1></center>

<p>Our engineers have read your proposed program and configured the ENIAC
to run it. After running the configuration, we have collected the output 
which you may recover from the tray below</p>

<center><h2>Program</h2></center>

<p>The code you submitted was <a href="$www_tmp_path/$session/program.eas" target="new">here</a></p>
<p>The translation we performed is <a href="$www_tmp_path/$session/program.lis" target="new">here</a></p>
<p>And the executable code in binary form is <a href="$www_tmp_path/$session/program.bin" target="new">this</a></p>
<p><font color="blue">This binary code has been used to reconfigure the ENIAC
cabling</font></p>


<center><h2>Data</h2></center>

<p>The data you submitted to run the program was <a href="$www_tmp_path/$session/data.in" target="new">this</a></p>
<p>Your data, after conversion to binary form is <a href="$www_tmp_path/$session/data.bin" target="new">this</a></p>
<p><font color="blue">This binary information has been used to dial in your
data in the ENIAC</font></p>

<center><h2>Output</h2></center>

<p>The binary output collected after the ENIAC run your program is 
<a href="$www_tmp_path/$session/output.bin">here</a> 
    (see as <a href="$www_tmp_path/$session/output.txt" target="new">raw ASCII text</a>)</p>
<p>The translation of your binary output into numeric form is
<a href="$www_tmp_path/$session/output.out" target="new">here</a></p>

<center><h3>Thanks</h3></center>

<p>We wish to thank you for using our service and hope you are satisfied
with the results.</p>

ENDREPORT

?>
