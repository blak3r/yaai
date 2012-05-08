{*
 * Author: Blake Robertson
 * Date: 5/7/2012
 *
 * Adds the .phone class to any contact list view (including subpanels).  Modifying metadata doesn't work on subpanels in v6.4
 * See this forum post for complete history: http://forums.sugarcrm.com/f6/adding-customcode-metadata-account_subpanel_contacts-does-nothing-79951/
 * 
 * Manual Install Instructions:
 *   Copy to /custom/include/SugarFields/Fields/Phone
 *   This is based off v6.4.2 /include/SugarFields/Fields/Phone if this file is updated in the future versions of sugar you should
 *   copy it to the custom folder and add the <span> </span> part around the last line.
 * 
*}
{capture name=getPhone assign=phone}{sugar_fetch object=$parentFieldArray key=$col}{/capture}

<span class="phone">{sugar_phone value=$phone usa_format=$usa_format}</span>