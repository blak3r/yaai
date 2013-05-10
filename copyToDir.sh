# This is a dev script which copies from git repo to your sugar instance
# ARG 1 == path to your sugarcrm root
#@/bin/bash

if [ "$1" = "" ]
then
   echo USAGE: ./copyToDir.sh [SugarRoot]
   echo      Example: ./build_zip /var/www/sugarcrm
   exit 1
else
    echo Copying to: $1
    # TODO check if folder exists...
fi

cp -ruv ./SugarModules/modules/Configurator $1/custom/modules/
cp -ruv ./SugarModules/modules/Asterisk $1/custom/modules/
cp -ruv ./SugarModules/modules/Administration $1/custom/modules/
cp -ruv ./SugarModules/service $1/custom/service/