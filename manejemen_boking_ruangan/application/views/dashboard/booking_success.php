<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Success</title>
    <link href="<?= base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/flatpickr.min.css'); ?>">
</head>
<style>
    html {
        overflow-y: scroll; scrollbar-width: none;
        font-size: 90%;
    }
    body {
        min-height: 100vh;
        background: url('<?= base_url('assets/image/image.png'); ?>') center center/cover no-repeat fixed;
        position: relative;
    }
</style>
<body>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
        <div id="bookingToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    Booking successful! Your data has been saved.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12 col-md-8 mx-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-white rounded shadow-sm px-3 py-2">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard/back'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Booking Success</li>
                    </ol>
                </nav>
                <div class="progress mt-2" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-success text-white text-center">
                        <h2 class="mb-0"><i class="fa-solid fa-circle-check me-2"></i>Booking Successful!</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-4 align-items-center">
                            <div class="col-md-5 text-center">
                                <div class="mb-3">
                                    <img src="<?= base_url('assets/image/1.jpg'); ?>" alt="Toppan Building" class="img-fluid rounded shadow-sm w-75">
                                </div>
                                <div>
                                    <span class="badge bg-success-subtle border border-success-subtle text-success-emphasis rounded-pill px-3 py-2" data-bs-toggle="tooltip"  data-bs-placement="top"  data-bs-trigger="hover focus click" title="You have access to use this room at the specified time!">
                                        <i class="fa-solid fa-key"></i>
                                        <span id="tokenText"> <?= htmlspecialchars($room->nama_ruangan); ?></span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <h5 class="fw-bold mb-3">Booking Details</h5>
                                <ul class="list-group list-group-flush mb-3">
                                    <li class="list-group-item">
                                        <i class="fa-solid fa-user text-success me-2"></i>
                                        <strong>Name:</strong> <?= htmlspecialchars($booking['name']); ?>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fa-solid fa-door-open text-success me-2"></i>
                                        <strong>Room:</strong> <?= htmlspecialchars($room->nama_ruangan); ?>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fa-solid fa-calendar-days text-success me-2"></i>
                                        <strong>Date:</strong> <?= strftime('%d %B %Y', strtotime(htmlspecialchars($booking['date']))); ?>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fa-solid fa-clock text-success me-2"></i>
                                        <strong>Time:</strong> <?= strftime('%H:%M', strtotime(htmlspecialchars($booking['start_time']))); ?> - <?= strftime('%H:%M', strtotime(htmlspecialchars($booking['end_time']))); ?>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fa-solid fa-users text-success me-2"></i>
                                        <strong>Capacity:</strong> <?= htmlspecialchars($room->kapasitas); ?> people
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fa-solid fa-network-wired text-success me-2"></i>
                                        <strong>Room IP Address:</strong> <?= htmlspecialchars($booking['ip_address']); ?>
                                    </li>
                                </ul>
                                <div class="d-flex gap-2 mb-2">
                                    <button class="btn btn-outline-success w-50 flex-fill" onclick="window.location.href='<?= base_url('dashboard/back'); ?>'">
                                        <i class="fa-solid fa-house"></i> Back to Dashboard
                                    </button>
                                    <button class="btn w-50 btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#detailModal">
                                        <i class="fa-solid fa-circle-info"></i> View Details
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <?php if ($booking['sisa_update'] > 0 && $booking['status']=='upcoming'){ ?>
                                    <button class="btn w-50 btn-outline-warning flex-fill" data-bs-toggle="collapse" data-bs-target="#rescheduleForm<?=$booking['id_booking'];?>" aria-expanded="false" aria-controls="rescheduleForm<?=$booking['id_booking'];?>">
                                        <i class="fa-solid fa-pen-to-square"></i> Reschedule/Move room
                                    </button>
                                    <?php } else { ?>
                                    <span class="btn w-50 btn-outline-secondary flex-fill"  data-bs-toggle="tooltip"  data-bs-placement="top"  data-bs-trigger="hover focus click" title="Maximum booking update limit reached."  style="cursor: not-allowed;">
                                        <i class="fa-solid fa-pen-to-square"></i> Reschedule/Move Room
                                    </span>
                                    <?php } ?>
                                    <button class="btn w-50 btn-outline-danger flex-fill" onclick="if(confirm('Are you sure you want to cancel this booking?')) { window.location.href='<?= base_url('dashboard/cancel_booking/' . $booking['id_booking']); ?>'; }">
                                        <i class="fa-solid fa-xmark"></i> Cancel Booking
                                    </button>
                                </div>
                            </div>
                            <div class="collapse mt-3" id="rescheduleForm<?= $booking['id_booking']; ?>">
                                <div class="card card-body border-0 shadow-sm">
                                    <span class="badge bg-primary-subtle border border-primary-subtle text-primary-emphasis rounded-pill px-3 py-2 mb-3">
                                        <i class="far fa-calendar-alt"></i> Reschedule & Change Room (<?= $booking['sisa_update']; ?> Updates Left)
                                    </span>
                                    <form action="<?= base_url('dashboard/proses_update_booking/' . $booking['id_booking']); ?>" method="POST" id="rescheduleBookingForm">
                                        <div class="row g-4 mb-4">
                                            <div>
                                                <input type="email" name="email" value="<?= htmlspecialchars($booking['email']); ?>" hidden>   
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control datepicker" id="newDate" name="new_date"
                                                        value="<?= date('Y-m-d', strtotime($booking['date'])); ?>" placeholder="New Date" required>
                                                    <label for="newDate">New Date</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control timepicker" id="newStartTime" name="new_start_time"
                                                        value="<?= strftime('%H:%M', strtotime(htmlspecialchars($booking['start_time']))); ?>" placeholder="Start Time" required>
                                                    <label for="newStartTime">New Start Time</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control timepicker" id="newEndTime" name="new_end_time"
                                                        value="<?= strftime('%H:%M', strtotime(htmlspecialchars($booking['end_time']))); ?>" placeholder="End Time" required>
                                                    <label for="newEndTime">New End Time</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 d-grid align-self-end">
                                                <button type="button" class="btn btn-outline-secondary" onclick="resetRescheduleForm();">
                                                    <i class="fa fa-rotate-left"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                        <div class="collapse mb-4" id="availableRoomsReschedule">
                                            <div class="card card-body" style="max-height: 400px; overflow-y: auto;">
                                                <span class="badge bg-success-subtle border border-success-subtle text-success-emphasis rounded-pill px-3 py-2 mb-3">
                                                    Rooms Available at That Time
                                                </span>
                                                <div class="accordion" id="roomsAccordionReschedule">
                                                    <!-- AJAX result appears here -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fa-solid fa-paper-plane"></i> Submit Change Request
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center bg-white">
                        <span class="text-muted small">Save this message for your verification when using the room.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="detailModalLabel"><i class="fa-solid fa-circle-info"></i> Full Booking Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($booking['name']); ?></li>
                        <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($booking['email']); ?></li>
                        <li class="list-group-item"><strong>Room:</strong> <?= htmlspecialchars($room->nama_ruangan); ?></li>
                        <li class="list-group-item"><strong>Facilities:</strong> <?= htmlspecialchars($room->fasilitas); ?></li>
                        <li class="list-group-item"><strong>Date:</strong> <?= strftime('%d %B %Y', strtotime(htmlspecialchars($booking['date']))); ?></li>
                        <li class="list-group-item"><strong>Time:</strong> <?= strftime('%H:%M', strtotime(htmlspecialchars($booking['start_time']))); ?> - <?= strftime('%H:%M', strtotime(htmlspecialchars($booking['end_time']))); ?></li>
                        <li class="list-group-item"><strong>Capacity:</strong> <?= htmlspecialchars($room->kapasitas); ?> people</li>
                        <li class="list-group-item"><strong>Room IP Address:</strong> <?= htmlspecialchars($booking['ip_address']); ?></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="chooseRoomModalReschedule" tabindex="-1" aria-labelledby="chooseRoomModalLabelReschedule" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="chooseRoomModalLabelReschedule"><i class="fa fa-exclamation-triangle text-white"></i> Select a Room!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Please select a room before changing your booking.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= base_url('assets/js/bootstrap.min.js');?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js');?>"></script>
    <script src="<?= base_url('assets/js/flatpickr.js');?>"></script>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        var toast = new bootstrap.Toast(document.getElementById('bookingToast'));
        toast.show();

        flatpickr("#newDate", {
            dateFormat: "Y-m-d",
            minDate: "today",
            altInput: true,
            altFormat: "l, d F Y",
            disableMobile: "true"
        });
        flatpickr("#newStartTime", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            disableMobile: "true",
            time_24hr: true
        });
        flatpickr("#newEndTime", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            disableMobile: "true",
            time_24hr: true
        });

        function resetRescheduleForm() {
            document.getElementById('newDate').value = "<?= htmlspecialchars($booking['date']); ?>";
            document.getElementById('newStartTime').value = "<?= strftime('%H:%M', strtotime(htmlspecialchars($booking['start_time']))); ?>";
            document.getElementById('newEndTime').value = "<?= strftime('%H:%M', strtotime(htmlspecialchars($booking['end_time']))); ?>";
            document.getElementById('roomsAccordionReschedule').innerHTML = '';
            let collapse = bootstrap.Collapse.getOrCreateInstance(document.getElementById('availableRoomsReschedule'));
            collapse.hide();
        }

        function cekRuanganReschedule() {
            let date = document.getElementById('newDate').value;
            let start_time = document.getElementById('newStartTime').value;
            let end_time = document.getElementById('newEndTime').value;

            document.querySelectorAll('input[name="selected_room_reschedule"]').forEach(el => el.checked = false);

            if (!date || !start_time || !end_time) {
                document.getElementById('roomsAccordionReschedule').innerHTML = '';
                let collapse = bootstrap.Collapse.getOrCreateInstance(document.getElementById('availableRoomsReschedule'));
                collapse.hide();
                return;
            }

            fetch('<?= base_url('dashboard/cek_ketersediaan_ruangan'); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `date=${date}&start_time=${start_time}&end_time=${end_time}&ignore_booking=<?= $booking['id_booking']; ?>`
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
                            <h2 class="accordion-header" id="headingRes${room.id_ruangan}">
                                <button class="accordion-button collapsed bg-gradient text-white fw-bold rounded shadow-sm"
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapseRes${room.id_ruangan}" 
                                        aria-expanded="false" 
                                        aria-controls="collapseRes${room.id_ruangan}"
                                        style="background: linear-gradient(135deg, #198754, #146c43);">
                                    🏢 ${room.nama_ruangan}
                                </button>
                            </h2>
                            <div id="collapseRes${room.id_ruangan}" class="accordion-collapse collapse"
                                aria-labelledby="headingRes${room.id_ruangan}" data-bs-parent="#roomsAccordionReschedule">
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
                                                    name="selected_room_reschedule" 
                                                    id="roomRes${room.id_ruangan}" 
                                                    value="${room.id_ruangan}" 
                                                    style="transform: scale(1.3);">
                                                <label class="form-check-label fw-bold text-success ms-2" for="roomRes${room.id_ruangan}">
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
                document.getElementById('roomsAccordionReschedule').innerHTML = html;
                let collapse = bootstrap.Collapse.getOrCreateInstance(document.getElementById('availableRoomsReschedule'));
                collapse.show();
            });
        }

        document.getElementById('newDate').addEventListener('change', cekRuanganReschedule);
        document.getElementById('newStartTime').addEventListener('change', cekRuanganReschedule);
        document.getElementById('newEndTime').addEventListener('change', cekRuanganReschedule);

        document.getElementById('rescheduleBookingForm').addEventListener('submit', function(e) {
            let date = document.getElementById('newDate').value;
            let start_time = document.getElementById('newStartTime').value;
            let end_time = document.getElementById('newEndTime').value;
            let selectedRoom = document.querySelector('input[name="selected_room_reschedule"]:checked');

            if (date && start_time && end_time && !selectedRoom) {
                e.preventDefault();
                let modal = new bootstrap.Modal(document.getElementById('chooseRoomModalReschedule'));
                modal.show();
            }
        });
    </script>
</body>
</html>