<h2>Stock Movement History</h2>
<p>
    <button type="button" data-modal-url="<?php echo site_url('stock/in'); ?>">Stock In</button>
    <button type="button" data-modal-url="<?php echo site_url('stock/out'); ?>">Stock Out</button>
    <button type="button" data-modal-url="<?php echo site_url('stock/adjustment'); ?>">Adjustment</button>
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

<?php $this->load->view('modal/container'); ?>
