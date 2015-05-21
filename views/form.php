<?php
extract($request);
if(!empty($id)){
	$ps = FreePBX::Presencestate();
	$thisPS = $ps->presencestateItemGet($id);
}
$typeoptions = "";
foreach(presencestate_types_get() as $v => $k){
	$selected = (!empty($thisPS->type) && ($v == $thisPS->type))?'SELECTED':'';
	$typeoptions .= '<option value="'.$v.'" '.$selected.'>'.$k.'</option>';
}

?>
<form name='presence' id='presence' class="fpbx-submit" method="POST" action="?display=presencestate" <?php if(!empty($id)) {?>data-fpbx-delete='?display=presencestate&amp;action=delete&amp;id=<?php echo $id?>'<?php } ?>>
	<input type="hidden" name="action" value="save">
	<?php if(!empty($id)) {?><input type="hidden" name="id" value="<?php echo $id?>"><?php }?>
	<!--Type-->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="type"><?php echo _("Type") ?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="type"></i>
						</div>
						<div class="col-md-9">
							<select class="form-control" id="type" name="type">
								<?php echo $typeoptions?>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="type-help" class="help-block fpbx-help-block"><?php echo _("State type")?></span>
			</div>
		</div>
	</div>
	<!--END Type-->
	<!--Optional Message-->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="message"><?php echo _("Optional Message") ?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="message"></i>
						</div>
						<div class="col-md-9">
							<input type="text" class="form-control" id="message" name="message" value="<?php echo !empty($thisPS->message) ? $thisPS->message : ""?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="message-help" class="help-block fpbx-help-block"><?php echo _("Optional message, example: Lunch")?></span>
			</div>
		</div>
	</div>
	<!--END Optional Message-->
</form>
