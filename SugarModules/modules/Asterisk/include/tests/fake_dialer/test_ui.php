<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>YAAI Fake Dialer</title>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css" />
    <script src="http://code.jquery.com/jquery-1.8.2.js"></script>
    <script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>
    <style>
    .draggable { width: 100px; height: 100px; padding: 0.5em; float: left; margin: 10px 10px 10px 0; }
    .droppable { width: 150px; height: 150px; padding: 0.5em; float: left; margin: 10px; }
    </style>

</head>
<body>

<button id="add_call">Add Call</button>

<div id="main">
</div>

<div id="clone_elements">
    <div id="call_setup" style="float:left;">
        <div id="call" class="draggable ui-widget-content">
            <img src="/custom/modules/Asterisk/include/tests/fake_dialer/call_green.jpg" alt="Call" />
        </div>
 
        <div id="ringing" class="droppable ui-widget-header">
            <p>Ringing</p>
        </div>

        <div id="connected" class="droppable ui-widget-header">
            <p>Connected</p>
        </div>

        <div id="hangup" class="droppable ui-widget-header">
            <p>Hangup</p>
        </div>

        <div id="closed" class="droppable ui-widget-header">
            <p>Closed</p>
        </div>
    </div>
</div>

<div id="extension-input" title="Extension to call or call from">
    <form>
    <fieldset>
        <label for="extension">Extension</label>
        <input type="text" name="extension" id="extension" class="text ui-widget-content ui-corner-all" />
        <label for="phone_number">Phone#</label>
        <input type="text" name="phone_number" id="phone_number" class="text ui-widget-content ui-corner-all" />
    
    <div id="radio" style="text-align:center;">
        <p>Matching Contacts</p>
        <input type="radio" id="radio1" name="radio" value="0" /><label for="radio1">0</label>
        <input type="radio" id="radio2" name="radio" value="1" checked="checked" /><label for="radio2">1</label>
        <input type="radio" id="radio3" name="radio" value="2" /><label for="radio3">>=2</label>
    </div>
    </fieldset>
    </form>
</div>

<script type="text/javascript" src="custom/modules/Asterisk/include/tests/fake_dialer/test_ui.js"></script>
</body>
</html>
