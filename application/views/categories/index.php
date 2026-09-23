<h2>Categories</h2>
<button type="button" onclick="document.getElementById('category-form-add').showModal();">Add Category</button>
<?php $this->load->view('modal/categories/form', array('modal_id' => 'add', 'form_action' => site_url('categories/add'))); ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Status</th>
            <th>Products</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($categories)): foreach ($categories as $category): ?>
            <tr>
                <td><?php echo $category->id; ?></td>
                <td><?php echo $category->category_name; ?></td>
                <td><?php echo ($category->status == 1) ? 'Active' : 'Inactive'; ?></td>
                <td><?php echo $this->Category_model->count_products($category->id); ?></td>
                <td>
                    <button type="button" onclick="document.getElementById('category-form-<?php echo (int) $category->id; ?>').showModal();">Edit</button>
                    <button type="button" onclick="document.getElementById('category-details-<?php echo (int) $category->id; ?>').showModal();">Details</button>
                    <button type="button" onclick="document.getElementById('category-delete-<?php echo (int) $category->id; ?>').showModal();">Delete</button>
                </td>
            </tr>
            <?php $this->load->view('modal/categories/form', array('modal_id' => $category->id, 'category' => $category, 'form_action' => site_url('categories/edit/' . $category->id))); ?>
            <?php $this->load->view('modal/categories/details', array('category' => $category)); ?>
            <?php $this->load->view('modal/categories/delete', array('category' => $category)); ?>
        <?php endforeach; else: ?>
            <tr>
                <td colspan="5">No categories found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($pagination)): ?>
    <?php echo $pagination; ?>
<?php endif; ?>
