<h2>Products</h2>
<p><a href="<?php echo site_url('products/add'); ?>">Add Product</a></p>

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
