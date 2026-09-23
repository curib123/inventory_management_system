<h2>Categories</h2>
<p><a href="<?php echo site_url('categories/add'); ?>">Add Category</a></p>

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
