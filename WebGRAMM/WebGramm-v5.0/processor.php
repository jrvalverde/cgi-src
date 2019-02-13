<?
//
// Customization section:
//
//	Set these values appropriately for your environment
//

/**
 * Base system location of the user data and result files
 *
 *	they should be WWW accessible, but we need
 *	to specify their location relative to the system /
 *	while doing internal processing.
 */
$system_tmproot="/data/www/EMBnet/tmp";

/**
 * Base WWW location of user data and result files:
 *
 *	This is the root used to generate the URLs that the
 *	user will access to see his results.
 *
 * @global string http_tmproot
 */
$http_tmproot="/tmp";

/// set to 0 for no debug output, or select a debug level
$debug=0;

/**
 * Location of PDB to VRML converter
 * 
 * leave empty if you don't have the corresponding program
 *
 * \todo check if this is used at all on this file or needed here.
 *
 * @global string pdb2vrml
 */
$pdb2vrml="/opt/structure/bin/pdb2vrml";

//
// End of configuration section
//

//
// Start processing
//

$matching=$_POST["matching"];
$representation=$_POST["representation"];
$resolution=$_POST["resolution"];

$receptor_name = "receptor";
$ligand_name = "ligand";


//
// OpenHTML tags
//
echo "<HTML><HEAD>";
echo "<title>WebGramm</title>";
echo "</HEAD><BODY BGCOLOR=\"LightGrey\">";

// create the working directory and make it the default directory
$work_dir = go_to_work($system_tmproot, $http_tmproot );

// upload receptor and ligand coordinates
upload_data();

write_rmol_gr($receptor_name, $ligand_name);

write_rpar_gr($matching, $representation, $resolution);

write_wlist_gr($receptor_name, $ligand_name);

run_gramm($debug);

// XXX NOTE: when converting to a web service the following should
// actually be a return that gives back the URL of another object
// (the show_process object) that will provide status info as well
// as the final results.

// Make the user browser go to the working directory as well
echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"5; URL=$http_tmproot/$work_dir\">";

// display background running notice with a pointer to the
// working dir so the user knows and just in case the automatic
// redirection fails (so s/he can jump manually).
echo "<CENTER><H1>Your GRAMM job has been started</H1><BR><BR>";
echo "<H2>When the results are ready they will be available in the ";
echo "following link: <br><br><A HREF=\"$http_tmproot/$work_dir/\">$random_str</A></CENTER></H2>";

//
// close HTML tags
//
echo "</BODY></HTML>";

// and we are done.
exit;

/////////////////////////////// SUBROUTINES ////////////////////////////////

/**
 * Write rmol.gr configuration file
 *
 * The file rmol.gr is needed by GRAMM to specify the molecules to be
 * used for docking.
 *
 * @param receptor the file containing the molecule to maintain fixed
 * @param ligand the file containing the molecule to move around (should
 *	hence be the smaller one).
 */
function write_rmol_gr($receptor, $ligand)
{
    $rmol="./rmol.gr";

    $fp = fopen( "$rmol", "w" );

    fwrite( $fp, "# Filename  Fragment  ID      Filename  Fragment  ID     [paral/anti  max.ang]\n");
    fwrite( $fp, "# ----------------------------------------------------------------------------\n");
    fwrite( $fp, "\n");
    fwrite( $fp, "$receptor.pdb\t*\treceptor\t$ligand.pdb\t*\tligand\n");

    fclose( $fp );

}

/**
 * Write the configuration file rpar.gr
 *
 * The file rpar.gr contains all the parameters that Gramm should use
 * to perform the docking work.
 *
 * We allow users to modify a small amount of these parameters in
 * order to guarantee some sensible default values in the face of
 * lay users (the average expected user). More advanced users might
 * want a fine grained control, but then these may as well run it
 * on the command line.
 *
 * @param matching generic or helix
 * @param representation whether we use hydrophobic or normal docking
 * @param resolution	high or low resolution docking
 *
 * @note see Gramm documentation for details.
 */
function write_rpar_gr($matching, $representation, $resolution)
{
    //Writing rpar.gr
    $rpar="./rpar.gr";

    if ($representation=="hydrophobic")
    {
	$projection="blackwhite";
    }else
    {
	$projection="gray";
    }

    $fp1 = fopen( "$rpar", "w" );

	if ( $resolution=="low")
	{
    	    fwrite( $fp1, "Matching mode (generic/helix) ....................... mmode= $matching\n" );
    	    fwrite( $fp1, "Grid step ............................................. eta= 6.8\n" );
    	    fwrite( $fp1, "Repulsion (attraction is always -1) .................... ro= 6.5.\n" );
    	    fwrite( $fp1, "Attraction double range (fraction of single range) ..... fr= 0.\n" );
    	    fwrite( $fp1, "Potential range type (atom_radius, grid_step) ....... crang= grid_step\n" );
    	    fwrite( $fp1, "Projection (blackwhite, gray) ................ ....... ccti= $projection\n" );
     	    fwrite( $fp1, "Representation (all, hydrophobic) .................... crep= $representation\n" );
    	    fwrite( $fp1, "Number of matches to output .......................... maxm= 1000\n" );
    	    fwrite( $fp1, "Angle for rotations, deg (10,12,15,18,20,30, 0-no rot.)  ai= 20\n" );
	}else
	{
    	    fwrite( $fp1, "Matching mode (generic/helix) ....................... mmode= $matching\n" );
    	    fwrite( $fp1, "Grid step ............................................. eta= 1.7\n" );
    	    fwrite( $fp1, "Repulsion (attraction is always -1) .................... ro= 30.\n" );
    	    fwrite( $fp1, "Attraction double range (fraction of single range) ..... fr= 0.\n" );
    	    fwrite( $fp1, "Potential range type (atom_radius, grid_step) ....... crang= atom_radius\n" );
     	    fwrite( $fp1, "Projection (blackwhite, gray) ................ ....... ccti= $projection\n" );
     	    fwrite( $fp1, "Representation (all, hydrophobic) .................... crep= $representation\n" );
    	    fwrite( $fp1, "Number of matches to output .......................... maxm= 1000\n" );
    	    fwrite( $fp1, "Angle for rotations, deg (10,12,15,18,20,30, 0-no rot.)  ai= 10\n" );
	}

    fclose( $fp1 );
}

/**
 *	Write configuration file wlist.gr
 *
 *	The file wlist.gr is used by GRAMM in the coordinate building
 * step as a guide to know how many coordinate sets for the docked
 * ligand and receptor molecules to generate. It tells as well whether
 * these sets should be saved in a single file or a separate coordinate
 * files.
 *
 *	We use a single file to provide the user with simpler browsing
 * capabilities. Hence we need minimal information.
 *
 * @param receptor the file containing the receptor (fixed) molecule
 * @param ligand the file containing the ligand (mobile) molecule
 */
function write_wlist_gr($receptor, $ligand)
{
    //Writing wlist.gr
    $wlist="./wlist.gr";

    $fp2 = fopen( "$wlist", "w" );

    fwrite( $fp2, "# File_of_predictions   First_match   Last_match   separate/joint  +init_lig\n" );
    fwrite( $fp2, "# ----------------------------------------------------------------------------\n" );
    fwrite( $fp2, "$receptor-$ligand.res\t1\t10\tsepar\tno\n" );	

    fclose( $fp2 );

}

/**
 * Create a temporary work directory and cd to it.
 *
 * In order to run the job we need a temporary directory to store the
 * results. This can not have a static name since then concurrent runs
 * would overwrite each other's data.
 * The solution is trivial: we require a seed string to generate the
 * directory. This string should be unique.
 */
function setup_gramm_tmp_dir( $system_tmproot, $http_tmproot )
{   	
    $random_str = rand(1000000, 9999999);
    $work_dir = "webgramm$random_str";

    mkdir ("$system_tmproot/$work_dir", 0755);
    copy("./gramm.sh", "$system_tmproot/$work_dir/gramm.sh");
    copy("./Rodin_Penseur.jpg", "$system_tmproot/$work_dir/Rodin_Penseur.jpg");
    copy("./6h2o-w-small.gif", "$system_tmproot/$work_dir/6h2o-w-small.gif");
    copy("./left.gif", "$system_tmproot/$work_dir/left.gif");
    copy("./right.gif", "$system_tmproot/$work_dir/right.gif");
    copy("./show_results.php", "$system_tmproot/$work_dir/index.php");
    chdir( "$system_tmproot/$work_dir" ); 
    chmod ("./gramm.sh", 0755);

    return $work_dir;
}

/**
 * Manage uploads
 *
 * This function will manage the upload of the files needed to run the
 * program. We'll retrieve two files which will be saved as receptor.pdb
 * and ligand.pdb
 */

function upload_data()
{
    // Two files to upload: Receptor file and Ligand file
    // NB JR: there is a far better way to do this. David did it
    //	this way since he was looking for a generic solution to
    //	upload N files and later adapted it to this problem...

    for ($i=0; $i<2; $i++) 
    {

	if($i==0)
	{
	    $file_str="Receptor"; 
	    $upfile="receptor.pdb";
	}
	else
	{
	    $file_str="Ligand";
	    $upfile="ligand.pdb";
	}


	$userfile = $_FILES['upload']['tmp_name'][$i];
	$userfile_name = $_FILES['upload']['name'][$i];
	$userfile_size = $_FILES['upload']['size'][$i];


  	if ($_FILES['upload']['tmp_name'][$i]=="none" || $_FILES['upload']['tmp_name'][$i]=="")
  	{
    	    echo "<h1>Problem: no $file_str file uploaded</h1>";
    	    exit;
  	}

  	if ($_FILES['upload']['size'][$i]==0)
  	{
    	    echo "<h1>Problem: uploaded $file_str file is zero length</h1>";
    	    exit;
  	}

//  	$upfile = "./$userfile_name";
  	if ( !move_uploaded_file($userfile, $upfile)) 
  	{
    	    echo "<h1>$userfile -&gt; $upfile Problem: Could not move file into directory</h1>"; 
    	    exit;
  	}


    }

}

/**
 * Run Gramm
 *
 * We now have everything ready to start running GRAMM. Launch it.
 */
function run_gramm($debug)
{	
	// Create a Gramm run script (or copy it from our install dir)
	// run the driver script in the background
	//     The driver script:
	//		Runs Gramm directly with args
	//		touches "done"

	$gramm = "./gramm.sh scan coord >> /dev/null 2>&1";
    	// Note:  If you start a program using this function ( exec ) and want to leave it
	// running in the background, you have to make sure that the output of 
	// that program is redirected to a file or some other output stream or 
	// else PHP will hang until the execution of the program ends.
	// Gramm creates his own .log file, so we don't need to redirect the 
	// output to a file

    	$started = fopen("./started", "w");
    	fclose($started);	

	exec("$gramm");

	if ($debug > 0) 
	{
            echo "<CENTER><H1>Done.</H1></CENTER>";
	}
}

?>
