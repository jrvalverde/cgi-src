#!/bin/bash
# $Id: makedoc.sh,v 1.1 2005/05/11 08:01:24 netadmin Exp $ 

#/**
#  * makedoc - PHPDocumentor script to save your settings
#  * 
#  * Put this file inside your PHP project homedir, edit its variables and run whenever you wants to
#  * re/make your project documentation.
#  * 
#  * The version of this file is the version of PHPDocumentor it is compatible.
#  * 
#  * It simples run phpdoc with the parameters you set in this file.
#  * NOTE: Do not add spaces after bash variables.
#  *
#  * @copyright         makedoc.sh is part of PHPDocumentor project {@link http://freshmeat.net/projects/phpdocu/} and its LGPL
#  * @author            Roberto Berto <darkelder (inside) users (dot) sourceforge (dot) net>
#  * @version           Release-1.1.0
#  */


##############################
# should be edited
##############################

#/**
#  * title of generated documentation, default is 'Generated Documentation'
#  * 
#  * @var               string TITLE
#  */
TITLE="RBGMDB Documentation"

#/** 
#  * name to use for the default package. If not specified, uses 'default'
#  *
#  * @var               string PACKAGES
#  */
PACKAGES="RBGMDB"

#/** 
#  * name of a directory(s) to parse directory1,directory2
#  * $PWD is the directory where makedoc.sh is
#  *
#  * @var               string PATH_PROJECT
#  */
#PATH_PROJECT=$PWD
PATH_PROJECT=$PWD/../update

#/** 
#  * name of a file(s) to parse file1,file2
#  * Can contain complete path and * ? wildcard
#  * $PWD is the directory where makedoc.sh 
#  *
#  * @var               string PATH_FILES
#  */
PATH_FILES=$PWD/../complex_query.php,\
$PWD/../complex_query_export.php,\
$PWD/../complex_query_pdf.php,\
$PWD/../sorted_query.php,\
$PWD/../sorted_query_export.php,\
$PWD/../sorted_query_pdf.php,\
$PWD/../submit.php,\
$PWD/../utils.php

#/** 
#  * name of a file(s)/directorie(s) to ignore
#  * $PWD is the directory where makedoc.sh 
#  *
#  * @var               string PATH_IGNORE
#  */
PATH_IGNORE=$PWD/../fpdf153/

#/**
#  * path of PHPDoc executable
#  *
#  * @var               string PATH_PHPDOC
#  */
#PATH_PHPDOC=/opt/tools/phpDocumentor/phpdoc
PATH_PHPDOC=/opt/tools/PhpDocumentor-1.3.0RC4/phpdoc

#/**
#  * where documentation will be put
#  *
#  * @var               string PATH_DOCS
#  */
PATH_DOCS=$PWD/pDoc

#/**
#  * what outputformat to use (html/pdf)
#  *
#  * @var               string OUTPUTFORMAT
#  */
OUTPUTFORMAT=HTML

#/** 
#  * converter to be used
#  *
#  * @var               string CONVERTER
#  */
#CONVERTER=Smarty
CONVERTER=frames

#/**
#  * template to use
#  *
#  * @var               string TEMPLATE
#  */
#TEMPLATE=PHP
TEMPLATE=DOM/earthli

#/**
#  * parse elements marked as private
#  *
#  * @var               bool (on/off)           PRIVATE
#  */
PRIVATE=on

#/**
#  * generate colored source code listing
#  *
#  * @var               bool (on/off)           SOURCE
#  */
SOURCE=on

# make documentation

echo "Making phpDocumentor documentation..."

$PATH_PHPDOC -i $PATH_IGNORE \
    -f $PATH_FILES -d $PATH_PROJECT -t $PATH_DOCS -ti "$TITLE" -dn $PACKAGES \
    -ct note -s $SOURCE \
    -o $OUTPUTFORMAT:$CONVERTER:$TEMPLATE -pp $PRIVATE > logs/phpdoc.log 2>&1

# add phpXref docs as well
 
echo "Making phpXref documentation..."

/opt/tools/phpxref-0.4.1/phpxref.pl -c phpxref.cfg > logs/phpxref.log 2>&1

# vim: set expandtab :
