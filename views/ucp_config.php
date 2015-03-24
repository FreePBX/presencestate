<!--Enable Presence-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="presencestate"><?php echo _("Enable Presence") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="presencestate"></i>
					</div>
					<div class="col-md-9 radioset">
						<input type="radio" name="presencestate|enable" id="presencestate|enable_yes" value="yes" <?php echo ($enabled) ? 'checked' : ''?>>
						<label for="presencestate|enable_yes"><?php echo _("Yes")?></label>
						<input type="radio" name="presencestate|enable" id="presencestate|enable_no" value="no" <?php echo !($enabled) ? 'checked' : ''?>>
						<label for="presencestate|enable_no"><?php echo _("No")?></label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="presencestate-help" class="help-block fpbx-help-block"><?php echo _("Allow user to set presence state")?></span>
		</div>
	</div>
</div>
<!--END Enable Presence-->