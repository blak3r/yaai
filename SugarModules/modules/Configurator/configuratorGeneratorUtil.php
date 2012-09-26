<?php
/**
 * Utility class I use to generate the code needed for configuration pages.
 * User: blake
 * Date: 9/3/12
 * Time: 8:56 PM
 *
 * Instructions:
 * 1) paste the $config_meta array from asterisk_configurator.php here
 * 2) Run script. (php configuratorGeneratorUtil.php)
 * 3) Paste the outputs into it's appropriate locations
 */

$config_meta['asterisk_host'] 	= array('default' => '127.0.0.1', 'section'=>'Asterisk Server Settings');
$config_meta['asterisk_port'] 	= array('default' => '5038', 'type'=>'int', 'section'=>'Asterisk Server Settings');
$config_meta['asterisk_user'] = array('default' => 'ami_user', 'section'=>'Asterisk Server Settings');
$config_meta['asterisk_secret'] = array('default' => 'ami_pass', 'section'=>'Asterisk Server Settings');

$config_meta['asterisk_soapuser'] = array('default' => 'admin','section'=>'SugarCRM SOAP Settings' );
$config_meta['asterisk_soappass'] = array('default' => 'soap_password','section'=>'SugarCRM SOAP Settings');

$config_meta['asterisk_prefix'] = array('default' => '', 'section'=>'Call Configuration', 'required'=>'FALSE');
$config_meta['asterisk_dialinPrefix'] = array('default' => '','section'=>'Call Configuration', 'required'=>'FALSE');
$config_meta['asterisk_context'] = array('default' => 'from-internal','section'=>'Call Configuration');
$config_meta['asterisk_expr'] = array('default' => '^(sip\/[1-9][0-9][0-9]?[0-9]?-|Local)','section'=>'Call Configuration');
$config_meta['asterisk_dialout_channel'] = array('default' => 'SIP/###','section'=>'Call Configuration');
$config_meta['asterisk_dialin_ext_match'] = array('default' => 'Local\/(?:.*?)(\d\d\d?\d?\d?)@','section'=>'Call Configuration');
$config_meta['asterisk_rg_detect_expr'] = array('default'=>"^Local\/RG",'section'=>'Call Configuration');
$config_meta['asterisk_rg_cell_ring_expr'] = array('default'=>"^Local\/\d{7,10}",'section'=>'Call Configuration');

$config_meta['asterisk_call_subject_inbound_abbr'] = array('default' => "IBC: ", 'section'=>'Misc');
$config_meta['asterisk_call_subject_outbound_abbr'] = array('default' => "OBC: ",'section'=>'Misc');
$config_meta['asterisk_call_subject_max_length'] = array('default' => '50','section'=>'Misc');
$config_meta['asterisk_listener_poll_rate'] = array('default' => '5000','section'=>'Misc');
$config_meta['asterisk_opencnam_enabled'] = array('default' => 'false','section'=>'Misc');
$config_meta['asterisk_opencnam_username'] = array('default'=> '', 'section' => 'Misc');
$config_meta['asterisk_opencnam_apikey'] = array('default' => '', 'section'=>'Misc');
$config_meta['asterisk_opencnam_retries'] = array('default'=> '4', 'section'=>'Misc');
$config_meta['asterisk_gravatar_enabled'] = array('default' => 'false','section'=>'Misc') ;
$config_meta['asterisk_short_call_status'] = array('default' => "Missed",'section'=>'Misc');
$config_meta['asterisk_hide_call_popups_after_mins'] = array('default' => '60','section'=>'Misc');

$config_meta['asterisk_log_file'] = array('default' => '', 'section'=>'Logging');

$config_meta['asterisk_recordings_enabled'] = array('default'=> 'false', 'section'=>'Recordings');
$config_meta['asterisk_recordings_path'] = array('default' => '/var/spool/asterisk/monitor', 'section'=>'Recordings');



$columnSmarty=<<<'END'
    <td nowrap width="10%" class="dataLabel">{$MOD.LBL_@@UPPER@@}
      {if !empty($MOD.LBL_@@UPPER@@_DESC)}
          [<a href="#" title="{$MOD.LBL_@@UPPER@@_DESC}">?</a>]:
      {/if}
    </td>
    <td width="25%" class="dataField">
    {if empty($config.@@NORMAL@@ )}
        {assign var='@@NORMAL@@' value=$asterisk_config.@@NORMAL@@}
    {else}
        {assign var='@@NORMAL@@' value=$config.@@NORMAL@@}
    {/if}
        <input type='@@TYPE@@' name='@@NORMAL@@' size="45" value='{$@@NORMAL@@}'>
    </td>
END;

$i = 0;
$prevSection='';
    foreach ($config_meta as $key => $value) {
    $in = $columnSmarty ;

    // Once section changes we insert a new header
    if( $value['section'] != $prevSection ) {
        $prevSection = $value['section'];
        if( $i%2 == 1 ) {
            print "<TD>&nbsp;</TD><TD>&nbsp;</TD> </tr>";
            $i++;
        }
        print "\n\n<TR><td colspan=\"4\">&nbsp;&nbsp;<TR><TD colspan=\"4\"><h3>{$value['section']}</h3></TD></tr>\n\n";
    }

    if( ($i % 2) == 0)
        print "\n\n<TR>\n\n";

    $type = "textbox";
    if( isset($value['type'] ) ) {
        if( $value['type'] == "bool") {
            $type = "checkbox"; // checkbox is too hard to support since it's value isn't submitted when form is posted and it's unchecked!
        }
        else {
            $type = $value['type'];
        }
    }

    $out  = str_replace(
        array('@@UPPER@@','@@NORMAL@@','@@TYPE@@'),
        array(strtoupper($key),$key,$type),
        $in);

    print $out . "\n\n";

    $i++;
}

if( $i%2 == 1 ) {
    print "<TD>&nbsp;</TD><TD>&nbsp;</TD> </tr>";
}

print "\n\n\n -- FOR SugarModules/Configurator/language/en_us.lang.php ------\n\n";

// TODO: here we should merge the values that are already defined and only output new ones....
foreach ($config_meta as $key => $value) {
    print "$" . "mod_strings['" . "LBL_" . strtoupper($key) . "'] = 'TODO DEFINE';" . "\n";
}









