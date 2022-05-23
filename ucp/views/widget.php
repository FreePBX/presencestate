<select name="status" data-toggle="select" data-container="body" data-width="fit" title="<i class='fa fa-circle active'></i> Offline" class="show-tick">
<?php foreach($states as $state) { ?>
	<?php
		$icon = "<i class='fa fa-circle active' style='color:" . $state['color'] . "'></i>";
		$subtext = !empty($state['message']) ? ' ('.$state['message'].')' : '';

		$selected = $state['type'] == $currentState['State'] && $state['message'] == $currentState['Message'];
	?>
	<option title="<?php echo $icon . ' ' . $state['nice']?>" <?php echo $selected ? 'selected' : ''?> class="presence-item" data-id="<?php echo $state['id']?>" data-content="<?php echo $icon . ' ' . $state['nice'] . $subtext?>"><?php echo $state['type'] . $subtext?></option>
<?php } ?>
</select>
