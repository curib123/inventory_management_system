<!-- Profile Header -->
<div class="bg-primary rounded text-white p-4">
    <div class="d-flex align-items-center">

        <div
            class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
            style="width: 64px; height: 64px;"
        >
            <i class="bi bi-person-badge fs-2"></i>
        </div>

        <div class="ms-3 flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h2 class="h4 mb-1">
                    <?php echo html_escape($role->role_name); ?>
                </h2>

                <?php if ($role->status): ?>
                    <span class="badge bg-success-subtle text-success">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Active
                    </span>
                <?php else: ?>
                    <span class="badge bg-light text-secondary">
                        <i class="bi bi-x-circle-fill me-1"></i>
                        Inactive
                    </span>
                <?php endif; ?>
            </div>

            <p class="mb-0 text-white-50 small">
                Role ID #<?php echo (int) $role->id; ?>
            </p>
        </div>

    </div>
</div>

<!-- Profile Content -->
<div class="p-4">

    <!-- Description -->
    <div class="mb-4">
        <h6 class="text-uppercase text-muted fw-bold small mb-2">
            <i class="bi bi-card-text me-1"></i>
            Description
        </h6>

        <div class="bg-white border rounded-3 p-3">
            <p class="mb-0 text-secondary">
                <?php echo html_escape($role->description ?: 'No description provided.'); ?>
            </p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">

        <!-- Role ID -->
        <div class="col-6">
            <div class="bg-white border rounded-3 p-3 h-100">
                <div class="d-flex align-items-center">
                    <div
                        class="bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center"
                        style="width: 42px; height: 42px;"
                    >
                        <i class="bi bi-hash fs-5"></i>
                    </div>

                    <div class="ms-3">
                        <div class="text-muted small">
                            Role ID
                        </div>
                        <div class="fw-bold">
                            <?php echo (int) $role->id; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users -->
        <div class="col-6">
            <div class="bg-white border rounded-3 p-3 h-100">
                <div class="d-flex align-items-center">
                    <div
                        class="bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center"
                        style="width: 42px; height: 42px;"
                    >
                        <i class="bi bi-people fs-5"></i>
                    </div>

                    <div class="ms-3">
                        <div class="text-muted small">
                            Users
                        </div>

                        <div class="fw-bold">
                            <?php echo (int) $user_count; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Role Information -->
    <div>
        <h6 class="text-uppercase text-muted fw-bold small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            Role Information
        </h6>

        <div class="bg-white border rounded-3 overflow-hidden">

            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                <div class="d-flex align-items-center">
                    <i class="bi bi-person-badge text-primary fs-5 me-3"></i>
                    <span class="text-muted">Role Name</span>
                </div>

                <span class="fw-semibold text-end">
                    <?php echo html_escape($role->role_name); ?>
                </span>
            </div>

            <div class="d-flex align-items-center justify-content-between p-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-toggle-on text-primary fs-5 me-3"></i>
                    <span class="text-muted">Status</span>
                </div>

                <?php if ($role->status): ?>
                    <span class="badge text-bg-success">
                        Active
                    </span>
                <?php else: ?>
                    <span class="badge text-bg-secondary">
                        Inactive
                    </span>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>

<!-- Footer -->
<div class="bg-white border-top p-3">
    <div class="d-flex justify-content-end">

        <button
            type="button"
            class="btn btn-outline-secondary"
            data-modal-close
        >
            <i class="bi bi-x-lg me-1"></i>
            Close
        </button>

    </div>
</div>


</div>
