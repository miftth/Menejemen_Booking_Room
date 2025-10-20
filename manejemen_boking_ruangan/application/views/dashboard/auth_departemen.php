<div class="bg-overlay"></div>
<div class="container d-flex align-items-center justify-content-center" style="min-height:100vh; position:relative; z-index:1;">
    <div class="col-md-7 col-lg-4 card bg-white p-3">
        <div class="text-center mb-4">
            <span class="text-primary fs-1"><i class="fa-solid fa-door-open"></i></span>
            <h3 class="fw-bold mt-2 mb-1 text-primary">Departement Booking</h3>
            <div class="text-muted mb-2" style="font-size:12px">Please enter your departement to continue</div>
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
                <input type="text" class="form-control" id="departement_name" name="departement_name" placeholder="Departement Name" required>
                <label for="departement_name"><i class="fa fa-building me-1"></i> Departement Name</label>
            </div>
            <div class="form-floating mb-2">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password"><i class="fa fa-lock me-1"></i> Password</label>
            </div>
            <div class="text-end mb-3">
                <a href="<?= base_url('auth/'); ?>" class="text-decoration-none small text-primary">
                    <i class="fa fa-key me-1"></i> Continue as a Code Booking
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