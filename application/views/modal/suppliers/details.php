<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Supplier Details',
    'modal_subtitle' => 'Supplier contact information and availability status.',
    'modal_icon' => 'bi-truck'
));
?>

<div class="app-modal-body">
    <dl class="row app-detail-list mb-0">
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?php echo html_escape($supplier->supplier_name); ?></dd>
        <dt class="col-sm-4">Contact Person</dt><dd class="col-sm-8"><?php echo html_escape($supplier->contact_person ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Phone</dt><dd class="col-sm-8"><?php echo html_escape($supplier->phone ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Address</dt><dd class="col-sm-8"><?php echo html_escape($supplier->address ?: 'N/A'); ?></dd>
        <?php $products = $supplier->get_supplier_products; ?>

        <dt class="col-sm-4">Products</dt>
        <dd class="col-sm-8">
            <?php if (!empty($products)): ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($products as $product): ?>
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <a
                                href="<?php echo site_url('products/view/' . (int) $product->id); ?>"
                                class="text-decoration-none fw-semibold"
                                data-modal-url="<?php echo site_url('products/view/' . (int) $product->id); ?>"
                            >
                                <?php echo html_escape(isset($product->product_name) ? $product->product_name : 'N/A'); ?>
                            </a>
                            <span class="badge <?php echo (int) $product->status === 1 ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                <?php echo (int) $product->status === 1 ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                N/A
            <?php endif; ?>
        </dd>
        <dt class="col-sm-4">Total Products</dt>
        <dd class="col-sm-8"><?php echo (int) $supplier->total_products; ?></dd>
        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge <?php echo $supplier->status ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo $supplier->status ? 'Active' : 'Inactive'; ?></span></dd>

    </dl>
</div>

<?php $this->load->view('components/modal/footer', array('close_label' => 'Close')); ?>
