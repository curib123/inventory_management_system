<h2>Products</h2>
<button type="button" onclick="document.getElementById('product-form-add').showModal();">Add Product</button>

<?php $this->load->view('modal/products/form', array(
    'modal_id' => 'add',
    'suppliers' => $suppliers,
    'form_action' => site_url('products/add')
)); ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Name</th>
            <th>Supplier</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($products)): foreach ($products as $product): ?>
            <tr>
                <td><?php echo $product->id; ?></td>
                <td><?php echo $product->product_code; ?></td>
                <td><?php echo $product->product_name; ?></td>
                <td><?php echo isset($product->supplier_name) ? $product->supplier_name : 'N/A'; ?></td>
                <td><?php echo $product->stock; ?></td>
                <td><?php echo $product->selling_price; ?></td>
                <td>
                    <button type="button" onclick="document.getElementById('product-form-<?php echo (int) $product->id; ?>').showModal();">Edit</button>
                    <button type="button" onclick="document.getElementById('product-details-<?php echo (int) $product->id; ?>').showModal();">Details</button>
                    <button type="button" onclick="document.getElementById('product-delete-<?php echo (int) $product->id; ?>').showModal();">Delete</button>
                </td>
            </tr>
            <?php $this->load->view('modal/products/form', array('modal_id' => $product->id, 'product' => $product, 'suppliers' => $suppliers, 'form_action' => site_url('products/edit/' . $product->id))); ?>
            <?php $this->load->view('modal/products/details', array('product' => $product)); ?>
            <?php $this->load->view('modal/products/delete', array('product' => $product)); ?>
        <?php endforeach; else: ?>
            <tr>
                <td colspan="7">No products found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($pagination)): ?>
    <?php echo $pagination; ?>
<?php endif; ?>
