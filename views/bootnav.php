<div id="toolbar-rnav">
  <a href="?display=presencestate&view=form" class="btn btn-default"><i class="fa fa-plus"></i>&nbsp;<?php echo _("Add State")?></a>
  <a href="?display=presencestate" class="btn btn-default"><i class="fa fa-list"></i>&nbsp;<?php echo _("List States")?></a>
</div>
<table id="presencernav"
  data-url="ajax.php?module=presencestate&amp;command=getJSON&amp;jdata=grid"
  data-cache="false"
  data-toggle="table"
  data-search="true"
  data-toolbar="#toolbar-rnav"
  class="table">
    <thead>
            <tr>
            <th data-field="type"><?php echo _("Type")?></th>
            <th data-field="message"><?php echo _("Message")?></th>
        </tr>
    </thead>
</table>
<script type="text/javascript">
$("#presencernav").on('click-row.bs.table',function(e,row,elem){
  window.location = '?display=presencestate&view=form&id='+row['id'];
});
</script>
