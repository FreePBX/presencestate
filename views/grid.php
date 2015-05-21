<table id="mygrid" data-url="ajax.php?module=presencestate&amp;command=getJSON&amp;jdata=grid" data-cache="false" data-toggle="table"  data-maintain-selected="true" data-show-columns="true" data-show-toggle="true" data-pagination="true" data-search="true"  class="table table-striped">
    <thead>
            <tr>
            <th data-field="type"><?php echo _("Type")?></th>
            <th data-field="message"><?php echo _("Message")?></th>
            <th data-field="id" data-formatter="actionFormatter"><?php echo _("Actions")?></th>
        </tr>
    </thead>
</table>
