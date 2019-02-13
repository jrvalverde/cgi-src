<?php
/**
 *	A Web Services Class to convert 3D structure data between a 
 *	number of different formats
 *
 *	Services provided:
 *    
 *   source_code(
 *   	in: none
 *	out: source code for the script (PHP source string)
 *   )
 *   
 *   bibliography(
 *   	in: none
 *	out: an array of bibliography strings
 *   )
 *   
 *   usage(
 *   	in: none
 *	out: usage description (html formatted string)
 *  }
 *   
 *   convert(
 *       in: content to convert (string)
 *	    input-format
 *	    output-format
 *	out: converted-content (string)
 *   )
 *   
 *   convert_URL(
 *       in: URL to convert (string)
 *	    input-format
 *	    output-format
 *	out: converted-content (string)
 *   )
 *
 *
 *	@package 	PDB-servlets
 *	@copyright 	EMBnet/CNB 13/05/2003
 *	@author		José R. Valverde (EMBnet/CNB) jrvalverde@es.embnet.org
 *	@version	$Id: babel-ws.php,v 1.2 2005/01/21 15:35:53 netadmin Exp $
 *
 *	$Log: babel-ws.php,v $
 *	Revision 1.2  2005/01/21 15:35:53  netadmin
 *	Fixed minor bugs... and installed pdb2vrml on the server
 *	
 *	Revision 1.1.1.2  2005/01/21 14:35:22  netadmin
 *	Added WS to pdb2vrml1
 *	
 *	Revision 1.1.1.1  2005/01/20 16:56:45  netadmin
 *	Added support for Web Services using nuSOAP
 *	
 */
	
// Pull in the NuSOAP code
require_once('lib/nusoap.php');

/////////////////////// GLOBAL VARIABLES //////////////////////////////////
	
// Location of the user data and result files:
// they should be WWW accessible, therefore we need
// to specify their location relative to the system /
// and to the www root (for the URLs):
$system_tmproot="/data/www/EMBnet/tmp";
$http_tmproot="/tmp";

// program locations
$babel_home="/opt/structure/babel";

// set to 0 for no debug output, or select a debug level
$debug = 0;

////////////////////////////////// START /////////////////////////////////
    
/*
 * Generate WS server
 */
$server = new soap_server();

// Initialize WSDL support
$server->configureWSDL('babel', 'urn:babel');

// Register the data structures used by the service
//	Input data
/*
 *	struct babel_input {
 *		string struct3D;
 *		string inputFormat;
 *		string outputFormat;
 *	}
 */
$server->wsdl->addComplexType(
    'babel_input',  	    	    // name
    'complexType',  	    	    // kind of type
    'struct',	    	    	    // type (e.g. struct, array)
    'all',
    '',     	    	    	    // encoding (e.g. SOAP-ENC:Array)
    array(  	    	    	    // components
	 'struct3D' => array('name' => 'struct3D', 'type' => 'xsd:string'),
	 'inputFormat' => array('name' => 'inputFormat', 'type' => 'xsd:string'),
	 'outputFormat' => array('name' => 'outputFormat', 'type' => 'xsd:string')
    )
);

//	Input URL
/*
 *	struct babel_input_url {
 *		string url;
 *		string inputFormat;
 *		string outputFormat;
 *	}
 */
$server->wsdl->addComplexType(
    'babel_input_url',
    'complexType',
    'struct',
    'all',
    '',
    array(
	 'struct3Durl' => array('name' => 'struct3Durl', 'type' => 'xsd:string'),
	 'inputFormat' => array('name' => 'inputFormat', 'type' => 'xsd:string'),
	 'outputFormat' => array('name' => 'outputFormat', 'type' => 'xsd:string')
    )
);

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
	'urn:babel',	    	    	    	    // namespace
	'urn:babel#source_code',    	    	    // soapaction
	'rpc',	    	    	    	    	    // invocation style
	'encoded',  	    	    	    	    // use
	'Returns the source code for this server'   // documentation
);

$server->register('bibliography',
	array(),
	array('return' => 'tns:bibliographyList'),
	'urn:babel',
	'urn:babel#bibliography',
	'rpc',
	'encoded', 
	'Return a list of bibliographic references to include in derived works'
);

$server->register('usage',
	array(),
	array('return' => 'xsd:string'),
	'urn:babel',
	'urn:babel#usage',
	'rpc',
	'encoded',
	'Report usage tips on this service'
);

$server->register('convert',
	array('babel_input' => 'tns:babel_input'),
	array('return' => 'xsd:string'),
	'urn:babel',
	'urn:babel#convert',
	'rpc',
	'encoded',
	'Convert 3D structure coordinates from input_format to output_format'
);

$server->register('convert_url',
	array('babel_input_url' => 'tns:babel_input_url'),
	array('return' => 'xsd:string'),
	'urn:babel',
	'urn:babel#convert_url',
	'rpc',
	'encoded',
	'Convert 3D structure coordinates from input_format to output_format'
);

// Call the service method to initiate the transaction and send the response
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

if(isset($log) and $log != ''){
	harness('babel',$server->headers['User-Agent'],$server->methodname,$server->request,$server->response,$server->result);
}

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
	$source = file_get_contents("./babel-ws.php");
	return $source;
}

/**
 * explain usage of this script
 *
 *  We include the documentation with the script. Any user willing to know
 * how to use it needs only call this method: they will get in return an
 * HTML'ized description of the server, its arguments, options and methods.
 *
 * @return string containing the usage instructions in HTML format
 */
function usage()
{
    return 
    "<CENTER><H1>Babel</H1></CENTER>\n" .
    "<P><STRONG>Babel</STRONG>: convert a molecular structure file between formats</P>\n" .
    "<P><H2>Usage:</H2>\n" .
    "<p><PRE>". 
    "string babel.convert_url(struct babel_input_url{\n". 
    "    string struct3Durl; // url/path/to/file.3d\n" . 
    "    string inputFormat;    // input_format\n" . 
    "    string outputFormat    // output_format\n});\n". 
    "string babel.convert_url(struct babel_input{\n". 
    "    string struct3D;    // contents of file.3d\n" . 
    "    string inputFormat;    // input_format\n" . 
    "    string outputFormat    // output_format\n});\n". 
    "string[] babel.bibliography();" .
    "</PRE>\n" .
    "<P>Valid formats are:</P>\n" .
    "<CENTER><TABLE CELLSPACING=\"4\">\n" .
    "<TR><TD ALIGN=\"top\"><TABLE BORDER=\"2\"<TR><TD COLSPAN=\"2\"><CENTER><STRONG>Input formats</STRONG></CENTER></TD></TR>\n" .
    "<TR><TD>prep</TD><TD>AMBER prep file</TD></TR>\n" .
    "<TR><TD>bs</TD><TD>Ball and Stick file</TD></TR>\n" .
    "<TR><TD>bgf</TD><TD>MSI BGF file</TD></TR>\n" .
    "<TR><TD>car</TD><TD>Biosym .CAR file</TD></TR>\n" .
    "<TR><TD>boog</TD><TD>Boogie file</TD></TR>\n" .
    "<TR><TD>caccrt</TD><TD>Cacao Cartesian file</TD></TR>\n" .
    "<TR><TD>cadpac</TD><TD>Cambridge CADPAC file</TD></TR>\n" .
    "<TR><TD>charmm</TD><TD>CHARMm file</TD></TR>\n" .
    "<TR><TD>c3d1</TD><TD>Chem3D Cartesian 1 file</TD></TR>\n" .
    "<TR><TD>c3d2</TD><TD>Chem3D Cartesian 2 file</TD></TR>\n" .
    "<TR><TD>cssr</TD><TD>CSD CSSR file</TD></TR>\n" .
    "<TR><TD>fdat</TD><TD>CSD FDAT file</TD></TR>\n" .
    "<TR><TD>gstat</TD><TD>CSD GSTAT file</TD></TR>\n" .
    "<TR><TD>dock</TD><TD>Dock Database file</TD></TR>\n" .
    "<TR><TD>dpdb</TD><TD>Dock PDB file</TD></TR>\n" .
    "<TR><TD>feat</TD><TD>Feature file</TD></TR>\n" .
    "<TR><TD>fract</TD><TD>Free Form Fractional file</TD></TR>\n" .
    "<TR><TD>gamout</TD><TD>GAMESS Output file</TD></TR>\n" .
    "<TR><TD>gzmat</TD><TD>Gaussian Z-Matrix file</TD></TR>\n" .
    "<TR><TD>gauout</TD><TD>Gaussian 92 Output file</TD></TR>\n" .
    "<TR><TD>g94</TD><TD>Gaussian 94 Output file</TD></TR>\n" .
    "<TR><TD>gr96A</TD><TD>GROMOS96 (A) file</TD></TR>\n" .
    "<TR><TD>gr96N</TD><TD>GROMOS96 (nm) file</TD></TR>\n" .
    "<TR><TD>hin</TD><TD>Hyperchem HIN file</TD></TR>\n" .
    "<TR><TD>sdf</TD><TD>MDL Isis SDF file</TD></TR>\n" .
    "<TR><TD>m3d</TD><TD>M3D file</TD></TR>\n" .
    "<TR><TD>macmol</TD><TD>Mac Molecule file</TD></TR>\n" .
    "<TR><TD>macmod</TD><TD>Macromodel file</TD></TR>\n" .
    "<TR><TD>micro</TD><TD>Micro World file</TD></TR>\n" .
    "<TR><TD>mm2in</TD><TD>MM2 Input file</TD></TR>\n" .
    "<TR><TD>mm2out</TD><TD>MM2 Output file</TD></TR>\n" .
    "<TR><TD>mm3</TD><TD>MM3 file</TD></TR>\n" .
    "<TR><TD>mmads</TD><TD>MMADS file</TD></TR>\n" .
    "<TR><TD>mdl</TD><TD>MDL MOLfile file</TD></TR>\n" .
    "<TR><TD>molen</TD><TD>MOLIN file</TD></TR>\n" .
    "<TR><TD>mopcrt</TD><TD>Mopac Cartesian file</TD></TR>\n" .
    "<TR><TD>mopint</TD><TD>Mopac Internal file</TD></TR>\n" .
    "<TR><TD>mopout</TD><TD>Mopac Output file</TD></TR>\n" .
    "<TR><TD>pcmod</TD><TD>PC Model file</TD></TR>\n" .
    "<TR><TD>pdb</TD><TD>PDB file</TD></TR>\n" .
    "<TR><TD>psin</TD><TD>PS-GVB Input file</TD></TR>\n" .
    "<TR><TD>psout</TD><TD>PS-GVB Output file</TD></TR>\n" .
    "<TR><TD>msf</TD><TD>Quanta MSF file</TD></TR>\n" .
    "<TR><TD>schakal</TD><TD>Schakal file</TD></TR>\n" .
    "<TR><TD>shelx</TD><TD>ShelX file</TD></TR>\n" .
    "<TR><TD>smiles</TD><TD>SMILES file</TD></TR>\n" .
    "<TR><TD>spar</TD><TD>Spartan file</TD></TR>\n" .
    "<TR><TD>semi</TD><TD>Spartan Semi-Empirical file</TD></TR>\n" .
    "<TR><TD>spmm</TD><TD>Spartan Mol. Mechanics file</TD></TR>\n" .
    "<TR><TD>mol</TD><TD>Sybyl Mol file</TD></TR>\n" .
    "<TR><TD>mol2</TD><TD>Sybyl Mol2 file</TD></TR>\n" .
    "<TR><TD>wiz</TD><TD>Conjure file</TD></TR>\n" .
    "<TR><TD>unixyz</TD><TD>UniChem XYZ file</TD></TR>\n" .
    "<TR><TD>xyz</TD><TD>XYZ file</TD></TR>\n" .
    "<TR><TD>xed</TD><TD>XED file</TD></TR>\n" .
    "</TABLE></TD>\n" .
    "\n" .    
    "<TD ALIGN=\"top\"><TABLE BORDER=\"2\"<TR><TD COLSPAN=\"2\"><CENTER><STRONG>Output formats</STRONG></CENTER></TD></TR>\n" .
    "<TR><TD>diag</TD><TD>DIAGNOTICS file</TD></TR>\n" .
    "<TR><TD>alc</TD><TD>Alchemy file</TD></TR>\n" .
    "<TR><TD>bs</TD><TD>Ball and Stick file</TD></TR>\n" .
    "<TR><TD>bgf</TD><TD>BGF file</TD></TR>\n" .
    "<TR><TD>bmin</TD><TD>Batchmin Command file</TD></TR>\n" .
    "<TR><TD>box</TD><TD>DOCK 3.5 box file</TD></TR>\n" .
    "<TR><TD>caccrt</TD><TD>Cacao Cartesian file</TD></TR>\n" .
    "<TR><TD>cacint</TD><TD>Cacao Internal file</TD></TR>\n" .
    "<TR><TD>cache</TD><TD>CAChe MolStruct file</TD></TR>\n" .
    "<TR><TD>c3d1</TD><TD>Chem3D Cartesian 1 file</TD></TR>\n" .
    "<TR><TD>c3d2</TD><TD>Chem3D Cartesian 2 file</TD></TR>\n" .
    "<TR><TD>cdct</TD><TD>ChemDraw Conn. Table file</TD></TR>\n" .
    "<TR><TD>dock</TD><TD>Dock Database file</TD></TR>\n" .
    "<TR><TD>wiz</TD><TD>Wizard file</TD></TR>\n" .
    "<TR><TD>contmp</TD><TD>Conjure Template file</TD></TR>\n" .
    "<TR><TD>cssr</TD><TD>CSD CSSR file</TD></TR>\n" .
    "<TR><TD>dpdb</TD><TD>Dock PDB file</TD></TR>\n" .
    "<TR><TD>feat</TD><TD>Feature file</TD></TR>\n" .
    "<TR><TD>fhz</TD><TD>Fenske-Hall ZMatrix file</TD></TR>\n" .
    "<TR><TD>gamin</TD><TD>Gamess Input file</TD></TR>\n" .
    "<TR><TD>gcart</TD><TD>Gaussian Cartesian file</TD></TR>\n" .
    "<TR><TD>gzmat</TD><TD>Gaussian Z-matrix file</TD></TR>\n" .
    "<TR><TD>gotmp</TD><TD>Gaussian Z-matrix tmplt file</TD></TR>\n" .
    "<TR><TD>gr96A</TD><TD>GROMOS96 (A) file</TD></TR>\n" .
    "<TR><TD>gr96N</TD><TD>GROMOS96 (nm) file</TD></TR>\n" .
    "<TR><TD>hin</TD><TD>Hyperchem HIN file</TD></TR>\n" .
    "<TR><TD>icon</TD><TD>Icon 8 file</TD></TR>\n" .
    "<TR><TD>idatm</TD><TD>IDATM file</TD></TR>\n" .
    "<TR><TD>sdf</TD><TD>MDL Isis SDF file</TD></TR>\n" .
    "<TR><TD>m3d</TD><TD>M3D file</TD></TR>\n" .
    "<TR><TD>macmol</TD><TD>Mac Molecule file</TD></TR>\n" .
    "<TR><TD>macmod</TD><TD>Macromodel file</TD></TR>\n" .
    "<TR><TD>micro</TD><TD>Micro World file</TD></TR>\n" .
    "<TR><TD>mm2in</TD><TD>MM2 Input file</TD></TR>\n" .
    "<TR><TD>mm2out</TD><TD>MM2 Ouput file</TD></TR>\n" .
    "<TR><TD>mm3</TD><TD>MM3 file</TD></TR>\n" .
    "<TR><TD>mmads</TD><TD>MMADS file</TD></TR>\n" .
    "<TR><TD>mdl</TD><TD>MDL Molfile file</TD></TR>\n" .
    "<TR><TD>miv</TD><TD>MolInventor file</TD></TR>\n" .
    "<TR><TD>mopcrt</TD><TD>Mopac Cartesian file</TD></TR>\n" .
    "<TR><TD>mopint</TD><TD>Mopac Internal file</TD></TR>\n" .
    "<TR><TD>csr</TD><TD>MSI Quanta CSR file</TD></TR>\n" .
    "<TR><TD>pcmod</TD><TD>PC Model file</TD></TR>\n" .
    "<TR><TD>pdb</TD><TD>PDB file</TD></TR>\n" .
    "<TR><TD>psz</TD><TD>PS-GVB Z-Matrix file</TD></TR>\n" .
    "<TR><TD>psc</TD><TD>PS-GVB Cartesian file</TD></TR>\n" .
    "<TR><TD>report</TD><TD>Report file</TD></TR>\n" .
    "<TR><TD>smiles</TD><TD>SMILES file</TD></TR>\n" .
    "<TR><TD>spar</TD><TD>Spartan file</TD></TR>\n" .
    "<TR><TD>mol</TD><TD>Sybyl Mol file</TD></TR>\n" .
    "<TR><TD>mol2</TD><TD>Sybyl Mol2 file</TD></TR>\n" .
    "<TR><TD>maccs</TD><TD>MDL Maccs file</TD></TR>\n" .
    "<TR><TD>torlist</TD><TD>Torsion List file</TD></TR>\n" .
    "<TR><TD>tinker</TD><TD>Tinker XYZ file</TD></TR>\n" .
    "<TR><TD>unixyz</TD><TD>UniChem XYZ file</TD></TR>\n" .
    "<TR><TD>xyz</TD><TD>XYZ file</TD></TR>\n" .
    "<TR><TD>xed</TD><TD>XED file</TD></TR>\n" .
    "</TABLE></TD></TR>\n" .
    "</TABLE></CENTER>\n" .
    "<BR><P>Returned file will be of mime type chemical/x-output_format</P>\n" .
    "<BR><P><STRONG>NOTE:</STRONG> you may use \"babel.source_code()\" to dowload this servlet's source code</P>";
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
		'Ref1',
		'Ref2...');
	return $biblio;
}

/**
 *  Convert a 3D structure referenced by a URL between formats.
 *
 *  This function gets a URL pointing to a 3D structure, an input format 
 * and an output format. The 3D structure pointed to by the URL must be
 * in the input format specified. This method will retrieve the structure
 * and convert it using 'babel' to the output format specified, returning
 * the converted structure as a string with embedded newlines. Both, input
 * and output format must be valid 'babel' formats (see '$this->usage()'
 * for details).
 *
 *  @param babel_input_url  a URL pointing to the structure, input and output
 *  	    formats as strings. In PHP this is implemented as an associative
 *  	    array (see babel_input_url)
 *
 *  @return a string with the converted structure
 *
 *  @exception	SOAP SERVER exceptions stating what went wrong
 */
function convert_url($babel_input_url)
{
    global $system_tmproot, $http_tmproot, $babel_home, $debug;

    $iformat = $babel_input_url['inputFormat'];
    $oformat = $babel_input_url['outputFormat'];
    $url = $babel_input_url['struct3Durl'];

    $random_str = rand(1000000, 9999999);
    set_tmp_dir( $random_str, $system_tmproot );
    
    $ok = get_URL($url);   // no debug
    if (strcmp($ok, "ok") != 0){
    	return new soap_fault('SERVER', '', $ok);
    }
    
    if ( do_conversion($babel_home, $iformat, $oformat) ) 
    {
	$converted = file_get_contents("./structure.out");
    	cleanup($random_str); // no debug
	//return new soapval('return', 'string', $converted);
	return $converted;
    }
    else 
    {
    	// raise a fault
	return new soap_fault('SERVER', '', "Error... Babel could not carry out the conversion", '');
    }
    
}

/**
 *  Convert a 3D structure between formats.
 *
 *  This function gets a string containing a 3D structure, an input format 
 * and an output format. The 3D structure contained in the string must be
 * in the input format specified. This method will process the structure
 * and convert it using 'babel' to the output format specified, returning
 * the converted structure as a string with embedded newlines. Both, input
 * and output format must be valid 'babel' formats (see '$this->usage()'
 * for details).
 *
 *  @param babel_input  a string with the 3-D structure, input and output
 *  	    formats as strings. In PHP this is implemented as an associative
 *  	    array (see babel_input_url)
 *
 *  @return a string with the converted structure
 *
 *  @exception	SOAP SERVER exceptions stating what went wrong
 */
function convert()
{
    global $system_tmproot, $http_tmproot, $babel_home, $debug;

    $iformat = $babel_input_url['inputFormat'];
    $oformat = $babel_input_url['outputFormat'];
    $raw = $babel_input['struct3D'];

    $random_str = rand(1000000, 9999999);
    set_tmp_dir( $random_str, $system_tmproot );
    file_put_contents('./structure.in', $raw);
    if ( do_conversion($babel_home, $iformat, $oformat) ) 
    {
	$converted = file_get_contents("./structure.out");
    	cleanup($random_str); // no debug
	return new soapval('return', 'string', $converted);
    }
    else 
    {
    	// raise a fault
	return new soap_fault('SERVER', '', "Error... Babel could not carry out the conversion", '');
    }
    
    cleanup($random_str);
}
//////////////////////////// PRIVATE METHODS ////////////////////////////////

//
//Create the tmp directory
//
function set_tmp_dir( $random_str, $system_tmproot )
{   	
	mkdir ("$system_tmproot/babel$random_str", 0755);
	chdir( "$system_tmproot/babel$random_str" ); 
}

//
// Get remote URL to process
//  	Return errors as a string so exceptions can be returned
//
function get_URL($url)
{   
    global $debug;

    if ( ! isset($url) ) 
    {
    	// if not set raise a fault
	return 'Please, submit the URL of the PDB file to process.';
    }
    if ( $debug == 1 ) echo "<H1>Called with URL=$url</H1>";
    
    // Retrieve URL contents into "structure.pdb" in current dir
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_URL, $url);

    $fp = fopen("./structure.in", "w");
    
    curl_setopt ($ch, CURLOPT_FILE, $fp);
    curl_setopt ($ch, CURLOPT_HEADER, 0);

    curl_exec ($ch);
    curl_close ($ch);
    fclose ($fp);

    if ($error = curl_error($ch)) 
    {
	return "Error ($error): can't retrieve the PDB file:";
    }
    
    if ( $debug == 1 ) 
    {
    	// Show what we got
    	echo "<PRE>";
	readfile("structure.in");
	echo "</PRE>";
    }
    return "ok";
    
}

//
// do the actual processing using babel
//
function do_conversion($babel_home, $iformat, $oformat)
{
    global $babel_home, $debug;
    
    putenv("BABEL_DIR=$babel_home");
    
    // check for errors and return true or false appropriately
    exec("$babel_home/babel -i$iformat structure.in -o$oformat structure.out", $str_output, $str_var);
    if ( $str_var==0 )
    {
	return true;
    }else
    {
    	if ( $debug == 1 ) print $str_output;
    	return false;
    }
}

//
// clean-up after we are done
//
function cleanup($random_str)
{
    global $debug;
    
    if ( $debug == 1 ) return;
    
    unlink("structure.in");
    unlink("structure.out");
    
    chdir("../");
    rmdir("$system_tmproot/babel$random_str");
}

/* auxiliary implementations for olde versions of PHP
function _file_put_contents($filename, $data) {
    $fp = fopen($filename);
    if(!$fp) {
	return new soap_fault('SERVER', '', 'file_put_contents cannot write in file.', E_USER_ERROR);
    }
    fwrite($fp, $data);
    fclose($fp);
}

function file_get_contents($file) {
   return implode(file($file));
}
*/
?>
