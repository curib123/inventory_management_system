<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Category Details',
    'modal_subtitle' => 'Category identity, status, and product usage.',
    'modal_icon' => 'bi-tags'
));
?>

<div class="app-modal-body">
    <dl class="row app-detail-list mb-0">
        <dt class="col-sm-4">Name</dt>
        <dd class="col-sm-8">
            <?php echo html_escape($category->category_name); ?>
            <span class="badge mx-3 <?php echo $category->status ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                <?php echo $category->status ? 'Active' : 'Inactive'; ?>
            </span>
        </dd>

        <dt class="col-sm-4">Products</dt>
        <dd class="col-sm-8">
            <?php if (!empty($products)): ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($products as $product): ?>
                        <?php
                        $is_low_stock = (int) $product->stock <= (int) $product->reorder_level;
                        $product_url = site_url('products/view/' . (int) $product->id);
                        ?>
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom pb-2">
                            <div class="min-w-0">
                                <?php if (!empty($can_view_products)): ?>
                                    <a
                                        href="<?php echo $product_url; ?>"
                                        class="text-decoration-none fw-semibold"
                                        data-modal-url="<?php echo $product_url; ?>"
                                    >
                                        <?php echo html_escape($product->product_name); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="fw-semibold"><?php echo html_escape($product->product_name); ?></span>
                                <?php endif; ?>
                                <div class="small text-body-secondary">
                                    <?php echo html_escape($product->product_code); ?>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <span class="badge <?php echo $product->status ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                    <?php echo $product->status ? 'Active' : 'Inactive'; ?>
                                </span>
                                <span class="badge <?php echo $is_low_stock ? 'text-bg-warning' : 'text-bg-light'; ?>">
                                    Stock: <?php echo (int) $product->stock; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <span class="text-body-secondary">No products in this category.</span>
            <?php endif; ?>
        </dd>

        <dt class="col-sm-4">Total Products</dt>
        <dd class="col-sm-8"><?php echo (int) $product_count; ?></dd>
    </dl>
</div>

<?php $this->load->view('components/modal/footer', array('close_label' => 'Close')); ?>
