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


$onClickLocation="window.location=index.php?module=Configurator&action=asterisk_configurator";

  if ($_REQUEST['mode'] == 'Install') {

?>
<br /><br />

<img src="http://www.blakerobertson.com/ext/yaai-postinstall-logo-26.png" alt="YAAI Logo" style="margin-right:-5px"><br>
<span style="font-size: 1.6em;"><strong>Please review the documentation!  There are several additional steps that must be taken.</strong></span>
<br /><span style="font-size: 1.3em;">The User Guide can be found on the Project website here: <a href="https://github.com/blak3r/yaai/wiki/User-Manual">https://github.com/blak3r/yaai/wiki/User-Manual</a>.  Please note the fairly comprehensive troubleshooting section at the end of the manual.</span>
<br>
<BR>
<span style="font-size: 1.6em;"><strong>Donate to YAAI</strong></span>
<br /><span style="font-size:1.3em;">Please consider making a donation to the project.  Your contribution allows me to spend more time improving/supporting.
We're also in great need of some help from developers.  Please see: <a href="https://github.com/blak3r/yaai/wiki/Project-TODO-List">https://github.com/blak3r/yaai/wiki/Project-TODO-List</a> for ways you can help.</span>
<P>
    <BR/>
  <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
      <input type="hidden" name="cmd" value="_s-xclick">
      <input type="hidden" name="hosted_button_id" value="FA36DGSYQ3NE6">
      <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
      <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
  </form>
</P>
<BR/>

  <input title="Continue" class="button primary" onclick="javascript:window.location='index.php?module=Configurator&action=asterisk_configurator';" type="button" name="button" value="    Continue on to Module Configuration -->    ">
<br/>
<br/>

<?php
  }
}
?>
