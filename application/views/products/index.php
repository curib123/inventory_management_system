<h2>Products</h2>
<p><button type="button" data-modal-url="<?php echo site_url('products/add'); ?>">Add Product</button></p>

<table data-datatable-server data-source="<?php echo site_url('products/datatable'); ?>">
    <thead>
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Name</th>
            <th>Category</th>
            <th>Supplier</th>
            <th>Stock</th>
            <th>Selling Price</th>
            <th>Status</th>
            <th data-orderable="false">Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<?php $this->load->view('modal/container'); ?>
