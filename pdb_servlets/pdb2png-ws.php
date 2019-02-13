<?php
// Retrieve URL from params
// If not, return a Web page with usage description
// Use CURL to get PDB file contents
// if not, output error
//
//	You need access to molscript to use this servlet.
//
// (C) EMBnet/CNB 13/05/2003
//	See http://www.es.embnet.org/Copyright-CSIC.html
//
// AUTHOR: José R. Valverde (EMBnet/CNB) jrvalverde@es.embnet.org
//	   David García Aristegui (EMBnet/CNB) david@es.embnet.org
//
// SEE ALSO:	http://www.pc.chemie.tu-darmstadt.de/research/vrml/pdb2vrml.html
//
// $Id: pdb2png-ws.php,v 1.1 2005/01/24 09:56:42 netadmin Exp $
// $Log: pdb2png-ws.php,v $
// Revision 1.1  2005/01/24 09:56:42  netadmin
// Added web services to PDB2VRML2 and PDB2PNG
//
// Revision 1.2  2005/01/21 15:35:54  netadmin
// Fixed minor bugs... and installed pdb2vrml on the server
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
$molauto="/opt/structure/bin/molauto";
$molscript="/opt/structure/bin/molscript";

// set to 0 for no debug output, or select a debug level
$debug=0;


////////////////////////////////// START /////////////////////////////////

require_once('lib/nusoap.php');

/*
 * Generate WS server
 */
$server = new soap_server();

// initialize WSDL support
$server->configureWSDL('pdb2png', 'urn:pdb2png');

// Register the data structures used by the service
//	Output bibliography list
/*
 *	string bibliographyList[]
 */
$server->wsdl->addComplexType(
	'bibliographyList',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'string[]')),
	'xsd:string'
);

// Register the methods

$server->register('source_code',    	    	    // method name
	array(),    	    	    	    	    // input parameters
	array('return' => 'xsd:string'),    	    // output parameters
	'urn:pdb2png',	    	    	    	    // namespace
	'urn:pdb2png#source_code',    	    	    // soapaction
	'rpc',	    	    	    	    	    // invocation style
	'encoded',  	    	    	    	    // use
	'Returns the source code for this server'   // documentation
);

$server->register('bibliography',
	array(),
	array('return' => 'tns:bibliographyList'),
	'urn:pdb2png',
	'urn:pdb2png#bibliography',
	'rpc',
	'encoded', 
	'Return a list of bibliographic references to include in derived works'
);

$server->register('usage',
	array(),
	array('return' => 'xsd:string'),
	'urn:pdb2png',
	'urn:pdb2png#usage',
	'rpc',
	'encoded',
	'Report usage tips on this service'
);

$server->register('PNG_from_URL',  // method name
    array('pdb_url' => 'xsd:string'),         // input parameters
    array('return' => 'xsd:string'),          // output parameters
    'urn:pdb2png',                          // namespace
    'urn:pdb2png#PNG_from_URL',  // soapaction
    'rpc',                                    // style
    'encoded',                                // use
    'Generate a PNG file containing the PDB structure provided'            // documentation
);

$server->register('PNG_from_PDB',  // method name
    array('pdb_data' => 'xsd:string'),    // input parameters
    array('return' => 'xsd:string'),      // output parameters
    'urn:pdb2png',                      // namespace
    'urn:pdb2png#PNG_from_PDB',  // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Generate a PNG file containing the PDB structure provided'            // documentation
);

// Call the service method to initiate the transaction and send the response
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

if(isset($log) and $log != ''){
	harness('pdb2png',$server->headers['User-Agent'],$server->methodname,$server->request,$server->response,$server->result);
}

exit;
    	
/////////////////////////// PUBLIC METHODS //////////////////////////////

/**
 *  return source code for this script
 *
 *  This method allows interested parties to get the source code for
 * this script. Since this is open source, we want to make it easy
 * for users to get/produce source code.
 *
 *  @return 	string containing the source code (with embedded newlines)
 */
function source_code() {
	$source = file_get_contents("./pdb2png-ws.php");
	return $source;
}

/**
 *  Return bibliographic references needed when using this server
 *
 *  We want users to have an easy way to determine which references they
 * must include in publications using this server. This method provides an
 * easy way to collect them... and to add to them by higher-level software.
 *
 *  Clients may then call this method, get the references as an array of
 * strings, and add additional strings to this array to append *their* own
 * references to the list.
 *
 *  @return an array of strings containing bibliographic references
 */
function bibliography()
{
	$biblio = array(
		'<A HREF=\"http://www.avatar.se/molscript\">http://www.avatar.se/molscript</A>',
		'<A HREF=\"http://www.avatar.se/molscript/doc/references.html\">http://www.avatar.se/molscript/doc/references.html</A>');
	return $biblio;
}

/**
 *  return source code for this script
 *
 *  This method allows interested parties to get the source code for
 * this script. Since this is open source, we want to make it easy
 * for users to get/produce source code.
 *
 *  @return 	string containing the source code (with embedded newlines)
 */
function usage()
{
    return "<CENTER><H1>pdb2png</H1></CENTER>" .
    "<P><STRONG>PDB2PNG</STRONG>: convert a PDB file to a PNG representation</P>" .
    "<P><STRONG>Usage:</STRONG><PRE>\n" .
    "string pdb2png->source_code()     // return source code of this server\n" .
    "string pdb2png->usage()           // return usage instructions (this message)\n" .
    "string pdb2png->bibliography()    // return bibliographic references\n" .
    "string pdb2png->PNG_from_PDB(string) // convert PDB data to PNG\n" .
    "string pdb2png->PNG_from_URL(string) // retrieve PDB data from URL and convert to PNG" .
    "</PRE>";
}

function PNG_from_URL($pdb_url)
{
    global $system_tmproot, $debug;
    
    // select a random name for the tmp dir and cd to it
    $random_str = rand(1000000, 9999999);
    set_tmp_dir( $random_str, $system_tmproot );

    // get URL to convert into "./structure.pdb"
    if ( $debug == 1 ) echo "<P>Getting remote URL</P>";
    
    $ok = get_URL($pdb_url);
    if (strcmp($ok, "ok") != 0){
    	return new soap_fault('SERVER', '', $ok);
    }

    if ( generate_png() ) 
    {
    	$world = file_get_contents("./structure.png");
    	cleanup($system_tmproot, $random_str);
	return $world;
    }
    else 
    {
    	// produce an error message
	echo "<html><body><h1>Error...</h1></body></html>";
    }
}

function PNG_from_PDB($pdb)
{
    global $system_tmproot, $debug;
    
    // select a random name for the tmp dir and cd to it
    $random_str = rand(1000000, 9999999);
    set_tmp_dir( $random_str, $system_tmproot );

    // write PDB data into "./structure.pdb"
    file_put_contents('./structure.pdb', $pdb);

    if ( generate_png() ) 
    {
    	$world = file_get_contents("./structure.png");
    	cleanup($system_tmproot, $random_str);
	return $world;
    }
    else 
    {
    	// produce an error message
	echo "<html><body><h1>Error...</h1></body></html>";
    }
}

/////////////////////////////// SUBROUTINES ////////////////////////////////

//
//Creating the tmp directory
//  	Return errors as a string so they can be returned as exceptions
//
function set_tmp_dir( $random_str, $system_tmproot )
{   	
	mkdir ("$system_tmproot/pdb2png$random_str", 0755);
	chdir( "$system_tmproot/pdb2png$random_str" ); 
}

//
// Get remote URL to process
// It comes in a WWW variable url=remote_url
//
function get_URL($url)
{   
    global $debug;
    
    if ( !isset($url) ) 
    {
    	// if not set print usage notice
	return 'Please, submit the URL of the PDB file to process.';
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
    
    if ($error = curl_error($ch)) 
    {
    	return 'Error ($error): can\'t retrieve the PDB file';
    }
    
    $size=filesize("./structure.pdb");
    if ( $size==0 )
    {
    	return 'Error: can\'t retrieve the PDB file';
    } else
    {
    	if ( $debug == 1 ) 
    	{
    	    // Show what we got
    	    echo "<PRE>";
	    readfile("./structure.pdb");
	    echo "</PRE>";
    	}
	return "ok";
    }
}

function generate_png()
{
    global $molauto, $molscript;
    
    // check for errors and return true or false appropriately
    system("$molauto -cylinder -turns -nice -cpk ./structure.pdb | $molscript -png > ./structure.png");
    
    if ( !file_exists("./structure.png") || 
    	(($size_png=filesize("./structure.png"))==0) )
    {
	return false;
    } else
    {
    	return true;
    }
}

//
// clean-up after we are done
//
function cleanup($system_tmproot, $random_str)
{
    global $debug;
    
    if ( $debug == 1 ) return;
    
    unlink("structure.pdb");
    unlink("structure.png");
    
    chdir("../");
    rmdir("$system_tmproot/pdb2png$random_str");
}
?>
