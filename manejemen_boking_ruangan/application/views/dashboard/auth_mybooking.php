<div class="bg-overlay"></div>
<div class="container d-flex align-items-center justify-content-center" style="min-height:100vh; position:relative; z-index:1;">
    <div class="col-md-7 col-lg-4 card bg-white p-3">
        <div class="text-center mb-4">
            <span class="text-primary fs-1"><i class="fa-solid fa-door-open"></i></span>
            <h3 class="fw-bold mt-2 mb-1 text-primary">Room Booking</h3>
            <div class="text-muted mb-2" style="font-size:12px">Please enter your booking code to continue</div>
        </div>

        <?php if($this->session->flashdata('success')) { ?>
            <div class="alert alert-success alert-dismissible show mt-3 mb-3 mx-3" role="alert">
                <i class="fa fa-check-circle"></i> <?= $this->session->flashdata('success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } elseif($this->session->flashdata('error')) { ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3 mb-3 mx-3" role="alert">
                <i class="fa fa-exclamation-circle"></i> <?= $this->session->flashdata('error'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <form method="post" action="<?= base_url('auth/login'); ?>">
            <div class="form-floating mb-2">
                <input type="password" class="form-control" id="code_booking" name="code_booking" placeholder="Booking Code" required>
                <label for="code_booking"><i class="fa fa-key me-1"></i> Booking Code</label>
            </div>
            <div class="text-end mb-3">
                <a href="<?= base_url('auth/authdepartement'); ?>" class="text-decoration-none small text-primary">
                    <i class="fa fa-users me-1"></i> Continue as a Departement
                </a>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    <i class="fa fa-sign-in-alt me-2"></i> Enter
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>