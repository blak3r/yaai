#@/bin/bash
#@SET /p VERSION=What is version number (ex 2.0.1.3): 

if [ "$1" = "" ]
then
   echo USAGE: ./build_zip.sh [Version]
   echo      Example: ./build_zip 2.0.0.1 
   exit 1
else
	VERSION=$1
fi


php manifest_updater.php $1
zip -r yaai-$VERSION.zip * -x .git* *.zip *.bak *.pnps *.pnproj *.eclipse *.svn copyTo*.sh copyFrom*.sh *.bat *.idea manifest_template.php

echo ""
echo ""


