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

zip -r yaii-$VERSION.zip * -x .git* *.zip *.bak *.pnps *.pnproj *.eclipse *.svn

echo ""
echo ""
echo "NOTE: This script doesn't update the manifest version number or publish date yet..."

