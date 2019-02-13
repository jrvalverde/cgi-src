<?php
    // Connect to db
    // check specified record exists (fetch it)
    // generate a form with default values for fields taken from DB entry
    // on submit update db calling db_edit.php

// show the user the available data sorted by key
    
// include DBMS credentials
include '../config.inc';
include '../utils.php';


function db_open($host, $user, $password, $db)
{
    if (! ($connection = @ mysql_connect($host, $user, $password)))
    	show_error();
	
    if (! mysql_select_db($db, $connection))
    	show_error();
    
    return $connection;
}

/** retrieve the data
 *
 * @param string $db	    	an open connection to the DBMS
 * @param string $id	    	ID to retrieve
 * @return $result  	Query result 	
 */
function db_query_accno($db, $id)
{
    global $debug;
    
    // Set up a query to show what we were requested
    if (($id == "") || (! my_is_int($id)))
    	err_invalid_request();
    else
    	// show all
	$query = "SELECT * FROM mut_nt WHERE rbdb_acc='$id'";

#    if ($debug) echo "<h3>$query</h3>";

    // run the query
    if (! ($result = @ mysql_query($query, $db)) )
    	show_error();

    return $result;
}


/** show an item as a table cell
 *
 * The field contents are formatted as a table cell, with provision for
 * display of additional information as Javascript content.
 *
 *  @param $data    contents to display
 *  @param $tip     tip to associate to the contents
 */
function show_field($field, $data, $tip)
{
    echo "<tr>".
    	 "\t<td>$field</td>\n" .
	 "\t<td><input name=\"$field\" size=\"70\" "
	     ."value=\"".$data."\">" .
	 "</td>\n".
	 "\t<td " .
             "onMouseOver=\"showtip(this,event,'$tip')\" " .
             "onMouseOut=\"hidetip()\"><a href=\"../rbdb_help.html\"><img src=\"../images/help.gif\" alt=\"help\"></a></td>\n".
	 "</tr>";
}

function show_country_field($field, $data, $tip)
{
    echo "<tr>".
    	 "\t<td>$field</td>\n" .
	 "\t<td>" .
    	"<select name=\"$field\">\n" .
	"   <option value=\"$data\" selected>$data</option>\n" .
	"   <option value=\"Afghanistan\">\n" .
	"   Afghanistan</option>\n" .
	"   <option value=\"Albania\">\n" .
	"   Albania</option>\n" .
	"   <option value=\"Algeria\">\n" .
	"   Algeria</option>\n" .
	"   <option value=\"American Samoa\">\n" .
	"   American Samoa</option>\n" .
	"   <option value=\"Andorra\">\n" .
	"   Andorra</option>\n" .
	"   <option value=\"Angola\">\n" .
	"   Angola</option>\n" .
	"   <option value=\"Anguilla\">\n" .
	"   Anguilla</option>\n" .
	"   <option value=\"Antarctica\">\n" .
	"   Antarctica</option>\n" .
	"   <option value=\"Antigua and Barbuda\">\n" .
	"   Antigua and Barbuda</option>\n" .
	"   <option value=\"Argentina\">\n" .
	"   Argentina</option>\n" .
	"   <option value=\"Armenia\">\n" .
	"   Armenia</option>\n" .
	"   <option value=\"Aruba\">\n" .
	"   Aruba</option>\n" .
	"   <option value=\"Ascension Island\">\n" .
	"   Ascension Island</option>\n" .
	"   <option value=\"Australia\">\n" .
	"   Australia</option>\n" .
	"   <option value=\"Austria\">\n" .
	"   Austria</option>\n" .
	"   <option value=\"Azerbaijani\">\n" .
	"   Azerbaijan</option>\n" .
	"   <option value=\"Azores\">\n" .
	"   Azores</option>\n" .
	"   <option value=\"Bahamas\">\n" .
	"   Bahamas</option>\n" .
	"   <option value=\"Bahrain\">\n" .
	"   Bahrain</option>\n" .
	"   <option value=\"Bangladesh\">\n" .
	"   Bangladesh</option>\n" .
	"   <option value=\"Barbados\">\n" .
	"   Barbados</option>\n" .
	"   <option value=\"Belarus\">\n" .
	"   Belarus</option>\n" .
	"   <option value=\"Belgium\">\n" .
	"   Belgium</option>\n" .
	"   <option value=\"Belize\">\n" .
	"   Belize</option>\n" .
	"   <option value=\"Benin\">\n" .
	"   Benin</option>\n" .
	"   <option value=\"Bermuda\">\n" .
	"   Bermuda</option>\n" .
	"   <option value=\"Bhutan\">\n" .
	"   Bhutan</option>\n" .
	"   <option value=\"Bolivia\">\n" .
	"   Bolivia</option>\n" .
	"   <option value=\"Bosnia and Herzegovina\">\n" .
	"   Bosnia and Herzegovina</option>\n" .
	"   <option value=\"Botswana\">\n" .
	"   Botswana</option>\n" .
	"   <option value=\"Bouvet Island\">\n" .
	"   Bouvet Island</option>\n" .
	"   <option value=\"Brazil\">\n" .
	"   Brazil</option>\n" .
	"   <option value=\"British Indian Ocean Territory\">\n" .
	"   British Indian Ocean Territory</option>\n" .
	"   <option value=\"British Virgin Islands\">\n" .
	"   British Virgin Islands</option>\n" .
	"   <option value=\"Brunei Darussalam\">\n" .
	"   Brunei Darussalam</option>\n" .
	"   <option value=\"Bulgaria\">\n" .
	"   Bulgaria</option>\n" .
	"   <option value=\"Burkina Faso\">\n" .
	"   Burkina Faso</option>\n" .
	"   <option value=\"Burma\">\n" .
	"   Burma</option>\n" .
	"   <option value=\"Burundi\">\n" .
	"   Burundi</option>\n" .
	"   <option value=\"Cambodia\">\n" .
	"   Cambodia</option>\n" .
	"   <option value=\"Cameroon\">\n" .
	"   Cameroon</option>\n" .
	"   <option value=\"Canada\">\n" .
	"   Canada</option>\n" .
	"   <option value=\"Canal Zone\">\n" .
	"   Canal Zone</option>\n" .
	"   <option value=\"Cape Verde\">\n" .
	"   Cape Verde</option>\n" .
	"   <option value=\"Cayman Islands\">\n" .
	"   Cayman Islands</option>\n" .
	"   <option value=\"Central African Republic\">\n" .
	"   Central African Republic</option>\n" .
	"   <option value=\"Chad\">\n" .
	"   Chad</option>\n" .
	"   <option value=\"Channel Islands\">\n" .
	"   Channel Islands</option>\n" .
	"   <option value=\"Chile\">\n" .
	"   Chile</option>\n" .
	"   <option value=\"China\">\n" .
	"   China</option>\n" .
	"   <option value=\"Christmas Island\">\n" .
	"   Christmas Island</option>\n" .
	"   <option value=\"Colombia\">\n" .
	"   Colombia</option>\n" .
	"   <option value=\"Comoros\">\n" .
	"   Comoros</option>\n" .
	"   <option value=\"Congo, Democratic People's Republic\">\n" .
	"   Congo, Democratic People's Republic</option>\n" .
	"   <option value=\"Congo, Republic of\">\n" .
	"   Congo, Republic of</option>\n" .
	"   <option value=\"Cook Islands\">\n" .
	"   Cook Islands</option>\n" .
	"   <option value=\"Corsica\">\n" .
	"   Corsica</option>\n" .
	"   <option value=\"Costa Rica\">\n" .
	"   Costa Rica</option>\n" .
	"   <option value=\"Ivory Coast\">\n" .
	"   Cote D'Ivoire</option>\n" .
	"   <option value=\"Croatia/Hrvastka\">\n" .
	"   Croatia/Hrvastka</option>\n" .
	"   <option value=\"Cuba\">\n" .
	"   Cuba</option>\n" .
	"   <option value=\"Cyprus\">\n" .
	"   Cyprus</option>\n" .
	"   <option value=\"Czech Republic\">\n" .
	"   Czech Republic</option>\n" .
	"   <option value=\"Denmark\">\n" .
	"   Denmark</option>\n" .
	"   <option value=\"Djibouti\">\n" .
	"   Djibouti</option>\n" .
	"   <option value=\"Dominica\">\n" .
	"   Dominica</option>\n" .
	"   <option value=\"Dominican Republic\">\n" .
	"   Dominican Republic</option>\n" .
	"   <option value=\"East Timor\">\n" .
	"   East Timor</option>\n" .
	"   <option value=\"Ecuador\">\n" .
	"   Ecuador</option>\n" .
	"   <option value=\"Egypt\">\n" .
	"   Egypt</option>\n" .
	"   <option value=\"El Salvador\">\n" .
	"   El Salvador</option>\n" .
	"   <option value=\"England UK\">\n" .
	"   England UK</option>\n" .
	"   <option value=\"Equatorial Guinea\">\n" .
	"   Equatorial Guinea</option>\n" .
	"   <option value=\"Eritrea\">\n" .
	"   Eritrea</option>\n" .
	"   <option value=\"Estonia\">\n" .
	"   Estonia</option>\n" .
	"   <option value=\"Ethiopia\">\n" .
	"   Ethiopia</option>\n" .
	"   <option value=\"Falkand Islands (Malvina)\">\n" .
	"   Falkland Islands (Malvina)</option>\n" .
	"   <option value=\"Faroe Islands\">\n" .
	"   Faroe Islands</option>\n" .
	"   <option value=\"Federal State of Micronesia\">\n" .
	"   Federal State of Micronesia</option>\n" .
	"   <option value=\"Fiji Islands\">\n" .
	"   Fiji</option>\n" .
	"   <option value=\"Finland\">\n" .
	"   Finland</option>\n" .
	"   <option value=\"French Polynesia\">\n" .
	"   French Polynesia</option>\n" .
	"   <option value=\"France\">\n" .
	"   France</option>\n" .
	"   <option value=\"French Guiana\">\n" .
	"   French Guiana</option>\n" .
	"   <option value=\"French Polynesia\">\n" .
	"   French Polynesia</option>\n" .
	"   <option value=\"French Southern Territories\">\n" .
	"   French Southern Territories</option>\n" .
	"   <option value=\"Gabon\">\n" .
	"   Gabon</option>\n" .
	"   <option value=\"Gambia\">\n" .
	"   Gambia</option>\n" .
	"   <option value=\"Georgia\">\n" .
	"   Georgia</option>\n" .
	"   <option value=\"Germany\">\n" .
	"   Germany</option>\n" .
	"   <option value=\"Ghana\">\n" .
	"   Ghana</option>\n" .
	"   <option value=\"Gibraltar\">\n" .
	"   Gibraltar</option>\n" .
	"   <option value=\"Great Britain\">\n" .
	"   Great Britain</option>\n" .
	"   <option value=\"Greece\">\n" .
	"   Greece</option>\n" .
	"   <option value=\"Greenland\">\n" .
	"   Greenland</option>\n" .
	"   <option value=\"Grenada\">\n" .
	"   Grenada</option>\n" .
	"   <option value=\"Grenadines\">\n" .
	"   Grenadines</option>\n" .
	"   <option value=\"Gt Britain\">\n" .
	"   Gt Britain</option>\n" .
	"   <option value=\"Guadeloupe\">\n" .
	"   Guadeloupe</option>\n" .
	"   <option value=\"Guam\">\n" .
	"   Guam</option>\n" .
	"   <option value=\"Guatemala\">\n" .
	"   Guatemala</option>\n" .
	"   <option value=\"Guernsey\">\n" .
	"   Guernsey</option>\n" .
	"   <option value=\"Guinea\">\n" .
	"   Guinea</option>\n" .
	"   <option value=\"Guinea-Bissau\">\n" .
	"   Guinea-Bissau</option>\n" .
	"   <option value=\"Guyana\">\n" .
	"   Guyana</option>\n" .
	"   <option value=\"Haiti\">\n" .
	"   Haiti</option>\n" .
	"   <option value=\"Heard and McDonald Islands\">\n" .
	"   Heard and McDonald Islands</option>\n" .
	"   <option value=\"Holland\">\n" .
	"   Holland</option>\n" .
	"   <option value=\"Holy See (City Vatican State)\">\n" .
	"   Holy See (City Vatican State)</option>\n" .
	"   <option value=\"Honduras\">\n" .
	"   Honduras</option>\n" .
	"   <option value=\"Hong Kong\">\n" .
	"   Hong Kong</option>\n" .
	"   <option value=\"Hungary\">\n" .
	"   Hungary</option>\n" .
	"   <option value=\"Iceland\">\n" .
	"   Iceland</option>\n" .
	"   <option value=\"India\">\n" .
	"   India</option>\n" .
	"   <option value=\"Indochina\">\n" .
	"   Indochina</option>\n" .
	"   <option value=\"Indonesia\">\n" .
	"   Indonesia</option>\n" .
	"   <option value=\"Iran\">\n" .
	"   Iran</option>\n" .
	"   <option value=\"Iraq\">\n" .
	"   Iraq</option>\n" .
	"   <option value=\"Ireland\">\n" .
	"   Ireland</option>\n" .
	"   <option value=\"Israel\">\n" .
	"   Israel</option>\n" .
	"   <option value=\"Italy\">\n" .
	"   Italy</option>\n" .
	"   <option value=\"Jamaica\">\n" .
	"   Jamaica</option>\n" .
	"   <option value=\"Japan\">\n" .
	"   Japan</option>\n" .
	"   <option value=\"Jordan\">\n" .
	"   Jordan</option>\n" .
	"   <option value=\"Kazakhstan\">\n" .
	"   Kazakhstan</option>\n" .
	"   <option value=\"Kenya\">\n" .
	"   Kenya</option>\n" .
	"   <option value=\"Kirghizstan\">\n" .
	"   Kirghizstan</option>\n" .
	"   <option value=\"Kiribati\">\n" .
	"   Kiribati</option>\n" .
	"   <option value=\"Kuwait\">\n" .
	"   Kuwait</option>\n" .
	"   <option value=\"Kyrgyztan\">\n" .
	"   Kyrgyzstan</option>\n" .
	"   <option value=\"Lao People's Democratic Republic\">\n" .
	"   Lao People's Democratic Republic</option>\n" .
	"   <option value=\"Laos\">\n" .
	"   Laos</option>\n" .
	"   <option value=\"Latvia\">\n" .
	"   Latvia</option>\n" .
	"   <option value=\"Lebanon\">\n" .
	"   Lebanon</option>\n" .
	"   <option value=\"Leeward Isle\">\n" .
	"   Leeward Isle</option>\n" .
	"   <option value=\"Lesotho\">\n" .
	"   Lesotho</option>\n" .
	"   <option value=\"Liberia\">\n" .
	"   Liberia</option>\n" .
	"   <option value=\"Libya\">\n" .
	"   Libya</option>\n" .
	"   <option value=\"Liechtenstein\">\n" .
	"   Liechtenstein</option>\n" .
	"   <option value=\"Lithuania\">\n" .
	"   Lithuania</option>\n" .
	"   <option value=\"Luxembourg\">\n" .
	"   Luxembourg</option>\n" .
	"   <option value=\"Macao\">\n" .
	"   Macao</option>\n" .
	"   <option value=\"Macedonia, Former Yugoslav Republic\">\n" .
	"   Macedonia, Former Yugoslav Republic</option>\n" .
	"   <option value=\"Madagascar\">\n" .
	"   Madagascar</option>\n" .
	"   <option value=\"Madeira Island\">\n" .
	"   Madeira Island</option>\n" .
	"   <option value=\"Malawi\">\n" .
	"   Malawi</option>\n" .
	"   <option value=\"Malaysia\">\n" .
	"   Malaysia</option>\n" .
	"   <option value=\"Maldives\">\n" .
	"   Maldives</option>\n" .
	"   <option value=\"Mali\">\n" .
	"   Mali</option>\n" .
	"   <option value=\"Malta\">\n" .
	"   Malta</option>\n" .
	"   <option value=\"Marshall Islands\">\n" .
	"   Marshall Islands</option>\n" .
	"   <option value=\"Martinique\">\n" .
	"   Martinique</option>\n" .
	"   <option value=\"Mauritania\">\n" .
	"   Mauritania</option>\n" .
	"   <option value=\"Mauritius\">\n" .
	"   Mauritius</option>\n" .
	"   <option value=\"Mayotte\">\n" .
	"   Mayotte</option>\n" .
	"   <option value=\"Mexico\">\n" .
	"   Mexico</option>\n" .
	"   <option value=\"Micronesia, Federal State of\">\n" .
	"   Micronesia, Federal State of</option>\n" .
	"   <option value=\"Moldova, Republic of\">\n" .
	"   Moldova, Republic of</option>\n" .
	"   <option value=\"Monaco\">\n" .
	"   Monaco</option>\n" .
	"   <option value=\"Mongolia\">\n" .
	"   Mongolia</option>\n" .
	"   <option value=\"Montenegro\">\n" .
	"   Montenegro</option>\n" .
	"   <option value=\"Montserrat\">\n" .
	"   Montserrat</option>\n" .
	"   <option value=\"Morocco\">\n" .
	"   Morocco</option>\n" .
	"   <option value=\"Mozambique\">\n" .
	"   Mozambique</option>\n" .
	"   <option value=\"Myanmar\">\n" .
	"   Myanmar</option>\n" .
	"   <option value=\"Nambia\">\n" .
	"   Nambia</option>\n" .
	"   <option value=\"Namibia\">\n" .
	"   Namibia</option>\n" .
	"   <option value=\"Nauru Island\">\n" .
	"   Nauru Island</option>\n" .
	"   <option value=\"Nepal\">\n" .
	"   Nepal</option>\n" .
	"   <option value=\"Netherlands\">\n" .
	"   Netherlands</option>\n" .
	"   <option value=\"Netherlands Antilles\">\n" .
	"   Netherlands Antilles</option>\n" .
	"   <option value=\"New Caledonia\">\n" .
	"   New Caledonia</option>\n" .
	"   <option value=\"New Zealand\">\n" .
	"   New Zealand</option>\n" .
	"   <option value=\"Nicaragua\">\n" .
	"   Nicaragua</option>\n" .
	"   <option value=\"Niger\">\n" .
	"   Niger</option>\n" .
	"   <option value=\"Nigeria\">\n" .
	"   Nigeria</option>\n" .
	"   <option value=\"Niue\">\n" .
	"   Niue</option>\n" .
	"   <option value=\"Northern Ireland\">\n" .
	"   Northern Ireland</option>\n" .
	"   <option value=\"Norfolk Island\">\n" .
	"   Norfolk Island</option>\n" .
	"   <option value=\"North Korea\">\n" .
	"   North Korea</option>\n" .
	"   <option value=\"Northern Mariana Island\">\n" .
	"   Northern Mariana Island</option>\n" .
	"   <option value=\"Norway\">\n" .
	"   Norway</option>\n" .
	"   <option value=\"Oman\">\n" .
	"   Oman</option>\n" .
	"   <option value=\"Pakistan\">\n" .
	"   Pakistan</option>\n" .
	"   <option value=\"Palau\">\n" .
	"   Palau</option>\n" .
	"   <option value=\"Panama\">\n" .
	"   Panama</option>\n" .
	"   <option value=\"Papua New Gnea\">\n" .
	"   Papua New Guinea</option>\n" .
	"   <option value=\"Paraguay\">\n" .
	"   Paraguay</option>\n" .
	"   <option value=\"People's Democartic Republic of Yemen\">\n" .
	"   People's Democartic Republic of Yemen</option>\n" .
	"   <option value=\"People's Democratic Republic of Lao\">\n" .
	"   People's Democratic Republic of Lao</option>\n" .
	"   <option value=\"Peru\">\n" .
	"   Peru</option>\n" .
	"   <option value=\"Philippines\">\n" .
	"   Philippines</option>\n" .
	"   <option value=\"Pitcairn Island\">\n" .
	"   Pitcairn Island</option>\n" .
	"   <option value=\"Poland\">\n" .
	"   Poland</option>\n" .
	"   <option value=\"Portugal\">\n" .
	"   Portugal</option>\n" .
	"   <option value=\"Qatar\">\n" .
	"   Qatar</option>\n" .
	"   <option value=\"Republic of Armenia\">\n" .
	"   Republic of Armenia</option>\n" .
	"   <option value=\"Republic Moldava\">\n" .
	"   Republic Moldava</option>\n" .
	"   <option value=\"Republic of Moldova\">\n" .
	"   Republic of Moldova</option>\n" .
	"   <option value=\"Reunion\">\n" .
	"   Reunion</option>\n" .
	"   <option value=\"Romania\">\n" .
	"   Romania</option>\n" .
	"   <option value=\"Russian Federation\">\n" .
	"   Russian Federation</option>\n" .
	"   <option value=\"Rwanda\">\n" .
	"   Rwanda</option>\n" .
	"   <option value=\"Saint Helena\">\n" .
	"   Saint Helena</option>\n" .
	"   <option value=\"Saint Kitts and Nevis\">\n" .
	"   Saint Kitts and Nevis</option>\n" .
	"   <option value=\"Saint Lucia\">\n" .
	"   Saint Lucia</option>\n" .
	"   <option value=\"Saint Pierre and Miquelon\">\n" .
	"   Saint Pierre and Miquelon</option>\n" .
	"   <option value=\"Saint Vincent and the Grenadines\">\n" .
	"   Saint Vincent and the Grenadines</option>\n" .
	"   <option value=\"Samoa\">\n" .
	"   Samoa</option>\n" .
	"   <option value=\"San Marino\">\n" .
	"   San Marino</option>\n" .
	"   <option value=\"Sao Tome and Principe\">\n" .
	"   Sao Tome and Principe</option>\n" .
	"   <option value=\"Saudi Arabia\">\n" .
	"   Saudi Arabia</option>\n" .
	"   <option value=\"Scotland Uk\">\n" .
	"   Scotland Uk</option>\n" .
	"   <option value=\"Senegal\">\n" .
	"   Senegal</option>\n" .
	"   <option value=\"Serbia\">\n" .
	"   Serbia</option>\n" .
	"   <option value=\"Seychelles\">\n" .
	"   Seychelles</option>\n" .
	"   <option value=\"Sierra Leone\">\n" .
	"   Sierra Leone</option>\n" .
	"   <option value=\"Singapore\">\n" .
	"   Singapore</option>\n" .
	"   <option value=\"Slovak Republic\">\n" .
	"   Slovak Republic</option>\n" .
	"   <option value=\"Slovenia\">\n" .
	"   Slovenia</option>\n" .
	"   <option value=\"Solomon Islands\">\n" .
	"   Solomon Islands</option>\n" .
	"   <option value=\"Somalia\">\n" .
	"   Somalia</option>\n" .
	"   <option value=\"South Africa\">\n" .
	"   South Africa</option>\n" .
	"   <option value=\"South Georgia and the South Sandwich Islands\">\n" .
	"   South Georgia and the South Sandwich Islands</option>\n" .
	"   <option value=\"South Korea\">\n" .
	"   South Korea</option>\n" .
	"   <option value=\"Spain\">\n" .
	"   Spain</option>\n" .
	"   <option value=\"Sri Lanka\">\n" .
	"   Sri Lanka</option>\n" .
	"   <option value=\"Sudan\">\n" .
	"   Sudan</option>\n" .
	"   <option value=\"Suriname\">\n" .
	"   Suriname</option>\n" .
	"   <option value=\"Svalbard And Jan Mayen Island\">\n" .
	"   Svalbard And Jan Mayen Island</option>\n" .
	"   <option value=\"Swaziland\">\n" .
	"   Swaziland</option>\n" .
	"   <option value=\"Sweden\">\n" .
	"   Sweden</option>\n" .
	"   <option value=\"Switzerland\">\n" .
	"   Switzerland</option>\n" .
	"   <option value=\"Syria\">\n" .
	"   Syria</option>\n" .
	"   <option value=\"Syrian\">\n" .
	"   Syrian</option>\n" .
	"   <option value=\"Tahiti\">\n" .
	"   Tahiti</option>\n" .
	"   <option value=\"Taiwan Roc\">\n" .
	"   Taiwan Roc</option>\n" .
	"   <option value=\"Tadzhikistan\">\n" .
	"   Tadzhikistan</option>\n" .
	"   <option value=\"Tanzania\">\n" .
	"   Tanzania</option>\n" .
	"   <option value=\"Thailand\">\n" .
	"   Thailand</option>\n" .
	"   <option value=\"The Bahamas\">\n" .
	"   The Bahamas</option>\n" .
	"   <option value=\"Togo\">\n" .
	"   Togo</option>\n" .
	"   <option value=\"Tokelau\">\n" .
	"   Tokelau</option>\n" .
	"   <option value=\"Tonga\">\n" .
	"   Tonga</option>\n" .
	"   <option value=\"Transkei\">\n" .
	"   Transkei</option>\n" .
	"   <option value=\"Trinidad and Tobgo\">\n" .
	"   Trinidad and Tobago</option>\n" .
	"   <option value=\"Tunisia\">\n" .
	"   Tunisia</option>\n" .
	"   <option value=\"Turkey\">\n" .
	"   Turkey</option>\n" .
	"   <option value=\"Turkmenistan\">\n" .
	"   Turkmenistan</option>\n" .
	"   <option value=\"Turks and Caicos Islands\">\n" .
	"   Turks and Caicos Islands</option>\n" .
	"   <option value=\"Tuvalu\">\n" .
	"   Tuvalu</option>\n" .
	"   <option value=\"Uganda\">\n" .
	"   Uganda</option>\n" .
	"   <option value=\"Ukraine\">\n" .
	"   Ukraine</option>\n" .
	"   <option value=\"United Arab Emirates\">\n" .
	"   United Arab Emirates</option>\n" .
	"   <option value=\"United Kingdom\">\n" .
	"   United Kingdom</option>\n" .
	"   <option value=\"United States of America\">\n" .
	"   United States of America</option>\n" .
	"   <option value=\"US Minor Outlying Islands\">\n" .
	"   US Minor Outlying Islands</option>\n" .
	"   <option value=\"Upper Volta\">\n" .
	"   Upper Volta</option>\n" .
	"   <option value=\"Uruguay\">\n" .
	"   Uruguay</option>\n" .
	"   <option value=\"Uzbekistan\">\n" .
	"   Uzbekistan</option>\n" .
	"   <option value=\"Vanuatu\">\n" .
	"   Vanuatu</option>\n" .
	"   <option value=\"Vatican City\">\n" .
	"   Vatican City</option>\n" .
	"   <option value=\"Venezuela\">\n" .
	"   Venezuela</option>\n" .
	"   <option value=\"Viet Nam\">\n" .
	"   Viet Nam</option>\n" .
	"   <option value=\"Wales Uk\">\n" .
	"   Wales Uk</option>\n" .
	"   <option value=\"West Indies\">\n" .
	"   West Indies</option>\n" .
	"   <option value=\"Virgin Island (British)\">\n" .
	"   Virgin Island (British)</option>\n" .
	"   <option value=\"Virgin Islands (USA)\">\n" .
	"   Virgin Islands (USA)</option>\n" .
	"   <option value=\"Wallis And Futuna Islands\">\n" .
	"   Wallis And Futuna Islands</option>\n" .
	"   <option value=\"Western Sahara\">\n" .
	"   Western Sahara</option>\n" .
	"   <option value=\"Western Samoa\">\n" .
	"   Western Samoa</option>\n" .
	"   <option value=\"Windward Island\">\n" .
	"   Windward Island</option>\n" .
	"   <option value=\"Yemen\">\n" .
	"   Yemen</option>\n" .
	"   <option value=\"Yugoslavia\">\n" .
	"   Yugoslavia</option>\n" .
	"   <option value=\"Zaire\">\n" .
	"   Zaire</option>\n" .
	"   <option value=\"Zambia\">\n" .
	"   Zambia</option>\n" .
	"   <option value=\"Zimbabwe\">\n" .
	"   Zimbabwe</option>\n" .
	"   <option value=\"\">\n" .
	"   </option>\n" .
	 "</select></td>\n".
	 "\t<td " .
             "onMouseOver=\"showtip(this,event,'$tip')\" " .
             "onMouseOut=\"hidetip()\"><a href=\"../rbdb_help.html\"><img src=\"../images/help.gif\" alt=\"help\"></a></td>\n".
	 "</tr>";
}

function show_sex_field($field, $data, $tip)
{
    echo "<tr>".
    	 "\t<td>$field</td>\n" .
	 "\t<td><Select name=\"$field\"> ";
    if ($data == "M")
	 echo "  	<option value=\"M\" selected>Male</option>" .
	      "	<option value=\"F\">Female</option>" .
	      "	<option value=\"\"></option>" ;
    else if ($data == "F")
	 echo "  	<option value=\"M\">Male</option>" .
	      "	<option value=\"F\" selected>Female</option>" .
	      "	<option value=\"\"></option>" ;
    else
	 echo "  	<option value=\"M\">Male</option>" .
	      "	<option value=\"F\">Female</option>" .
	      "	<option value=\"\" selected></option>" ;

    echo "</select></td>\n".
	 "\t<td " .
             "onMouseOver=\"showtip(this,event,'$tip')\" " .
             "onMouseOut=\"hidetip()\"><a href=\"../rbdb_help.html\"><img src=\"../images/help.gif\" alt=\"help\"></a></td>\n".
	 "</tr>";
}

function show_yesno_field($field, $data, $tip)
{
    echo "<tr>".
    	 "\t<td>$field</td>\n\t<td>$data";
    if ($data == "Yes")
	echo "<select name=\"$field\">" .
	     "  <option value=\"Yes\" selected>Yes</option>".
	     "  <option value=\"No\">No</option>" .
    	     "  <option value=\"\"></option>";
    else if ($data == "No")
	echo "<select name=\"$field\">" .
	     "  <option value=\"Yes\">Yes</option>".
	     "  <option value=\"No\" selected>No</option>" .
    	     "  <option value=\"\"></option>";
    else       
	echo "<select name=\"$field\">" .
	     "  <option value=\"Yes\">Yes</option>".
	     "  <option value=\"No\">No</option>" .
    	     "  <option value=\"\" selected></option>";
    echo "</select></td>\n".
	 "\t<td " .
             "onMouseOver=\"showtip(this,event,'$tip')\" " .
             "onMouseOut=\"hidetip()\"><a href=\"../rbdb_help.html\"><img src=\"../images/help.gif\" alt=\"help\"></a></td>\n".
	 "</tr>";
}

function show_result($id, $result)
{
    // did we find anything?
    $count = @ mysql_num_rows($result);
    if ( $count != 0)
    {
    	// yes, show results as a table and allow to edit the results
    	echo "<center><h1>This is the record you selected</h1>\n";
    	echo "<h2>Are you sure you want to edit this record?</h2></center>\n";
	echo "<p>Please, review it carefully before confirming any changes.</p>\n";
	
	// create table headings
    	echo "<table width=\"100%\" bgcolor=\"lightblue\" ".
	     "align=\"center\" border=\"2\" ".
             "cellspacing=\"0\" cellpadding=\"5\">\n" .
             "<tr><th>Field</th><th>Value</th><th>Help</th></tr>\n";
	
    	// fetch each database table row of the results
	while ($row = mysql_fetch_array($result))
	{
	    // display the data as a table row
	    echo "<tr>\n";
		show_field('location', $row["location"], '<b>Location:</b><br />Exon (E) and intron (I) number<br />according to cDNA sequence<br />NCBI (NM_000321.1)');
		show_field('genomic', $row["genomic"], '<b>Genomic:</b><br />Description follows the recommendations<br />published by Donnan and Antonarakis (2000)<br />using the genomic sequence<br />GenBank: L11910.1');
		show_field('cdna', $row["cdna"],'<b>cDNA:</b><br />changes as in Donnen and Antonarakis (2000),<br />using the cDNA sequence NCBI: NM_000321.1.');
    		show_field('protein', $row["protein"], '<b>Protein:</b><br />Deduced changes at the protein level<br />follow the recommendations of Dunnen and Antonarakis (2000)<br />using the protein sequence NCBI: NP_000312.1.');
		show_field('consequence', $row["consequence"], '<b>Consequence:</b><br />predicted consequences are as follows:<br /><ul><li>regulation (promoter)</li><li>FS (trunckating frameshift)</li><li>IF (non-truncating inframe changes)</li><li>MS (missense changes)</li><li>NS (non-sense trunckating mutations)</li><li>SP (trunckating mutations affecting splicing sites)</li><li>SP-IF (in frame exon deletion due to splicing mutations)</li><li>SP-MS (mutations affecting the last two nucleotides in exon can either be considered as  MS or splicing mutations).</li></ul>');
		show_field('type', $row["type"], '<b>Type of mutation:</b><ul><li>DUP (duplication)</li><li>IN (insertion)</li><li>DE (deletion)</li><li>I_D (complex insertion and deletion)</li><li>PM (point mutation)</li></ul>');
		show_field('origin', $row["origin"], '<b>Origin:</b><br />Germline or somatic.');
		show_field('sample', $row["sample"], '<b>Sample:</b><ul><li>PB (peripheral blood for germline)</li><li>retino (retinoblastoma)</li><li>other (other  tumors)</li></ul>');
		show_field('phenotype', $row["phenotype"], '<b>Phenotype:</b><ul><li>B (sporadic bilateral)</li><li>BF (bilateral familiar)</li><li>U (sporadic unilateral)</li><li>UF (unilateral familiar)</li><li>UMF (unilateral multifocal)</li><li>LP (familiar with low penetrance)</li></ul>');
		show_sex_field('sex', $row["sex"], '<b>Sex:</b><ul><li>F (female)</li><li>M (male)</li></ul>');
		show_field('age_months', $row["age_months"], '<b>Age (months):</b><br />at diagnosis or treatment in months.');
		show_country_field('country', $row["country"], '<b>Country:</b><br />of origin of probands or <br />of the main research group<br />in publications.');
		show_field('reference', $row["reference"], '<b>Reference</b>');
		show_field('pm_id', $row["pm_id"], '<b>PubMed ID:</b><br />Click to retrieve the abstract');
		show_field('patient_id', $row["patient_id"], '<b>Patient ID:</b><br />as reported in publication.');
		show_yesno_field('l_db', $row["l_db"], '<b>L-DB</b>');
		show_field('remarks', $row["remarks"], '<b>Remarks:</b><br />any observation which can be useful in the context of a given mutation');
	    echo "</tr>\n";
	}
	echo "</table>\n";
    }
    else 
    {
    	// no data was returned by the query
	// show an appropriate message
    	err_invalid_request($id);
    }
}

function prompt_for_edit($id)
{
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";
    echo "<table width=\"100%\" bgcolor=\"lightblue\" align=\"center\">\n";
    echo "<tr>\n    <td align=\"center\"><input type=\"submit\" ".
         " value=\"Apply changes\">&nbsp;</td>\n" .
	 "    <td align=\"center\"><input type=\"button\" name=\"Cancel\" ".
	 " value=\"Cancel\" onClick=\"history.go(-1)\"></td>\n</tr>\n";
    echo "</table></form>\n";
}

// We use a special header with the style and JavaScrip code needed to
// enhance the user experience.
function show_header()
{
    echo "<head>\n";
    echo "\t<title>RBDB Query results</title>\n";
    $showtips = file_get_contents("../js/showtips_inline.js");
    echo $showtips;
    echo "</head>\n";
}

function show_copyright()
{
    echo "\n<hr>\n" .
    	 "<table>\n<tr>\n" .
	 "<td align=\"left\">Data compilation &copy; Angel Pesta&ntilde;a, 2005</td>\n" .
	 "<td align=\"right\">Please cite (manuscript in preparation) to reference this data</td>\n" .
    	"</tr>\n</table>\n";
}

function err_invalid_request($id)
{
    echo "<h3><font color=\"red\">You supplied an invalid record ID ($id) for deletion</font></h3>";
    exit;
}

function my_is_int($x) {
   return (is_numeric($x) ? intval($x) == $x : false);
}
// Here we go

$id = escapeshellcmd($_GET["id"]);

echo "<html>\n";
show_header();
echo "<body bgcolor=\"#ccccff\">\n";
echo "<div id=\"tooltip\" style=\"position:absolute;visibility:hidden;border:1px solid black;font-size:12px;layer-background-color:lightyellow;background-color:lightyellow;padding:1px\"></div>\n";

if (($id == "") || (! my_is_int($id))) {
    err_invalid_request($id);
    exit;
}

// Prepare form with JavaScript validation
echo "<form method=\"GET\" action=\"db_edit.php?id=$id\" onsubmit=\"
	this.protein.optional = true;
	this.consequence.optional = true;
	this.sex.optional = true;
	this.age_months.optional = true;
	this.age_months.numeric = true;
	this.country.optional = true;
	this.l_db.optional = true;
	this.remarks.optional = true;
	return verify(this);
    \">\n";

$db = db_open($db_host, $db_user, $db_password, $db_name);

// show all
$res = db_query_accno($db, $id);

show_result($id, $res);

prompt_for_edit($id);

show_copyright();

// this might be show_footer()
echo "\n</body>\n</html>";
?>
