<table id="mygrid" data-url="ajax.php?module=presencestate&command=getJSON&jdata=grid" data-cache="false" data-height="299" data-toggle="table" class="table table-striped">
    <thead>
            <tr>
            <th data-field="type"><?php echo _("Type")?></th>
            <th data-field="message"><?php echo _("Message")?></th>
            <th data-field="id" data-formatter="actionFormatter"><?php echo _("Actions")?></th>
        </tr>
    </thead>
</table>
