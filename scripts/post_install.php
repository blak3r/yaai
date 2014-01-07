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

    $entry_point_registry['AsteriskCallDownload'] = array (
        'file' => 'custom/modules/Asterisk/include/callDownload.php',
        'auth' => true,
    );

    $entry_point_registry['AsteriskFakeCall'] = array (
        'file' => 'custom/modules/Asterisk/include/tests/fake_dialer/test_ui.php',
        'auth' => true,
    );

    $entry_point_registry['AsteriskFakeDialerActions'] = array (
        'file' => 'custom/modules/Asterisk/include/tests/fake_dialer/test_actions.php',
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

<img src="https://gitimg.com/blak3r/yaai-stats/install-3.6.10-community/track">
<img src="custom/modules/Asterisk/include/images/logo.png" alt="Callinize Logo" style="margin-right:-5px"><br>
<span style="font-size: 1.6em;"><strong>Please review the documentation!  There are several additional steps that must be taken.</strong></span>
<br /><span style="font-size: 1.3em;">The User Guide can be found on the Project website here: <a href="https://github.com/blak3r/yaai/wiki/User-Manual">https://github.com/blak3r/yaai/wiki/User-Manual</a>.  Please note the fairly comprehensive troubleshooting section at the end of the manual.</span>
<br>
<BR>
<span style="font-size: 1.4em;"><strong>
NOTE: If you just did an upgrade install, <u>RUN QUICK REPAIR AND REBUILD</u>  References to custom user fields added need to be refreshed.
</strong>
    </span>
<BR>
<BR>
      <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
      <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>

      <!-- @@@@ BEGIN CALLINIZE COMMUNITY ONLY @@@@ -->
      <table style="margin-bottom:-30px;">
          <tr>
              <TD width="2%"> &nbsp;</td>
              <td width="40%" valign="top">
                  <div class="know-whos-calling">
                      <div class="item">
                          <div class="left">
                              <h2>Upgrade Today</h2>
                              <p>Upgrade from the community edition and get awesome features like:<P>
                              <UL>
                                  <LI> Mobile App - Log/Create/Track from Anywhere! </LI>
                                  <LI> Support for Leads and custom phone fields. </LI>
                                  <LI> Call Recording / Playback in Sugar</LI>
                                  <LI> Operator Panel - Web switchboard for Asterisk </LI>
                                  <LI> Customer Support </LI>
                              </UL>
                              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.callinize.com/features.html" target="_blank"><button class="btn btn-primary">Learn More</button></a>
                          </div>
                      </div>
                  </div>

              <TD width="75px"> &nbsp;</td>
              <TD width="50%">

                  <div id="myCarousel" class="carousel slide animate" style="width:375px; ">
                      <ol class="carousel-indicators dark">
                          <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                          <li data-target="#myCarousel" data-slide-to="1" class=""></li>
                          <li data-target="#myCarousel" data-slide-to="2" class=""></li>
                      </ol>
                      <div class="carousel-inner">
                          <div class="item active">
                              <img src="custom/modules/Asterisk/include/images/banner_mobile_cropped.jpg" alt="" style="width: 375px; height: 275px; max-height:400px;">
                              <div class="carousel-caption">
                                  <p><strong>Mobile App:</strong> Finally! Your CRM Telephony integration will not require users to be tied down to their desk.
                              </div>
                          </div>
                          <div class="item">
                              <img src="custom/modules/Asterisk/include/images/banner_fop2.jpg" alt="" style="width: 375px; height: 275px; max-height:400px;">
                              <div class="carousel-caption">
                                  <P><strong>Operator Panel</strong> See presence of your coworkers, attended transfer, manage call queues, park calls, etc. -- all from right inside sugar.</P>
                              </div>
                          </div>
                          <div class="item">
                              <img src="custom/modules/Asterisk/include/images/banner_callrecording.jpg" alt="" style="width: 375px; height: 275px; max-height:400px;">
                              <div class="carousel-caption">
                                  <p><strong>Call Recordings</strong> Listen to phone call recordings inside SugarCRM automatically related to the Call.
                              </div>
                          </div>
                      </div>

                  </div>
              </td>
          </tr>
      </table>
      <!-- @@@@ END CALLINIZE COMMUNITY ONLY @@@@ -->


  <input title="Continue" class="button primary" onclick="javascript:window.location='index.php?module=Configurator&action=asterisk_configurator';" type="button" name="button" value="    Continue on to Module Configuration -->    ">
<br/>
<br/>

<?php

  }
}
?>
