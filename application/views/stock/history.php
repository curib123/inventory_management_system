<h2>Stock Movement History</h2>
<p>
    <a href="<?php echo site_url('stock/in'); ?>">Stock In</a> |
    <a href="<?php echo site_url('stock/out'); ?>">Stock Out</a> |
    <a href="<?php echo site_url('stock/adjustment'); ?>">Adjustment</a> |
    <a href="<?php echo site_url('stock/adjustments'); ?>">Adjustment History</a>
</p>

<table data-datatable-server data-source="<?php echo site_url('stock/history/datatable'); ?>">
    <thead>
        <tr>
            <th>Transaction No.</th>
            <th>Type</th>
            <th>Supplier</th>
            <th>Processed By</th>
            <th>Date</th>
            <th data-orderable="false">Action</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
