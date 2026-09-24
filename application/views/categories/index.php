<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="h4 mb-1">Categories</h2>
        <p class="text-body-secondary mb-0">Organize products into manageable inventory groups.</p>
    </div>
    <button type="button" class="btn btn-primary" data-modal-url="<?php echo site_url('categories/add'); ?>">
        <i class="bi bi-plus-lg me-1"></i>
        Add Category
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" data-datatable-server data-source="<?php echo site_url('categories/datatable'); ?>">
                <thead class="table-light">
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
        </div>
    </div>
</div>

<?php $this->load->view('modal/container'); ?>
