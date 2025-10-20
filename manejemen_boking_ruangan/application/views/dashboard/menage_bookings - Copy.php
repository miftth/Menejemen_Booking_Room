<?php
    if (!isset($bookings) || !is_array($bookings)) $bookings = [];
    if (!isset($total_booking)) $total_booking = count($bookings);
    if (!isset($rooms)) $rooms = [];
    if (!isset($total_room)) $total_room = isset($rooms) ? count($rooms) : 0;
    if (!isset($favorite_room)) $favorite_room = $total_room ? ($rooms[0]['nama_ruangan'] ?? '—') : '—';
    if (!isset($stats_room)) $stats_room = [];
    if (!isset($stats_month)) $stats_month = array_fill(0,12,0);
    $total_upcoming = 0;
    $total_ongoing = 0;
    $total_finished = 0;
    foreach ($bookings as $b) {
        $now = strtotime(date('Y-m-d H:i'));
        $start = strtotime($b->date . ' ' . $b->start_time);
        $end = strtotime($b->date . ' ' . $b->end_time);
        if ($now < $start) $total_upcoming++;
        elseif ($now >= $start && $now <= $end) $total_ongoing++;
        else $total_finished++;
    }
    $status_counts = [
        'upcoming' => $total_upcoming,
        'ongoing' => $total_ongoing,
        'finished' => $total_finished
    ];
    function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
    $js_stats_room = json_encode(array_map(function($r){
        $name = is_object($r) ? ($r->nama_ruangan ?? '—') : ($r['nama_ruangan'] ?? '—');
        $total = is_object($r) ? ($r->total ?? 0) : ($r['total'] ?? 0);
        return ['nama_ruangan'=>$name,'total'=>$total];
    }, $stats_room));
    $js_stats_month = json_encode(array_values($stats_month));
    $js_status_counts = json_encode($status_counts);
?>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="<?= base_url('assets/js/chart.umd.min.js');?>"></script>
    <style>
        :root{
            --bg-900: #05060b;
            --bg-800: #0b1120;
            --glass: rgba(255,255,255,0.03);
            --glass-2: rgba(255,255,255,0.025);
            --glass-border: rgba(255,255,255,0.06);
            --muted: #9aa4b2;
            --accent-cyan: #5ee8ff;
            --accent-purple: #a78bfa;
            --accent-green: #3ee89a;
            --card-shadow: 0 12px 40px rgba(2,6,23,0.6);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        }
        html,body {
            height:100%;
            color: #e6eef8;
            margin:0;
            padding:0;
        }
        .wrap {
            max-width:1200px;
            margin:28px auto;
            padding:22px;
            border-radius:16px;
            border:1px solid var(--glass-border);
            background: linear-gradient(180deg, rgba(255,255,255,0.015), rgba(255,255,255,0.01));
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(8px) saturate(120%);
        }
        header {
            display:flex;
            align-items:center;
            margin-bottom:18px;
        }
        .brand {
            display:flex;
            align-items:center;
            gap:12px;
        }
        .brand .logo {
            width:56px;
            height:56px;
            border-radius:12px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
            display:flex;
            align-items:center;
            justify-content:center;
            color:#031124;
            font-weight:800;
            font-size:20px;
            box-shadow: 0 10px 30px rgba(94,232,255,0.06);
        }
        .brand h1 { margin:0; font-size:1.05rem; font-weight:800; letter-spacing:0.2px; }
        .brand p { margin:0; color:var(--muted); font-size:0.85rem; }
        .header-actions { display:flex; gap:30px; align-items:center;margin-left:115px }
        .kpi-row { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:18px; }
        .card-glass {
            padding:14px;
            border-radius:12px;
            background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border:1px solid var(--glass-border);
            box-shadow: 0 8px 30px rgba(2,6,23,0.6);
        }
        .kpi {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
        }
        .kpi .left { display:flex; gap:12px; align-items:center; }
        .kpi .icon {
            width:56px;
            height:56px;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:20px;
            background: linear-gradient(135deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border:1px solid rgba(255,255,255,0.03);
        }
        .kpi .value { font-weight:800; font-size:1.6rem; color:#fff; }
        .kpi .label { color:var(--muted); font-size:0.85rem; }
        .grid { display:grid; grid-template-columns: 1fr 420px; gap:14px; align-items:start; }
        @media (max-width:1100px){
            .grid { grid-template-columns: 1fr; }
            .kpi-row { grid-template-columns:repeat(1,1fr); }
        }
        .small-card { padding:12px; border-radius:10px; background:linear-gradient(180deg, rgba(255,255,255,0.015), rgba(255,255,255,0.01)); border:1px solid var(--glass-border); }
        .table-wrap { margin-top:12px; border-radius:12px; overflow:hidden; border:1px solid var(--glass-border); }
        table { border-collapse:collapse; width:100%; }
        thead th {
            background: linear-gradient(90deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            color: #e6fdf6;
            font-weight:700;
            border-bottom:1px solid rgba(255,255,255,0.03);
            padding:12px 14px;
            text-align:left;
            font-size:0.85rem;
        }
        tbody td {
            padding:12px 14px;
            font-size:0.9rem;
            border-bottom:1px dashed rgba(255,255,255,0.02);
            vertical-align:middle;
        }
        tbody tr { transition: transform .12s ease, background .12s ease, box-shadow .12s ease; }
        tbody tr:hover {
            transform: translateY(-6px);
            background: linear-gradient(90deg, rgba(94,232,255,0.02), rgba(167,139,250,0.01));
            box-shadow: 0 10px 30px rgba(94,232,255,0.03);
        }
        .badge-status {
            padding:6px 10px;
            border-radius:999px;
            font-weight:700;
            font-size:0.82rem;
            display:inline-block;
            box-shadow: 0 6px 30px rgba(2,6,23,0.6) inset;
        }
        .badge-upcoming { background: linear-gradient(90deg, #ffcf6e, #ffb347); color: #111; box-shadow: none !important;text-shadow: none !important;}
        .badge-ongoing { background: linear-gradient(90deg, #3ee89a, #14c77a); color: #051220;   box-shadow: none !important;  text-shadow: none !important;}
        .badge-finished { background: linear-gradient(90deg, #3c6fb6ff, #5582ddff); color: #fff;   box-shadow: none !important;  text-shadow: none !important;}

        .btn-action {
            border-radius:10px;
            padding:6px 8px;
            border:1px solid rgba(255,255,255,0.03);
            background:transparent;
            color:#dff6f5;
        }
        .filters { display:flex; gap:12px; align-items:center; margin-bottom:12px; flex-wrap:wrap; }
        .filters .form-control, .filters .form-select { background:transparent; color:#dff6f5; border:1px solid rgba(255,255,255,0.03); }
        .search-wrap { display:flex; align-items:center; gap:8px; background:var(--glass); border-radius:10px; padding:8px 10px; border:1px solid var(--glass-border); }
        .search-wrap input { background:transparent; border:0; outline:0; color:#e9fbfb; width:320px; }
        .muted-small { color:var(--muted); font-size:0.8rem; }
        .float-up { transform: translateY(8px); opacity:0; }
        .float-up.show { transform: translateY(0); opacity:1; transition:all .6s cubic-bezier(.2,.9,.3,1); }
        .equal-cards-row { display:flex; gap:14px; align-items:stretch; }
        .equal-card { flex:1 1 0; display:flex; flex-direction:column; }
        .card-body-canvas { flex:1 1 0; display:flex; flex-direction:column; justify-content:flex-start; }
        .canvas-wrap { flex: 0 0 auto; }
        .list-scroll { max-height:120px; overflow:auto; }
        .dateFilter::placeholder{ color:#FFF; opacity: 1;}
        #bookingStatusChart { width: 80%;   height: 200px;  margin: 0 auto; 
        }
    </style>
</head>
        <div class="wrap">
            <header>
                <div class="brand">
                    <div class="logo"><i class="fa-solid fa-calendar-check"></i></div>
                    <div>
                        <h1>Booking Management</h1>
                        <p><?= date('Y'); ?></p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="search-wrap" title="Search (press /)">
                        <i class="fa-solid fa-magnifying-glass" style="color:var(--muted)"></i>
                        <input id="globalSearch" placeholder="Cari ruangan, nama, fasilitas, tanggal..." />
                        <button id="clearSearch" class="btn btn-sm btn-link" title="Clear"><i class="fa-solid fa-xmark" style="color:var(--muted)"></i></button>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <div style="padding:8px 12px; border-radius:10px; border:1px solid rgba(255,255,255,0.03); background:transparent;">
                            <div class="muted-small">Summary</div>
                            <div style="font-weight:800; font-size:0.95rem;" id="summaryText"><?= e($status_counts['upcoming']); ?> upcoming · <?= e($status_counts['ongoing']); ?> ongoing · <?= e($status_counts['finished']); ?> finished</div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="kpi-row">
                <div class="card-glass float-up" id="card1">
                    <div class="kpi">
                        <div class="left">
                            <div class="icon" style="background: linear-gradient(135deg, rgba(94,232,255,0.08), rgba(167,139,250,0.06));">
                                <i class="fa-solid fa-list-check" style="color:var(--accent-cyan); font-size:20px;"></i>
                            </div>
                            <div>
                                <div class="label">Total Bookings</div>
                                <div class="value"><?= e($total_booking); ?></div>
                                <div class="muted-small">Favorite: <?= e($favorite_room); ?></div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="card-glass float-up" id="card2">
                    <div class="kpi">
                        <div class="left">
                            <div class="icon" style="background: linear-gradient(135deg, rgba(255,191,105,0.05), rgba(255,140,140,0.03));">
                                <i class="fa-solid fa-door-open" style="color:#ffd19a; font-size:20px;"></i>
                            </div>
                            <div>
                                <div class="label">Total Rooms</div>
                                <div class="value"><?= e($total_room); ?></div>
                                <div class="muted-small">Favorite: <?= e($favorite_room); ?></div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div class="muted-small">Top Room</div>
                            <div style="font-weight:700; color:var(--accent-purple);"><?= e($favorite_room); ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-glass float-up" id="card3">
                    <div class="kpi">
                        <div class="left">
                            <div class="icon" style="background: linear-gradient(135deg, rgba(62,232,154,0.05), rgba(94,232,255,0.03));">
                                <i class="fa-solid fa-chart-pie" style="color:var(--accent-green); font-size:20px;"></i>
                            </div>
                            <div>
                                <div class="label">Status Overview</div>
                                <div class="value"><?= e($status_counts['ongoing']); ?> / <?= e($status_counts['upcoming']); ?></div>
                                <div class="muted-small"><?= e($status_counts['finished']); ?> finished</div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div class="muted-small">Live</div>
                            <div style="font-weight:700; color:var(--accent-cyan);" id="liveClock">--:--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="equal-cards-row mb-3" style="margin-top:8px;">
                <div class="equal-card">
                    <div class="small-card card-glass card-body-canvas">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold">Booking Status</div>
                                <div class="muted-small">Realtime breakdown</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-6"><?= e($status_counts['upcoming']); ?> / <?= e($status_counts['ongoing']); ?></div>
                                <div class="muted-small"><?= e($status_counts['finished']); ?> finished</div>
                            </div>
                        </div>
                        <div class="canvas-wrap">
                            <canvas id="bookingStatusChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="equal-card">
                    <div class="small-card card-glass card-body-canvas">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold">Most Used Rooms</div>
                                <div class="muted-small">Top usage</div>
                            </div>
                            <div class="muted-small"><?= count($stats_room); ?> items</div>
                        </div>
                        <div class="canvas-wrap">
                            <canvas id="roomStatsChart" style="width:100%; height:260px;"></canvas>
                        </div>
                        <div class="list-scroll mt-3">
                            <ul class="list-unstyled m-0">
                                <?php foreach(json_decode($js_stats_room, true) as $r): ?>
                                    <li class="d-flex justify-content-between py-1 border-bottom border-opacity-25">
                                        <div class="text-white"><i class="fa-solid fa-door-open me-2"></i><?= e($r['nama_ruangan']); ?></div>
                                        <div class="fw-bold text-info"><?= e($r['total']); ?>x</div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="equal-card">
                    <div class="small-card card-glass card-body-canvas">
                        <div class="fw-bold mb-2">Monthly Trend (<?= date('Y'); ?>)
                            <button class="btn btn-sm btn-outline-light float-end mb-2" onclick="exportChartWithImageToExcel()"><i class="fa fa-file-excel"></i> Export to Excel</button>
                        </div>
                        <div class="canvas-wrap">
                            <canvas id="monthlyStatsChart" style="width:100%; height:260px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-glass">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div>
                        <h3 style="margin:0; font-size:1.05rem;">Booking List</h3>
                        <div class="muted-small">Data booking ruangan — tampil menyesuaikan filter</div>
                    </div>
                    <div class="muted-small">Rows: <span id="rowCount"><?= count($bookings); ?></span></div>
                </div>
                <div class="filters" style="margin-bottom:12px;">
                    <select id="statusFilter" class="form-select form-select-sm" style="width:160px;">
                        <option class="text-black" value="all">All Status</option>
                        <option class="text-black" value="upcoming">Upcoming</option>
                        <option class="text-black" value="ongoing">Ongoing</option>
                        <option class="text-black" value="finished">Finished</option>
                    </select>
                    <select id="roomFilter" class="form-select form-select-sm" style="width:220px;">
                        <option value="all" class="text-black">All Rooms</option>
                        <?php foreach($rooms as $r):
                            $name = is_array($r) ? ($r['nama_ruangan'] ?? '') : ($r->nama_ruangan ?? '');
                        ?>
                            <option value="<?= e($name); ?>" class="text-black"><?= e($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input id="dateFilter" class="form-control form-control-sm w-25 dateFilter" style="width:160px;" placeholder="Enter Date" />
                    <div style="display:flex; gap:8px;">
                        <button id="applyFilter" class="btn btn-sm btn-outline-light">Apply</button>
                        <button id="resetFilter" class="btn btn-sm btn-outline-light">Reset</button>
                        <button id="exportToExcel" class="btn btn-sm btn-outline-light" onclick="exportBookingTableToExcel()"><i class="fa fa-file-excel"></i> Export to Excel</button>
                    </div>
                </div>
                <div class="table-wrap">
                    <table id="bookingTable">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Room</th>
                                <th>Capacity</th>
                                <th>Facilities</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <!--<th style="width:120px">Action</th>-->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $i => $b):
                                $now = strtotime(date('Y-m-d H:i'));
                                $start = strtotime($b->date . ' ' . $b->start_time);
                                $end = strtotime($b->date . ' ' . $b->end_time);
                                $status = ($now < $start) ? 'upcoming' : (($now >= $start && $now <= $end) ? 'ongoing' : 'finished');
                            ?>
                                <tr data-status="<?= e($status); ?>" data-room="<?= e($b->nama_ruangan ?? ''); ?>" data-date="<?= e($b->date ?? ''); ?>" data-name="<?= e($b->name ?? ''); ?>">
                                    <td><?= $i+1; ?></td>
                                    <td class="fw-semibold"><?= e($b->nama_ruangan ?? ''); ?></td>
                                    <td><?= e($b->kapasitas ?? '-'); ?></td>
                                    <td><?= e($b->fasilitas ?? '-'); ?></td>
                                    <td><?= e($b->name ?? '-'); ?></td>
                                    <td><?= e(date('d M Y', strtotime($b->date ?? ''))); ?></td>
                                    <td><?= e(date('H:i', strtotime($b->start_time ?? ''))).' - '.e(date('H:i', strtotime($b->end_time ?? ''))); ?></td>
                                    <td>
                                        <?php if($status=='upcoming'): ?>
                                            <span class="badge-status badge-upcoming">Upcoming</span>
                                        <?php elseif($status=='ongoing'): ?>
                                            <span class="badge-status badge-ongoing">Ongoing</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-finished">Finished</span>
                                        <?php endif; ?>
                                    </td>
                                <!--<td>
                                        <a href="<?= e(base_url('dashboard/edit_booking/'.($b->id_booking ?? ''))); ?>" class="btn-action" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="<?= e(base_url('dashboard/delete_booking/'.($b->id_booking ?? ''))); ?>" class="btn-action" onclick="return confirm('Delete this booking?');" title="Delete" style="margin-left:6px; border-color:rgba(255,0,80,0.12);"><i class="fa-solid fa-trash" style="color:#ff8aa1"></i></a>
                                    </td>-->
                                </tr>
                            <?php endforeach; ?>
                            <?php if(count($bookings)===0): ?>
                                <tr><td colspan="9" style="text-align:center; padding:32px; color:var(--muted)">No bookings available</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script src="<?= base_url('assets/js/exceljs.min.js'); ?>"></script>
        <script src="<?= base_url('assets/js/FileSaver.min.js'); ?>"></script>
        <script src="<?= base_url('assets/js/xlsx.full.min.js'); ?>"></script>
        <script src="<?= base_url('assets/js/flatpickr.js');?>"></script>
        <script>
            flatpickr(".dateFilter", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "l, d F Y",
                disableMobile: "true"
            });
        
            function updateClock(){
                const el = document.getElementById('liveClock');
                const d = new Date();
                if(el) el.textContent = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
            }
            setInterval(updateClock, 1000);
            updateClock();
            document.querySelectorAll('.float-up').forEach((el, i) => {
                setTimeout(()=> el.classList.add('show'), 120 + (i*120));
            });
            const statusCounts = <?= $js_status_counts; ?>;
            const roomStats = <?= $js_stats_room; ?>;
            const monthlyStats = <?= $js_stats_month; ?>;
            function palette(i) {
                const c = ['rgba(94,232,255,0.95)','rgba(167,139,250,0.95)','rgba(94,232,154,0.95)','rgba(255,191,105,0.95)','rgba(255,140,140,0.95)'];
                return c[i % c.length];
            }
            (function(){
                const ctx = document.getElementById('bookingStatusChart');
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(statusCounts).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                        datasets: [{
                            data: Object.values(statusCounts),
                            backgroundColor: ['#ffc65bff','#3ee89a','#4484deff'],
                            borderColor: ['#0b1120','#0b1120','#0b1120'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: '#dff6f5' } },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const v = ctx.raw || 0;
                                        const p = total > 0 ? ((v / total) * 100).toFixed(1) : 0;
                                        return ctx.label + ': ' + v + ' (' + p + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            })();
            (function(){
                const ctx = document.getElementById('roomStatsChart');
                if (!ctx) return;
                const labels = roomStats.map(r => r.nama_ruangan || '—');
                const data = roomStats.map(r => +r.total || 0);
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Usage',
                            data: data,
                            backgroundColor: labels.map((_,i)=> palette(i)),
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { color: '#9aa4b2' }, grid: { color: 'rgba(255,255,255,0.02)' } },
                            x: { ticks: { color: '#9aa4b2' }, grid: { display: false } }
                        }
                    }
                });
            })();
            (function(){
                const ctx = document.getElementById('monthlyStatsChart');
                if (!ctx) return;
                const g = ctx.getContext('2d').createLinearGradient(0, 0, 0, 180);
                g.addColorStop(0, 'rgba(94,232,255,0.25)');
                g.addColorStop(1, 'rgba(167,139,250,0.02)');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                        datasets: [{
                            label: 'Bookings',
                            data: monthlyStats,
                            fill: true,
                            backgroundColor: g,
                            borderColor: 'rgba(94,232,255,0.95)',
                            tension: 0.35,
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { color: '#9aa4b2' }, grid: { color: 'rgba(255,255,255,0.02)' } },
                            x: { ticks: { color: '#9aa4b2' }, grid: { display: false } }
                        }
                    }
                });
            })();
            async function exportChartWithImageToExcel() {
                const chart = Chart.getChart("monthlyStatsChart");
                if (!chart) return alert("Chart not found");

                const labels = chart.data.labels;
                const dataset = chart.data.datasets[0];
                const data = dataset.data;

                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet("Monthly Trend");

                worksheet.addRow(["Month", dataset.label]);
                labels.forEach((label, index) => {
                    worksheet.addRow([label, data[index]]);
                });

                const canvas = document.getElementById("monthlyStatsChart");
                const canvasWithBlackBg = getCanvasWithBlackBackground(canvas);
                const imageBase64 = canvasWithBlackBg.toDataURL("image/png");

                const imageId = workbook.addImage({
                    base64: imageBase64,
                    extension: 'png',
                });

                worksheet.addImage(imageId, {
                    tl: { col: 4, row: 1 },
                    ext: { width: 500, height: 300 }
                });

                const buffer = await workbook.xlsx.writeBuffer();
                saveAs(new Blob([buffer]), `Monthly_Trend_${new Date().getFullYear()}.xlsx`);
            }

            function getCanvasWithBlackBackground(originalCanvas) {
                const tempCanvas = document.createElement("canvas");
                const ctx = tempCanvas.getContext("2d");

                tempCanvas.width = originalCanvas.width;
                tempCanvas.height = originalCanvas.height;

                ctx.fillStyle = "black";
                ctx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);

                ctx.drawImage(originalCanvas, 0, 0);

                return tempCanvas;
            }
            function exportBookingTableToExcel() {
                const table = document.getElementById("bookingTable");

                const workbook = XLSX.utils.book_new();
                const worksheet = XLSX.utils.table_to_sheet(table);

                XLSX.utils.book_append_sheet(workbook, worksheet, "Booking Data");

                XLSX.writeFile(workbook, `Booking_List_${new Date().getFullYear()}.xlsx`);
            }

            const bookingTable = document.getElementById('bookingTable');
            const statusFilter = document.getElementById('statusFilter');
            const roomFilter = document.getElementById('roomFilter');
            const dateFilter = document.getElementById('dateFilter');
            const applyFilter = document.getElementById('applyFilter');
            const resetFilter = document.getElementById('resetFilter');
            const globalSearch = document.getElementById('globalSearch');
            const clearSearch = document.getElementById('clearSearch');
            const rowCount = document.getElementById('rowCount');
            function filterTable(){
                const s = statusFilter.value;
                const r = roomFilter.value;
                const d = dateFilter.value.trim();
                const q = (globalSearch.value || '').trim().toLowerCase();
                const rows = bookingTable.querySelectorAll('tbody tr');
                let visible = 0;
                rows.forEach(row => {
                    if(!row.dataset.status) { row.style.display='none'; return; }
                    const rs = row.dataset.status;
                    const rr = row.dataset.room || '';
                    const rd = row.dataset.date || '';
                    const rn = row.dataset.name || '';
                    const text = (row.innerText + ' ' + rr + ' ' + rn + ' ' + rd).toLowerCase();
                    const okStatus = (s === 'all' || rs === s);
                    const okRoom = (r === 'all' || rr === r);
                    const okDate = (d === '' || rd === d);
                    const okQuery = (q === '' || text.indexOf(q) !== -1);
                    if(okStatus && okRoom && okDate && okQuery){
                        row.style.display = '';
                        visible++;
                    } else row.style.display = 'none';
                });
                rowCount.textContent = visible;
                const up = document.querySelectorAll('tbody tr[data-status="upcoming"]:not([style*="display: none"])').length;
                const on = document.querySelectorAll('tbody tr[data-status="ongoing"]:not([style*="display: none"])').length;
                const fin = document.querySelectorAll('tbody tr[data-status="finished"]:not([style*="display: none"])').length;
                const sEl = document.getElementById('summaryText');
                if(sEl) sEl.textContent = `${up} upcoming · ${on} ongoing · ${fin} finished`;
            }
            applyFilter.addEventListener('click', filterTable);
            resetFilter.addEventListener('click', ()=> {
                statusFilter.value='all'; roomFilter.value='all'; dateFilter.value=''; globalSearch.value=''; filterTable();
            });
            statusFilter.addEventListener('change', filterTable);
            roomFilter.addEventListener('change', filterTable);
            globalSearch.addEventListener('input', filterTable);
            clearSearch.addEventListener('click', ()=> { globalSearch.value=''; filterTable(); });
            function downloadCSV(filename, rows){
                const process = r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',');
                const csv = rows.map(process).join('\n');
                const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
            
            document.addEventListener('keydown', e => {
                if(e.key === '/') { e.preventDefault(); globalSearch.focus(); }
            });
            filterTable();
        </script>
    </body>
</html>
