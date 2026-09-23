<h2>Stock Adjustments</h2>
<p><a href="<?php echo site_url('stock/adjustment'); ?>">New Adjustment</a></p>

<table data-datatable-server data-source="<?php echo site_url('stock/adjustments/datatable'); ?>">
    <thead>
        <tr>
            <th>Product</th>
            <th>System Stock</th>
            <th>Actual Stock</th>
            <th>Difference</th>
            <th>Reason</th>
            <th>Processed By</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
