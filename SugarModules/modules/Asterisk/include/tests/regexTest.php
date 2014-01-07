<?php
//$asteriskMatchInternal = "^(sip\/[1-9][0-9][0-9]?[0-9]?-|Local)";
$asteriskMatchInternal = "^SIP/[1-9][0-9][0-9]?";
$asteriskMatchInternal = '/' . $asteriskMatchInternal . '/i';



$eChannel = "SIP/202-0000001b";
//$eDestination = "SIP/cc800-0000010c";
$eDestination = "SIP/cc800-0000010c";

$statusDest = preg_match($asteriskMatchInternal, $eDestination);
$statusChan = preg_match($asteriskMatchInternal, $eChannel);


if (preg_match($asteriskMatchInternal, $eChannel) && !preg_match($asteriskMatchInternal, $eDestination)) {
    echo "It's outbound";

}
else {
    echo "It's inbound";
}





