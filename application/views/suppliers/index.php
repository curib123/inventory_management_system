<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="h4 mb-1">Suppliers</h2>
        <p class="text-body-secondary mb-0">Manage supplier information and product sources.</p>
    </div>
    <button type="button" class="btn btn-primary" data-modal-url="<?php echo site_url('suppliers/add'); ?>">
        <i class="bi bi-plus-lg me-1"></i>
        Add Supplier
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" data-datatable-server data-source="<?php echo site_url('suppliers/datatable'); ?>">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th data-orderable="false">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->load->view('modal/container'); ?>
