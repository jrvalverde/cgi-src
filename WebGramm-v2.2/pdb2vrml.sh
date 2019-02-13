#!/bin/sh

pdb2vrml='/opt/structure/bin/pdb2vrml'

echo "Content-type: text/vrml"
echo ""

cat $1 | $pdb2vrml -pdb - -
