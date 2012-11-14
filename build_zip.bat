:: Uses the same version of zip you get in cygwin.
:: I didn't have cygwin on my sugarcrm dev box... but had a bunch of gnu utils thanks to installing git.
:: I copied... zip.exe, cygbz2-1.dll and cygwin1.dll from another computers cygwin/bin folder and it works.
::
:: Copyright (c) 1990-2008 Info-ZIP - Type 'zip "-L"' for software license.
:: Zip 3.0 (July 5th 2008). Usage:
:: zip [-options] [-b path] [-t mmddyyyy] [-n suffixes] [zipfile list] [-xi list]
::  The default action is to add or replace zipfile entries from list, which
::  can include the special name - to compress standard input.
::  If zipfile and list are omitted, zip compresses stdin to stdout.
::  -f   freshen: only changed files  -u   update: only changed or new files
::  -d   delete entries in zipfile    -m   move into zipfile (delete OS files)
:: ...
::  -x   exclude the following names
:: (The rest is omitted)


@SET /p VERSION=What is version number (ex 2.0.1.3): 
@echo TODO: update the manifest version / publish date.

php manifest_updater.php $1
zip -r * yaii-%VERSION%.zip -x .git* *.zip *.bak *.pnps *.pnproj *.eclipse *.svn copyTo*.sh copyFrom*.sh *.bat *.idea manifest_template.php



