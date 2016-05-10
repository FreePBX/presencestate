<div class="col-md-10">
	<h3><?php echo _('Presence State Settings')?></h3>
	<div class="pssettings">
		<div id="message" class="alert" style="display:none;"></div>
		<form role="form">
			<div class="form-group">
				<label for="startsessionstatus"><?php echo _('On UCP Login Set Status to') ?>:</label><br/>
				<select name="startsessionstatus" id="startsessionstatus" class="form-control">
					<option value="0"><?php echo _('Do Not Change')?></option>
					<?php foreach($states as $state) { ?>
						<option value="<?php echo $state['id']?>" <?php echo ($startsessionstatus == $state['id']) ? 'selected' : '' ?>><?php echo $state['nice']?><?php echo $state['niceMessage']?></option>
					<?php } ?>
				</select>
			</div>
			<div class="form-group">
				<label for="endsessionstatus"><?php echo _('On Browser Close or UCP Logout Set Status to') ?>:</label><br/>
				<select name="endsessionstatus" id="endsessionstatus" class="form-control">
					<option value="0"><?php echo _('Do Not Change')?></option>
					<?php foreach($states as $state) { ?>
						<option value="<?php echo $state['id']?>" <?php echo ($endsessionstatus == $state['id']) ? 'selected' : '' ?>><?php echo $state['nice']?><?php echo $state['niceMessage']?></option>
					<?php } ?>
				</select>
			</div>
			<div class="form-group">
				<label><?php echo _('Define Actions on A Status Change') ?>:</label><br/>
				<div class="state-group">
					<?php foreach($states as $state) { ?>
						<label for="<?php echo $state['type']?>-<?php echo $state['id']?>"><?php echo $state['nice'] ?>:</label><br/>
						<?php if(!empty($state['message'])) {?><div class="message">(<?php echo $state['message']; ?>)</div><?php } ?>
						<select class="event form-control" name="<?php echo $state['id']?>" id="<?php echo $state['type']?>-<?php echo $state['id']?>">
							<?php foreach($actions as $action => $display) { ?>
								<option value="<?php echo $action?>" <?php echo ($action == $state['pref']) ? 'selected' : '' ?>><?php echo $display?></option>
							<?php } ?>
						</select><br/>
					<?php } ?>
				</div>
			</div>
		</form>
	</div>
</div>
