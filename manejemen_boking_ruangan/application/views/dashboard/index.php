<div class="col-md-9 container py-5 " style="position:relative; z-index:1;">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Room Booking Form</h4>
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
        <div class="card-body">
            <form method="post" action="<?= base_url('dashboard/proses_booking'); ?>" id="bookingForm">
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <span class="badge bg-primary-subtle border border-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                            <i class="fa-solid fa-users"></i> Personal Data
                        </span>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required>
                            <label for="name">Full Name</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="email" name="email" placeholder="Your Email" required>
                            <label for="email">Your Email</label>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <span class="badge bg-primary-subtle border border-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                            <i class="far fa-calendar-alt"></i> Choose Schedule
                        </span>
                    </div>

                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" class="form-control datepicker" id="date" name="date" placeholder="Booking Date" required>
                            <label for="date">Date</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="text" class="form-control timepicker" id="start_time" name="start_time" placeholder="Start Time" required>
                            <label for="start_time">Start Time</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="text" class="form-control timepicker" id="end_time" name="end_time" placeholder="End Time" required>
                            <label for="end_time">End Time</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-grid align-self-end">
                        <button type="button" class="btn btn-outline-secondary " onclick="resetRescheduleForm()">
                            <i class="fa fa-rotate-left"></i> Reset
                        </button>
                    </div>
                </div>

                <div class="collapse mb-4" id="availableRooms">
                    <div class="card card-body" style="max-height: 400px; overflow-y: auto;">
                        <span class="badge bg-success-subtle border border-success-subtle text-success-emphasis rounded-pill px-3 py-2 mb-3">
                            Rooms Available at That Time
                        </span>
                        <div class="accordion" id="roomsAccordion">
                            <!-- AJAX result appears here -->
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Book Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="requireddata" tabindex="-1" aria-labelledby="requireddataLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="requireddataLabel">
                    <i class="fa fa-exclamation-triangle text-white"></i> Incomplete Data!
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Please fill in all required data before continuing.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="chooseRoomModal" tabindex="-1" aria-labelledby="chooseRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="chooseRoomModalLabel"><i class="fa fa-exclamation-triangle text-white"></i> Select a Room!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
        <div class="modal-body">
            Please select a room before making a booking.
        </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/flatpickr.js');?>"></script>
<script>
    flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        minDate: "today",
        altInput: true,
        altFormat: "l, d F Y",
        disableMobile: "true"
    });
    flatpickr(".timepicker", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        disableMobile: "true",
        time_24hr: true
    });
    function resetRescheduleForm() {
        document.getElementById('date').value = '';
        document.getElementById('start_time').value = '';
        document.getElementById('end_time').value = '';
        document.getElementById('roomsAccordion').innerHTML = '';
        let collapse = bootstrap.Collapse.getOrCreateInstance(document.getElementById('availableRooms'));
        collapse.hide();
        document.querySelectorAll('input[name="selected_room"]').forEach(el => el.checked = false);
    }

    function cekRuangan() {
        let date = document.getElementById('date').value;
        let start_time = document.getElementById('start_time').value;
        let end_time = document.getElementById('end_time').value;

        document.querySelectorAll('input[name="selected_room"]').forEach(el => el.checked = false);

        if (!date || !start_time || !end_time) {
            document.getElementById('roomsAccordion').innerHTML = '';
            let collapse = bootstrap.Collapse.getOrCreateInstance(document.getElementById('availableRooms'));
            collapse.hide();
            return;
        }

        fetch('<?= base_url('dashboard/cek_ketersediaan_ruangan'); ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `date=${date}&start_time=${start_time}&end_time=${end_time}`
        })
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<div class="alert alert-warning text-center">There are no rooms available at that time.</div>';
            } else {
                data.forEach(function(room, idx) {
                    html += `
                    <div class="accordion-item bg-dark text-white border-0 rounded mb-3 shadow-sm">
                        <h2 class="accordion-header" id="heading${room.id_ruangan}">
                            <button class="accordion-button collapsed bg-gradient text-white fw-bold rounded shadow-sm"
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse${room.id_ruangan}" 
                                    aria-expanded="false" 
                                    aria-controls="collapse${room.id_ruangan}"
                                    style="background: linear-gradient(135deg, #198754, #146c43);">
                                🏢 ${room.nama_ruangan}
                            </button>
                        </h2>
                        <div id="collapse${room.id_ruangan}" class="accordion-collapse collapse"
                            aria-labelledby="heading${room.id_ruangan}" data-bs-parent="#roomsAccordion">
                            <div class="accordion-body bg-light text-dark rounded-bottom shadow-sm">
                                <div class="row g-4 align-items-center">
                                    <div class="col-md-4 text-center">
                                        <img src="<?= base_url('assets/image/'); ?>${room.foto_ruangan}" 
                                            alt="Room Photo" 
                                            class="img-fluid rounded shadow-sm border" 
                                            style="max-height: 160px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-8">
                                        <p class="mb-2">
                                            <strong><i class="fa fa-couch text-success me-2"></i>Facilities:</strong>
                                            ${room.fasilitas}
                                        </p>
                                        <p class="mb-3">
                                            <strong><i class="fa fa-users text-success me-2"></i>Capacity:</strong>
                                            ${room.kapasitas} people
                                        </p>
                                        <div class="form-check my-3">
                                            <input class="form-check-input border-success" type="radio" 
                                                name="selected_room" 
                                                id="room${room.id_ruangan}" 
                                                value="${room.id_ruangan}" 
                                                style="transform: scale(1.3);">
                                            <label class="form-check-label fw-bold text-success ms-2" for="room${room.id_ruangan}">
                                                ✅ Select This Room
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
            }
            document.getElementById('roomsAccordion').innerHTML = html;
            let collapse = bootstrap.Collapse.getOrCreateInstance(document.getElementById('availableRooms'));
            collapse.show();
        });
    }

    document.getElementById('date').addEventListener('change', cekRuangan);
    document.getElementById('start_time').addEventListener('change', cekRuangan);
    document.getElementById('end_time').addEventListener('change', cekRuangan);

    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        let name = document.getElementById('name').value.trim();
        let email = document.getElementById('email').value.trim();
        let date = document.getElementById('date').value;
        let start_time = document.getElementById('start_time').value;
        let end_time = document.getElementById('end_time').value;
        let selectedRoom = document.querySelector('input[name="selected_room"]:checked');

        if (!name || !email || !date || !start_time || !end_time) {
            e.preventDefault();
            let modal = new bootstrap.Modal(document.getElementById('requireddata'));
            modal.show();
        }
        if (name && email && date && start_time && end_time && !selectedRoom) {
            e.preventDefault();
            let modal = new bootstrap.Modal(document.getElementById('chooseRoomModal'));
            modal.show();
        }
    });
</script>
</body>
</html>