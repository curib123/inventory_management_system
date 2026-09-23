<h2>Categories</h2>
<p><button type="button" data-modal-url="<?php echo site_url('categories/add'); ?>">Add Category</button></p>

<table data-datatable-server data-source="<?php echo site_url('categories/datatable'); ?>">
    <thead>
        <tr>
            <th>ID</th>
            <th>Category</th>
            <th>Status</th>
            <th>Products</th>
            <th data-orderable="false">Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<?php $this->load->view('modal/container'); ?>
