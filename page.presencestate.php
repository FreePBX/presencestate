<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
$heading = _("Presence States");

$request = $_REQUEST;
$request['view'] = !empty($request['view']) ? $request['view'] : "";
switch ($request['view']) {
	case 'form':
		$content = load_view(__DIR__.'/views/form.php', array('request' => $request));
	break;
	default:
		$content = load_view(__DIR__.'/views/grid.php', array('request' => $request));
	break;
}

?>
<div class="container-fluid">
	<h1><?php $heading?></h1>
	<div class = "display full-border">
		<div class="row">
			<div class="col-sm-12">
				<div class="fpbx-container">
					<div class="display <?php echo (empty($request['view'])) ? "no" : "full"?>-border">
						<?php echo $content ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
