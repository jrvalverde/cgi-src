<?php
//
// HYML tags
//
echo "<HTML><HEAD></HEAD><BODY>";

//
// Customization section:
//	Set these values appropriately for your environment
//

// Location of the user data and result files:
//	they should be WWW accessible, therefore we need
//	to specify their location relative to the system /
//	and to the www root (for the URLs):
$system_tmproot="/data/www/EMBnet/tmp";
$http_tmproot="/tmp";

// set to 0 for no debug output, or select a debug level
$debug=0;

// leave empty if you don't have the corresponding programs
$pdb2vrml="/opt/structure/bin/pdb2vrml";

//
// End of configuration section
//

//
// Start processing
//

if ($debug > 0) 
{
	$system_tmproot="/data/www/EMBnet/tmp/webgramm";
	$http_tmproot="/tmp/yami";
	echo "<CENTER><H1>WEBGRAMM DRIVER</H1></CENTER>";
}

// select a random name for the tmp dir and cd to it
$random_str = rand(1000000, 9999999);
set_tmp_dir( $random_str, $system_tmproot );

//PIR and ALIGNMENT uploads
upload_processor();

//Writing rmol.gr

$rmol="./rmol.gr";
$receptor = "receptor";
$ligand = "ligand";

$fp = fopen( "$rmol", "w" );

fwrite( $fp, "# Filename  Fragment  ID      Filename  Fragment  ID     [paral/anti  max.ang]\n");
fwrite( $fp, "# ----------------------------------------------------------------------------\n");
fwrite( $fp, "\n");
fwrite( $fp, "$receptor.pdb     *    receptor  $ligand.pdb       *     ligand\n");

fclose( $fp );

//Writing rpar.gr
$rpar="./rpar.gr";

$fp1 = fopen( "$rpar", "w" );

fwrite( $fp1, "Matching mode (generic/helix) ....................... mmode= generic\n" );
fwrite( $fp1, "Grid step ............................................. eta= 6.8\n" );
fwrite( $fp1, "Repulsion (attraction is always -1) .................... ro= 6.5.\n" );
fwrite( $fp1, "Attraction double range (fraction of single range) ..... fr= 0.\n" );
fwrite( $fp1, "Potential range type (atom_radius, grid_step) ....... crang= grid_step\n" );
fwrite( $fp1, "Projection (blackwhite, gray) ................ ....... ccti= gray\n" );
fwrite( $fp1, "Representation (all, hydrophobic) .................... crep= all\n" );
fwrite( $fp1, "Number of matches to output .......................... maxm= 1000\n" );
fwrite( $fp1, "Angle for rotations, deg (10,12,15,18,20,30, 0-no rot.)  ai= 20\n" );

fclose( $fp1 );

//Writing wlist.gr
$wlist="./wlist.gr";

$fp2 = fopen( "$wlist", "w" );

fwrite( $fp2, "# File_of_predictions   First_match   Last_match   separate/joint  +init_lig\n" );
fwrite( $fp2, "# ----------------------------------------------------------------------------\n" );
fwrite( $fp2, "$receptor-$ligand.res	1   10	separ	no\n" );	

fclose( $fp2 );

//run gramm in the background
call_gramm($debug);

// display background running notice
// TEST THIS FIRST
echo "<CENTER><H1>Your GRAMM job has been started</H1><BR><BR>";
echo "<H2>When the results are ready they will be available in the ";
echo "following link: <br><br><A HREF=\"$http_tmproot/webgramm$random_str/\">$random_str</A></CENTER></H2>";



/////////////////////////////// SUBROUTINES ////////////////////////////////

//
//Creating the tmp directory
//
function set_tmp_dir( $random_str, $system_tmproot )
{   	
	mkdir ("$system_tmproot/webgramm$random_str", 0755);
	copy("./gramm.sh", "$system_tmproot/webgramm$random_str/gramm.sh");
	copy("./Rodin_Penseur.jpg", "$system_tmproot/webgramm$random_str/Rodin_Penseur.jpg");
	copy("./show_results.php", "$system_tmproot/webgramm$random_str/index.php");
	chdir( "$system_tmproot/webgramm$random_str" ); 
	chmod ("./gramm.sh", 0755);
}

//
// Manage uploads
//
function upload_processor()
{
//Two files to upload: Receptor file and Ligand file

	for ($i=0; $i<2; $i++) 
	{
   
		if($i==0)
		{
			$file_str="Receptor"; 
			$upfile="receptor.pdb";
		}else
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

//  		$upfile = "./$userfile_name";
  		if ( !move_uploaded_file($userfile, $upfile)) 
  		{
    			echo "<h1>$userfile y $upfile Problem: Could not move file into directory</h1>"; 
    			exit;
  		}


	}
	
}

//Runnig Gramm
function call_gramm($debug)
{	
	// Create a Gramm run script (or copy it from our install dir)
	// run the driver script in the background
	//     The driver script:
	//		Runs Gramm directly with args
	//		touches "done"
	//		suicides (removes itself to clear things up)

	$gramm = "./gramm.sh >> /dev/null 2>&1";
    	// Note:  If you start a program using this function ( exec ) and want to leave it
	// running in the background, you have to make sure that the output of 
	// that program is redirected to a file or some other output stream or 
	// else PHP will hang until the execution of the program ends.
	// Gramm creates his own .log file, so we redirect the output to a file
	// called "gramm.log" and after the program execution the modeller.sh
	// script delete this file.
	exec("$gramm");

	if ($debug > 0) 
	{
            echo "<CENTER><H1>Done.</H1></CENTER>";
	}
}

//
//HTML tags
//
echo "</BODY></HTML>";


?>
