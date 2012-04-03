<?php

require_once('include/utils.php');
require_once('include/utils/file_utils.php');
require_once('include/utils/array_utils.php');
require_once('include/utils/sugar_file_utils.php');


function post_install() {


  // Include existing custom entry point registry array
  @include_once('custom/include/MVC/Controller/entry_point_registry.php');

  // Start building custom entry point PHP
  $the_string =   "<?php\n" . '// created: ' . date('Y-m-d H:i:s') . "\n\n";
  
  // Define new entry point
	$entry_point_registry['AsteriskController'] = array (
	  'file' => 'custom/modules/Asterisk/include/controller.php',
	  'auth' => true,
	);

	$entry_point_registry['AsteriskCallListener'] = array (
	  'file' => 'custom/modules/Asterisk/include/callListener.php',
	  'auth' => true,
	);

	$entry_point_registry['AsteriskCallCreate'] = array (
	  'file' => 'custom/modules/Asterisk/include/callCreate.php',
	  'auth' => true,
	);
	
  // For each custom entry point, add override value
  foreach ($entry_point_registry as $key=>$value) {
    $the_string .= override_value_to_string('entry_point_registry', $key, $value)."\n";
  }

  // Write the dir if needed
  if (!is_dir('custom/include/MVC/Controller')) {
    $result = sugar_mkdir('custom/include/MVC/Controller', NULL, true);
  }
  // Write the new custom entry point registry file
  $result = @sugar_file_put_contents('custom/include/MVC/Controller/entry_point_registry.php', $the_string);



  if ($_REQUEST['mode'] == 'Install') {

?>
<br /><br />
<span style="font-size: 2em;"><strong>Please review the documentation!  There are several additional steps that must be taken.</strong></span> 
<br /><span style="font-size: 1.3em;">The User Guide can be found on the Project website here: <a href="https://github.com/blak3r/yaai/wiki/User-Manual">https://github.com/blak3r/yaai/wiki/User-Manual</a></span>

<?php
  }
}
?>
