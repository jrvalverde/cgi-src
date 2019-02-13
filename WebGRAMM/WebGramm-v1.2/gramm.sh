#!/bin/sh
pdb2vrml='/opt/structure/bin/pdb2vrml'
molauto='/opt/structure/bin/molauto'
molscript='/opt/structure/bin/molscript'
GRAMMDAT=/opt/structure/gramm
export GRAMMDAT

#( /opt/structure/bin/gramm scan coord ; touch done ; rm webgramm.log ; rm gramm.sh ; rm -f Rodin_Penseur.jpg ) >> webgramm.log 2>&1 &

doit() {
    touch started;
    /opt/structure/bin/gramm scan coord ;
    for i in *.pdb ; do
    	# VRML v1
    	cat $i | $pdb2vrml -pdb - - > `basename $i .pdb`.vrml
	# VRML v2
	$molauto -cylinder -turns -nice -cpk $i | \
	    $molscript -vrml > `basename $i .pdb`.wrl 2> /dev/null
	# PS
	$molauto -cylinder -turns -nice -cpk $i | \
	    $molscript -ps > `basename $i .pdb`.ps 2> /dev/null
    done
    touch done ;
    rm -f started;
    rm -f webgramm.log ;
    rm -f gramm.sh ;
    rm -f Rodin_Penseur.jpg ;
}

doit > /dev/null 2>&1 &

exit

