#$Id: gramm.sh,v 1.3 2003/04/21 14:35:58 netadmin Exp netadmin $
#$Log: gramm.sh,v $
#Revision 1.3  2003/04/21 14:35:58  netadmin
#Modified the gramm call.
#
#Revision 1.2  2003/04/21 14:19:33  netadmin
#Deleted the release 1.0 comments and the 'coord' parameter for gramm.

#!/bin/sh
pdb2vrml='/opt/structure/bin/pdb2vrml'
molauto='/opt/structure/bin/molauto'
molscript='/opt/structure/bin/molscript'
GRAMMDAT=/opt/structure/gramm
export GRAMMDAT

doit() {
    touch started;
    /opt/structure/bin/gramm $gramm_param ;
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
   #rm -f gramm.sh ;
    rm -f Rodin_Penseur.jpg ;
}

doit > /dev/null 2>&1 &

exit

