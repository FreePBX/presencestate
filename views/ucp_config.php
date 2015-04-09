<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="presencestate_enable"><?php echo _("Enable Presence")?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="presencestate_enable"></i>
					</div>
					<div class="col-md-9">
						<span class="radioset">
							<input type="radio" name="presencestate_enable" id="presencestate_enable_yes" value="yes" <?php echo ($penabled) ? 'checked' : ''?>>
							<label for="presencestate_enable_yes">Yes</label>
							<input type="radio" name="presencestate_enable" id="presencestate_enable_no" value="no" <?php echo !($penabled) ? 'checked' : ''?>>
							<label for="presencestate_enable_no">No</label>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="presencestate_enable-help" class="help-block fpbx-help-block"><?php echo _("Whether to allow this user to be able to change their presence state/status in UCP")?></span>
		</div>
	</div>
</div>
