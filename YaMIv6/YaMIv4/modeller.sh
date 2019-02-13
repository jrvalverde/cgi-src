#!/bin/sh

( /opt/structure/modeller4/run.csh $* ; touch done ; rm modeller.sh ) >> modeller.log 2>&1 &

exit


