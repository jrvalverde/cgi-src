<?
// Basic.
//  Convert a PDB file to VRML with all default options.
//
//	You need access to pdb2vrml to use this servlet.
//
// (C) EMBnet/CNB 13/05/2003
//	See http://www.es.embnet.org/Copyright-CSIC.html
//
// AUTHOR: José R. Valverde (EMBnet/CNB) jrvalverde@es.embnet.org
//	   David García Aristegui (EMBnet/CNB) david@es.embnet.org
//
// SEE ALSO:	http://www.pc.chemie.tu-darmstadt.de/research/vrml/pdb2vrml.html
//
// $Id: pdb2vrml1.php,v 1.3 2005/10/07 13:26:56 netadmin Exp $
// $Log: pdb2vrml1.php,v $
// Revision 1.3  2005/10/07 13:26:56  netadmin
// Modified for WebServices
// Modified pdb2png to use raster3d in addition to molscript (molscript alone
// required a valid DISPLAY)
//
// Revision 1.2  2005/01/24 09:52:19  root
// Added web services to PDB2VRML2 and PDB2PNG
//
// Revision 1.1.1.3  2005/01/21 14:35:22  netadmin
// Added WS to pdb2vrml1
//
// Revision 1.1.1.2  2005/01/20 16:56:44  netadmin
// Added support for Web Services using nuSOAP
//
// Revision 1.1.1.1  2004/12/16 14:32:48  root
// Various tools to manipulate PDB files
//

/////////////////////// GLOBAL VARIABLES //////////////////////////////////
	
// Location of the user data and result files:
// they should be WWW accessible, therefore we need
// to specify their location relative to the system /
// and to the www root (for the URLs):
$system_tmproot="/data/www/EMBnet/tmp";
$http_tmproot="/tmp";

// program locations
$pdb2vrml="/opt/structure/bin/pdb2vrml";

// set to 0 for no debug output, or select a debug level
$debug=0;


////////////////////////////////// START /////////////////////////////////
    
    // be nice: provide our own source code to others
    if (isset($_GET["download"]))
    	$dnld=$_GET["download"];
    if (isset($dnld)) {
	header("Content-type: text/plain");
	header("Content-Disposition: inline; filename=pdb2vrml1.php");
	readfile("./pdb2vrml1.php");
	exit;
    }
    // IMPORTANT! Provide citation references
    if (isset($_GET["reference"])) {
    	echo "<HTML><BODY><H1>pdb2vrml1</H1>";
	echo "<P>For more details and citation iformation see:</P><BLOCKQUOTE>";
	echo "<A HREF=\"http://www.pc.chemie.tu-darmstadt.de/research/vrml/pdb2vrml.html\">http://www.pc.chemie.tu-darmstadt.de/research/vrml/pdb2vrml.html</A></BLOCKQUOTE></BODY></HTML>";
	exit;
    }

    if ( $debug == 1 ) echo "<HTML><BODY><H1>pdb2vrml1</H1>";

    // select a random name for the tmp dir and cd to it
    $random_str = rand(1000000, 9999999);
    set_tmp_dir( $random_str, $system_tmproot );

    // get URL to convert into "./structure.pdb"
    if ( $debug == 1 ) echo "<P>Getting remote URL</P>";
    
    if ( !get_URL($debug) )
    {
    	exit;
    }
    
    if ( generate_vrml1($pdb2vrml) ) 
    {
    	// Change content-type to x-world/x-vrml and send world
    	header("Content-type: x-world/x-vrml");
	readfile("./structure.vrml");
    	cleanup($system_tmproot, $random_str);
    }
    else 
    {
    	// produce an error message
	echo "<html><body><h1>Error...</h1></body></html>";
    }
        
    echo "</BODY></HTML>";
    exit;
    	
/////////////////////////////// SUBROUTINES ////////////////////////////////

//
//Creating the tmp directory
//
function set_tmp_dir( $random_str, $system_tmproot )
{   	
	mkdir ("$system_tmproot/pdb2vrml1$random_str", 0755);
	chdir( "$system_tmproot/pdb2vrml1$random_str" ); 
}

//
// Get remote URL to process
// It comes in a WWW variable url=remote_url
//
function get_URL($debug)
{   
    // Retrieve URL value
    $url = $_GET["url"];
    
    if ( !isset($url) ) 
    {
    	// if not set print usage notice
	usage();
	echo "<BR><P>Please, submit the URL of the PDB file to process.</P>\n</BODY>\n</HTML>";
    	return false;
    }
    if ( $debug == 1 ) echo "<H1>Called with URL=$url</H1>";
    
    // Retrieve URL contents into "structure.pdb" in current dir
    $ch = curl_init($url);
    $fp = fopen("./structure.pdb", "w");
    
    curl_setopt ($ch, CURLOPT_FILE, $fp);
    curl_setopt ($ch, CURLOPT_HEADER, 0);

    curl_exec ($ch);
    curl_close ($ch);
    fclose ($fp);
    
    //BUG, waiting for the next PHP release
    /*if ($error = curl_error($ch)) 
    {
    	usage();
    	echo "<BR><P>Error ($error): can't retrieve the PDB file</P>";
	exit;
    }*/
    
    $size=filesize("./structure.pdb");
    if ( $size==0 )
    {
    	usage();
    	echo "<BR><P>Error: can't retrieve the PDB file</P></BODY></HTML>";
	return false;
    }else
    {
    	if ( $debug == 1 ) 
    	{
    	    // Show what we got
    	    echo "<PRE>";
	    readfile("./structure.pdb");
	    echo "</PRE>";
    	}
	return true;
    }
}

function usage()
{
    echo "<CENTER><H1>pdb2vrml1</H1></CENTER>";
    echo "<P><STRONG>PDB2VRML1</STRONG>: convert a PDB file to a VRML 1 virtual world</P>";
    echo "<P><STRONG>Usage:</STRONG> pdb2vrml1.php?url=url/path/to/file.pdb</P>";
}

function generate_vrml1($pdb2vrml)
{
    // check for errors and return true or false appropriately
    exec("cat ./structure.pdb | $pdb2vrml -pdb - - > ./structure.vrml", $str_output, $str_var);
    if ( $str_val==0 )
    {
	return true;
    }else
    {
    	return false;
    }
}

function cleanup($system_tmproot, $random_str)
{
    if ( $debug == 1 ) return;
    
    unlink("structure.pdb");
    unlink("structure.vrml");
    
    chdir("../");
    rmdir("$system_tmproot/pdb2vrml1$random_str");
}
?>
