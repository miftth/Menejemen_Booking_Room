<style>
.menu-link {
    border-radius: 8px;
    transition: background 0.2s, color 0.2s;
}
.menu-link:hover, .menu-link:focus {
    background: #e9ecef;
    color: #0d6efd !important;
    text-decoration: none;
}
.offcanvas-header {
    border-bottom: 1px solid #dee2e6;
}
.offcanvas-body {
    background: #f8f9fa;
}
</style>
<div class="offcanvas offcanvas-start shadow-lg" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
    <div class="offcanvas-header bg-primary text-white">
        <h5 class="offcanvas-title fw-bold" id="sidebarLabel">
            <i class="bi bi-list me-2"></i> Menu
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body px-0">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="<?= base_url('dashboard/index'); ?>" class="nav-link px-4 py-3 text-dark menu-link">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('auth/index'); ?>" class="nav-link px-4 py-3 text-dark menu-link">
                    <i class="bi bi-calendar-check me-2"></i> My Booking
                </a>
            </li>
            <li><hr class="dropdown-divider mx-4"></li>
            <?php if ($this->session->userdata('id_departemen')): ?>
            <li class="nav-item">
                <a href="<?= base_url('dashboard/back'); ?>" class="nav-link px-4 py-3 text-danger menu-link">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <div class="mt-4 text-center small text-muted">
            <i class="bi bi-info-circle"></i> Booking Room Management
        </div>
    </div>
</div>