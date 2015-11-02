<div id="toolbar-all">
  <a href="?display=presencestate&view=form" class="btn btn-default"><i class="fa fa-plus"></i>&nbsp;<?php echo _("Add State")?></a>
</div>
<table id="presencegrid"
  data-url="ajax.php?module=presencestate&amp;command=getJSON&amp;jdata=grid"
  data-cache="false"
  data-toggle="table"
  data-pagination="true"
  data-search="true"
  data-toolbar="#toolbar-all"
  class="table table-striped">
    <thead>
            <tr>
            <th data-field="type"><?php echo _("Type")?></th>
            <th data-field="message"><?php echo _("Message")?></th>
            <th data-field="id" data-formatter="actionFormatter" class="col-xs-2"><?php echo _("Actions")?></th>
        </tr>
    </thead>
</table>
