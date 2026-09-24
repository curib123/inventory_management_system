<div class="container-fluid px-0">

<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <!-- Header -->
    <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3">
                <i class="bi bi-person-badge fs-5"></i>
            </div>

            <div>
                <h5 class="fw-bold mb-1">Role Information</h5>
                <p class="text-muted small mb-0">
                    Create a role and assign the appropriate permissions.
                </p>
            </div>
        </div>
    </div>

<?php if (validation_errors() || !empty($form_error)): ?>
    <div class="alert alert-danger d-flex align-items-center border-0 shadow-sm rounded-3 mb-3 py-2 mm-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div class="text-truncate">
            <?php echo validation_errors('', ' '); ?>
            <?php echo !empty($form_error) ? html_escape($form_error) : ''; ?>
        </div>
    </div>
<?php endif; ?>


    <div class="card-body px-4 pb-4">

        <!-- Role Details -->
        <div class="row g-3">

            <!-- Role Name -->
            <div class="col-md-8">
                <label for="role_name" class="form-label fw-semibold">
                    Role Name <span class="text-danger">*</span>
                </label>

                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-person-badge text-muted"></i>
                    </span>

                    <input
                        type="text"
                        class="form-control border-start-0 ps-0"
                        id="role_name"
                        name="role_name"
                        required
                        maxlength="50"
                        placeholder="e.g. Administrator"
                        value="<?php echo html_escape(
                            set_value(
                                'role_name',
                                isset($role) && $role ? $role->role_name : ''
                            )
                        ); ?>"
                    >
                </div>
            </div>

            <!-- Status -->
            <div class="col-md-4">
                <label for="status" class="form-label fw-semibold">
                    Status
                </label>

                <?php
                $selected_status = set_value(
                    'status',
                    isset($role) && $role ? $role->status : 1
                );
                ?>

                <select
                    class="form-select"
                    id="status"
                    name="status"
                >
                    <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>
                        Active
                    </option>

                    <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>
                        Inactive
                    </option>
                </select>
            </div>

            <!-- Description -->
            <div class="col-12">
                <label for="description" class="form-label fw-semibold">
                    Description
                </label>

                <textarea
                    class="form-control"
                    id="description"
                    name="description"
                    rows="3"
                    maxlength="255"
                    placeholder="Enter a short description for this role"
                ><?php echo html_escape(
                    set_value(
                        'description',
                        isset($role) && $role ? $role->description : ''
                    )
                ); ?></textarea>

                <div class="form-text">
                    Briefly describe what this role is used for.
                </div>
            </div>

        </div>

        <!-- Divider -->
        <hr class="my-4">

        <!-- Permissions Header -->
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">

            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3">
                    <i class="bi bi-shield-lock fs-5"></i>
                </div>

                <div>
                    <h5 class="fw-bold mb-1">Permissions</h5>
                    <p class="text-muted small mb-0">
                        Select the permissions assigned to this role.
                    </p>
                </div>
            </div>

            <?php if (!empty($permissions)): ?>
                <span class="badge rounded-pill text-bg-primary px-3 py-2">
                    <i class="bi bi-list-check me-1"></i>
                    <?php echo count($permissions); ?> Available
                </span>
            <?php endif; ?>

        </div>

        <!-- Permissions -->
        <?php if (!empty($permissions)): ?>

            <div class="row g-3">

                <?php foreach ($permissions as $permission): ?>

                    <div class="col-md-6 col-xl-4">

                        <label
                            for="permission_<?php echo (int) $permission->id; ?>"
                            class="permission-item d-flex align-items-start gap-3 border rounded-3 p-3 h-100 bg-white"
                        >

                            <input
                                class="form-check-input flex-shrink-0 mt-1"
                                type="checkbox"
                                name="permissions[]"
                                value="<?php echo (int) $permission->id; ?>"
                                id="permission_<?php echo (int) $permission->id; ?>"
                                <?php echo in_array(
                                    (int) $permission->id,
                                    $selected_permissions,
                                    TRUE
                                ) ? 'checked' : ''; ?>
                            >

                            <span class="flex-grow-1">
                                <span class="fw-semibold d-block text-dark">
                                    <?php echo html_escape($permission->permission_name); ?>
                                </span>

                                <small class="text-muted d-flex align-items-center mt-1">
                                    <i class="bi bi-folder2 me-1"></i>
                                    <?php echo html_escape($permission->module_name); ?>
                                </small>
                            </span>

                        </label>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="alert alert-light border rounded-3 d-flex align-items-center mb-0">
                <i class="bi bi-info-circle text-muted fs-5 me-2"></i>
                <span class="text-muted">
                    No active permissions found.
                </span>
            </div>

        <?php endif; ?>

    </div>

    <!-- Footer -->
    <div class="card-footer bg-light border-top px-4 py-3">
        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">

            <button
                type="button"
                class="btn btn-outline-secondary px-4"
                data-modal-close
            >
                <i class="bi bi-x-lg me-1"></i>
                Cancel
            </button>

            <button
                type="submit"
                class="btn btn-primary px-4"
            >
                <i class="bi bi-check-lg me-1"></i>
                Save Role
            </button>

        </div>
    </div>

</div>

<?php echo form_close(); ?>


</div>


