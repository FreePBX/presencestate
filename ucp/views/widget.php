<select name="status" data-toggle="select" data-container="body" data-width="fit" class="show-tick">
	<?php
		if($currentState['State'] == 'not_set') {
			$icon = "<i class='fa fa-circle active'></i>";
			$subtext = "Offline";
			$id = "not_set";
	?>
			<option <?php echo 'selected';?> class="presence-item" data-id="<?php echo $id; ?>" data-content="<?php echo $icon . ' ' . $subtext?>"><?php echo $subtext?></option>
	<?php
		}
	?>
<?php foreach($states as $state) { ?>
	<?php
		$icon = "<i class='fa fa-circle active' style='color:" . $state['color'] . "'></i>";
		$subtext = !empty($state['message']) ? ' ('.$state['message'].')' : '';

		$selected = $state['type'] == $currentState['State'] && $state['message'] == $currentState['Message'];
	?>
	<option <?php echo $selected ? 'selected' : ''?> class="presence-item" data-id="<?php echo $state['id']?>" data-content="<?php echo $icon . ' ' . $state['nice'] . $subtext?>"><?php echo $state['type'] . $subtext?></option>
<?php } ?>
</select>
