<!-- This file is not used as of yaii 2.0 -->

<div class="asterisk_info" id="<?php print $item['asterisk_id']; ?>">
	<h4><?php print $mod_strings[$item['call_type']]; ?></h4>
	<div class="tabForm">
		<table class="asterisk_data">
			<tr>
				<td colspan="2" class="listViewThS1 asterisk_state"><?php print $item['state']; ?></td>
			</tr>
			<tr >
				<td style="width: 20%;"><?php print $mod_strings['ASTERISKLBL_PHONE']; ?>:</td>
				<td><?php print $item['phone_number']; ?></td>
			</tr>
			<tr>
				<td><?php print $mod_strings['ASTERISKLBL_NAME']; ?>:</td>
				<td>
					<a href="index.php?module=Contacts&action=DetailView&record=<?php print $item['contact_id']; ?>">
					<!-- before v6 <a href="index.php?action=DetailView&module=Contacts&record=<?php print $item['contact_id']; ?>"> -->
						<?php print $item['full_name']; ?>
					</a>
				</td>
			</tr>
			<tr>
				<td><?php print $mod_strings['ASTERISKLBL_COMPANY']; ?>:</td>
				<td>
					<a href="index.php?module=Accounts&action=DetailView&record=<?php print $item['company_id']; ?>">
						<?php print $item['company']; ?>
					</a>
				</td>
			</tr>
		</table>
		<input type="button" value="<?php print $mod_strings['ASTERISKLBL_OPEN_MEMO']; ?>" class="button asterisk_open_memo"/>
		<input type="button" value="Close" class="button asterisk_close"/>
	</div>
</div>