# This is a dev script which copies from git repo to your sugar instance
# ARG 1 == path to your sugarcrm root
# ARG 2 == update (pass this if your cp command supports -u)
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

if [ "%2" == "update" ]
then
    cp -ruv ./SugarModules/modules/Configurator $1/custom/modules/
    cp -ruv ./SugarModules/modules/Asterisk $1/custom/modules/
    cp -ruv ./SugarModules/modules/Administration $1/custom/modules/
    cp -ruv ./SugarModules/service/callinize $1/custom/service/
else
    #cp on mac doesn't have the u option (update only if newer)
    cp -rv ./SugarModules/modules/Configurator $1/custom/modules/
    cp -rv ./SugarModules/modules/Asterisk $1/custom/modules/
    cp -rv ./SugarModules/modules/Administration $1/custom/modules/
    cp -rv ./SugarModules/service/callinize $1/custom/service/
fi