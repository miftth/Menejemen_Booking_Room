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
    $years = array_unique(array_map(function($b){ return date('Y', strtotime($b->date ?? '')); }, $bookings));
    $years = array_unique(array_merge($years, [date('Y')]));
    rsort($years);
?>
    <script src="<?= base_url('assets/js/chart.umd.min.js');?>"></script>
    <style>
        :root {
            --bg-primary: #000000;
            --bg-secondary: #000000;
            --glass: rgba(166, 166, 166, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --muted: #b0b0b0;
            --accent-cyan: #00d4ff;
            --accent-purple: #9c27b0;
            --accent-green: #4caf50;
            --accent-orange: #ff9800;
            --accent-red: #f44336;
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --radius: 16px;
            --transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        html, body {
            margin: 0;
            padding: 0;
            color: var(--text-primary);
            background: var(--bg-primary);
            min-height: 100vh;
            transition: var(--transition);
        }

        .wrap {
            max-width: 1300px;
            margin: 30px auto;
            padding: 32px;
            border-radius: var(--radius);
            background: var(--glass);
            border: 1px solid var(--glass-border);
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(20px) saturate(180%);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .wrap::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-purple), var(--accent-green));
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand .logo {
            width: 64px;
            height: 64px;
            border-radius: var(--radius);
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 900;
            font-size: 1.2rem;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .brand h1 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--accent-purple), var(--accent-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand p {
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .card-glass {
            padding: 24px;
            border-radius: var(--radius);
            background: var(--glass);
            border: 1px solid var(--glass-border);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }

        .small-card {
            padding: 20px;
            border-radius: var(--radius);
            background: var(--glass);
            border: 1px solid var(--glass-border);
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(10px);
        }

        .table-wrap {
            margin-top: 20px;
            border-radius: var(--radius);
            overflow: hidden;
            border: 1px solid var(--glass-border);
            box-shadow: var(--card-shadow);
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: var(--glass);
            backdrop-filter: blur(10px);
        }

        thead th {
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            color: var(--text-primary);
            font-weight: 700;
            padding: 16px 20px;
            text-align: left;
            font-size: 0.95rem;
            border-bottom: 2px solid var(--glass-border);
        }

        tbody td {
            padding: 16px 20px;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: var(--text-light);
        }

        .badge-status {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .badge-upcoming {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #212529;
            box-shadow: 0 4px 8px rgba(255, 215, 0, 0.3);
        }

        .badge-ongoing {
            background: linear-gradient(135deg, #00ff88, #00cc66);
            color: #fff;
            box-shadow: 0 4px 8px rgba(0, 255, 136, 0.3);
        }

        .badge-finished {
            background: linear-gradient(135deg, #4dabf7, #228be6);
            color: #fff;
            box-shadow: 0 4px 8px rgba(74, 171, 247, 0.3);
        }

        .filters {
            display: flex;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filters .form-control,
        .filters .form-select {
            background: var(--glass);
            color: var(--text-primary);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            padding: 10px 12px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
            backdrop-filter: blur(5px);
            transition: var(--transition);
        }

        .filters .form-control:focus,
        .filters .form-select:focus {
            border-color: var(--accent-cyan);
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
        }

        .search-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--glass);
            border-radius: 12px;
            padding: 10px 16px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }

        .search-wrap input {
            background: transparent;
            border: 0;
            outline: none;
            color: var(--text-primary);
            width: 320px;
            font-size: 0.95rem;
        }

        .muted-small {
            color: var(--muted);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .float-up {
            transform: translateY(20px);
            opacity: 0;
        }

        .float-up.show {
            transform: translateY(0);
            opacity: 1;
            transition: all 0.8s cubic-bezier(.2,.9,.3,1);
        }

        .canvas-wrap {
            flex: 0 0 auto;
        }

        .list-scroll {
            max-height: 140px;
            overflow: auto;
        }

        .dateFilter::placeholder {
            color: var(--muted);
            opacity: 1;
        }

        #bookingStatusChart {
            width: 100%;
            height: 220px;
            margin: 0 auto;
        }

        #toggleDarkMode {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 1px solid var(--glass-border);
            background: var(--glass);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1.3rem;
            backdrop-filter: blur(10px);
        }

        #toggleDarkMode:hover {
            transform: scale(1.1);
            background: rgba(255,255,255,0.95);
            color: #000;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        @media (max-width: 1200px) {
            .wrap {
                margin: 20px;
                padding: 24px;
            }

            .header-actions {
                flex-direction: column;
                align-items: flex-start;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .search-wrap input {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .wrap {
                margin: 10px;
                padding: 16px;
            }

            header {
                flex-direction: column;
                text-align: center;
            }

            .brand h1 {
                font-size: 1.2rem;
            }

            .card-glass {
                padding: 16px;
            }

            .small-card {
                padding: 16px;
            }

            table {
                font-size: 0.8rem;
            }

            thead th,
            tbody td {
                padding: 8px 12px;
            }
        }

        html.light-mode {
            --bg-primary: linear-gradient(135deg, #929292ff 0%, #8f8f8fff 100%);
            --bg-secondary: #ffffff;
            --glass: rgba(220, 220, 220, 0.95);
            --glass-border: rgba(0, 0, 0, 0.1);
            --muted: #6c757d;
            --accent-cyan: #00bcd4;
            --accent-purple: #9c27b0;
            --accent-green: #4caf50;
            --accent-orange: #ff9800;
            --accent-red: #f44336;
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            --text-primary: #212529;
            --text-secondary: #6c757d;
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        html.light-mode .wrap {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            box-shadow: var(--card-shadow);
        }

        html.light-mode .card-glass,
        html.light-mode .small-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            box-shadow: var(--card-shadow);
        }

        html.light-mode .search-wrap,
        html.light-mode .filters .form-control,
        html.light-mode .filters .form-select {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
        }

        html.light-mode .table-wrap table {
            background: var(--glass);
        }

        html.light-mode thead th {
            background: linear-gradient(135deg, rgba(0,0,0,0.05), rgba(0,0,0,0.02));
            color: var(--text-primary);
        }

        html.light-mode tbody td {
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        html.light-mode .badge-status {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        html.light-mode #toggleDarkMode {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
        }

        html.light-mode #toggleDarkMode:hover {
            background: rgba(0,0,0,0.05);
            color: #000;
        }

        html.light-mode .list-group-item {
            background: var(--glass);
            border: 1px solid var(--glass-border);
        }

        html.light-mode .list-group-item:nth-child(odd) {
            background: rgba(0,0,0,0.02);
        }

        .room-list-item {
            color: var(--text-primary);
        }

        .room-name {
            color: var(--text-primary);
        }

        .room-count {
            color: var(--accent-cyan);
        }

        html.light-mode .btn-outline-dark {
            color: #333;
            border-color: #333;
        }

        html.light-mode .btn-outline-dark:hover {
            background-color: #333;
            color: #fff;
        }

        html:not(.light-mode) .btn-outline-dark {
            color: #fff;
            border-color: #fff;
        }

        html:not(.light-mode) .btn-outline-dark:hover {
            background-color: #fff;
            color: #000;
        }

        html.light-mode tbody td {
            color: #212529;
            border-bottom: 1px solid #dee2e6;
            font-weight: 500;
        }

        html.light-mode thead th {
            color: #212529;
            border-bottom: 2px solid #adb5bd;
            font-weight: 700;
        }

        html.light-mode tbody tr:hover {
            background: rgba(0,123,255,0.1);
        }

        html.light-mode tbody tr:nth-child(even) {
            background: rgba(0,0,0,0.02);
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .modal-content {
            border-radius: var(--radius);
            border: none;
            box-shadow: var(--card-shadow);
        }

        .list-group-item {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(5px);
        }

        .list-group-item:nth-child(odd) {
            background: rgba(255,255,255,0.05);
        }
    </style>
        <div class="wrap">
            <header>
                <div class="brand">
                    <div class="logo"><i class="fa-solid fa-calendar-check"></i></div>
                    <div>
                        <h1>Booking Management</h1>
                        <p><?= date('Y'); ?></p>
                    </div>
                </div>
                <div class="header-actions d-none d-lg-flex">
                    <div class="search-wrap" title="Search (press /)">
                        <i class="fa-solid fa-magnifying-glass" style="color:var(--muted)"></i>
                        <input id="globalSearch" placeholder="Cari ruangan, nama, fasilitas, tanggal..." />
                        <button id="clearSearch" class="btn btn-sm btn-outline-dark" title="Clear"><i class="fa-solid fa-xmark" style="color:var(--muted)"></i></button>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <div style="padding:8px 12px; border-radius:10px; border:1px solid rgba(255,255,255,0.03); background:transparent;">
                            <div class="muted-small">Summary</div>
                            <div style="font-weight:800; font-size:0.95rem;" id="summaryText"><?= e($status_counts['upcoming']); ?> upcoming · <?= e($status_counts['ongoing']); ?> ongoing · <?= e($status_counts['finished']); ?> finished</div>
                        </div>
                    </div>
                </div>
                <button id="toggleDarkMode" class="btn btn-lg btn-outline-dark ms-3" title="Toggle dark mode"><i class="fa-solid fa-moon" style="color:var(--muted)"></i></button>
            </header>
            <div class="row my-4 g-3">
                <div class="col-md-4">
                    <div class="card-glass p-3 d-flex align-items-center gap-3 float-up">
                        <div style="background: linear-gradient(135deg, rgba(94,232,255,0.08), rgba(167,139,250,0.06));">
                            <i class="fa-solid fa-list-check bg-transparent" style="color:var(--accent-cyan); font-size:20px;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold ">Total Bookings</div>
                            <div class="fs-4 fw-bold"><?= e($total_booking); ?></div>
                            <div class="muted-small">Favorite: <?= e($favorite_room); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-glass p-3 d-flex justify-content-between align-items-center float-up">
                        <div class="d-flex align-items-center gap-3">
                            <div style="background: linear-gradient(135deg, rgba(255,191,105,0.05), rgba(255,140,140,0.03));">
                              <i class="fa-solid fa-door-open bg-transparent" style="color:#ffd19a; font-size:20px;"></i>
                            </div>
                            <div>
                                <div class="fw-bold ">Total Rooms</div>
                                <div class="fs-4 fw-bold"><?= e($total_room); ?></div>
                                <div class="muted-small">Favorite: <?= e($favorite_room); ?></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="muted-small">Top Room</div>
                            <div class="fw-bold" style="color:#F59E0B;"><?= e($favorite_room); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-glass p-3 d-flex justify-content-between align-items-center float-up">
                        <div class="d-flex align-items-center gap-3">
                            <div style="background: linear-gradient(135deg, rgba(62,232,154,0.05), rgba(94,232,255,0.03));">
                                <i class="fa-solid fa-chart-pie bg-transparent" style="color:var(--accent-green); font-size:20px;"></i>
                            </div>
                            <div>
                                <div class="fw-bold ">Status Overview</div>
                                <div class="fs-4 fw-bold"><?= e($status_counts['upcoming']); ?> / <?= e($status_counts['ongoing']); ?></div>
                                <div class="muted-small"><?= e($status_counts['finished']); ?> finished</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="muted-small">Live</div>
                            <div class="fw-bold" id="liveClock" style="color:var(--accent-cyan);">--:--</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row my-4 mb-0">
                <div class="col-md-4 mb-4">
                    <div class="small-card card-glass h-100">
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
                <div class="col-md-4 mb-4">
                    <div class="small-card card-glass h-100">
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
                                    <li class="d-flex justify-content-between py-1 border-bottom border-opacity-25 room-list-item">
                                        <div class="room-name"><i class="fa-solid fa-door-open me-2"></i><?= e($r['nama_ruangan']); ?></div>
                                        <div class="fw-bold room-count"><?= e($r['total']); ?>x</div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="small-card card-glass h-100">
                        <div class="fw-bold mb-2">Monthly Trend (<?= date('Y'); ?>)
                            <button class="btn btn-sm btn-outline-dark float-end mb-2" onclick="exportChartWithImageToExcel()"><i class="fa fa-file-excel"></i> Export to Excel</button>
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
                    <select id="roomFilter" class="form-select form-select-sm" style="width:160px;">
                        <option value="all" class="text-black">All Rooms</option>
                        <?php foreach($rooms as $r):
                            $name = is_array($r) ? ($r['nama_ruangan'] ?? '') : ($r->nama_ruangan ?? '');
                        ?>
                            <option value="<?= e($name); ?>" class="text-black"><?= e($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="yearFilter" class="form-select form-select-sm" style="width:120px;">
                        <option value="all" class="text-black">All Years</option>
                        <?php foreach($years as $year): ?>
                            <option value="<?= e($year); ?>" class="text-black"><?= e($year); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="mouthFilter" class="form-select form-select-sm" style="width:160px;">
                        <option value="all" class="text-black">All Months</option>
                        <option value="january" class="text-black">January</option>
                        <option value="february" class="text-black">February</option>
                        <option value="march" class="text-black">March</option>
                        <option value="april" class="text-black">April</option>
                        <option value="may" class="text-black">May</option>
                        <option value="june" class="text-black">June</option>
                        <option value="july" class="text-black">July</option>
                        <option value="august" class="text-black">August</option>
                        <option value="september" class="text-black">September</option>
                        <option value="october" class="text-black">October</option>
                        <option value="november" class="text-black">November</option>
                        <option value="december" class="text-black">December</option>
                    </select>
                    <input id="dateFilter" class="form-control form-control-sm w-15 dateFilter" placeholder="Enter Date" />
                    <div style="display:flex; gap:8px;">
                        <button id="applyFilter" class="btn btn-sm btn-outline-dark">Apply</button>
                        <button id="resetFilter" class="btn btn-sm btn-outline-dark">Reset</button>
                        <button id="exportToExcel" class="btn btn-sm btn-outline-dark" onclick="exportBookingTableToExcel()"><i class="fa fa-file-excel"></i> Export to Excel</button>
                    </div>
                </div>
                <div class="table-wrap" style="overflow-x:auto;">
                    <table id="bookingTable" class="table-responsive">
                        <thead>
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Room</th>
                                <th>Capacity</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th style="width:170px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $i => $b):
                                $now = strtotime(date('Y-m-d H:i'));
                                $start = strtotime($b->date . ' ' . $b->start_time);
                                $end = strtotime($b->date . ' ' . $b->end_time);
                                $status = ($now < $start) ? 'upcoming' : (($now >= $start && $now <= $end) ? 'ongoing' : 'finished');
                            ?>
                                <tr data-status="<?= e($status); ?>" data-room="<?= e($b->nama_ruangan ?? ''); ?>" data-date="<?= e(date('d F Y', strtotime($b->date ?? ''))); ?>" data-name="<?= e($b->name ?? ''); ?>">
                                    <td><?= $i+1; ?></td>
                                    <td class="fw-semibold"><?= e($b->nama_ruangan ?? ''); ?></td>
                                    <td><?= e($b->kapasitas ?? '-'); ?></td>
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
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#detailModal<?= e($b->id_booking ?? ''); ?>" title="Detail"><i class="fa-solid fa-eye"></i></button>
                                        <a href="<?= e(base_url('dashboard/edit_booking/'.($b->id_booking ?? ''))); ?>" class="btn btn-sm btn-success me-1" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="<?= e(base_url('dashboard/delete_booking/'.($b->id_booking ?? ''))); ?>" class="btn btn-sm btn-danger"  title="Delete"><i class="fa-solid fa-trash"></i></a>
                                    </td>
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
        <?php foreach($bookings as $b): ?>
            <div class="modal fade" id="detailModal<?= e($b->id_booking ?? ''); ?>" tabindex="-1" aria-labelledby="detailModalLabel<?= e($b->id_booking ?? ''); ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content shadow-lg border-0">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="detailModalLabel<?= e($b->id_booking ?? ''); ?>">
                                <i class="fa-solid fa-circle-info me-2"></i> Full Booking Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <ul class="list-group">
                                <li class="list-group-item"><strong>Name:</strong> <?= e($b->name ?? '-'); ?></li>
                                <li class="list-group-item"><strong>Email:</strong> <?= e($b->email ?? '-'); ?></li>
                                <li class="list-group-item"><strong>Room:</strong> <?= e($b->nama_ruangan ?? '-'); ?></li>
                                <li class="list-group-item"><strong>Facilities:</strong> <?= e($b->fasilitas ?? '-'); ?></li>
                                <li class="list-group-item"><strong>Date:</strong> <?= e(strftime('%d %B %Y', strtotime($b->date ?? ''))); ?></li>
                                <li class="list-group-item"><strong>Time:</strong> <?= e(date('H:i', strtotime($b->start_time ?? ''))); ?> - <?= e(date('H:i', strtotime($b->end_time ?? ''))); ?></li>
                                <li class="list-group-item"><strong>Capacity:</strong> <?= e($b->kapasitas ?? '-'); ?> people</li>
                                <li class="list-group-item"><strong>Room IP Address:</strong> <?= e($b->ip_address ?? '-'); ?></li>
                            </ul>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fa-solid fa-xmark me-1"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <script src="<?= base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>
        <script src="<?= base_url('assets/js/exceljs.min.js'); ?>"></script>
        <script src="<?= base_url('assets/js/FileSaver.min.js'); ?>"></script>
        <script src="<?= base_url('assets/js/xlsx.full.min.js'); ?>"></script>
        <script src="<?= base_url('assets/js/flatpickr.js'); ?>"></script>
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
            (function() {
                const ctx = document.getElementById('bookingStatusChart');
                if (!ctx) return;

                const makeGradient = (color1, color2) => {
                    const canvas = document.createElement('canvas');
                    const ctx2 = canvas.getContext('2d');
                    const gradient = ctx2.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, color1);
                    gradient.addColorStop(1, color2);
                    return gradient;
                };

                const gradientColors = [
                    makeGradient('#ffd700', '#ffed4e'), 
                    makeGradient('#00ff88', '#00cc66'),
                    makeGradient('#4dabf7', '#228be6') 
                ];

                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(statusCounts).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                        datasets: [{
                            data: Object.values(statusCounts),
                            backgroundColor: gradientColors,
                            borderColor: ['#ffd700', '#00ff88', '#4dabf7'],
                            borderWidth: 2,
                            hoverBorderColor: '#fff',
                            hoverBorderWidth: 3,
                            offset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: 10
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#fff',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    label: function(ctx) {
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const value = ctx.raw || 0;
                                        const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${ctx.label}: ${value} (${percent}%)`;
                                    }
                                }
                            }
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true,
                            duration: 2000,
                            easing: 'easeOutBounce'
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
                            backgroundColor: labels.map((_,i) => {
                                const colors = [
                                    'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                                    'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                                    'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                                    'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                                    'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
                                ];
                                const canvas = document.createElement('canvas');
                                const ctx = canvas.getContext('2d');
                                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                                const color = colors[i % colors.length];
                                if (color.includes('#667eea')) {
                                    gradient.addColorStop(0, '#667eea');
                                    gradient.addColorStop(1, '#764ba2');
                                } else if (color.includes('#f093fb')) {
                                    gradient.addColorStop(0, '#f093fb');
                                    gradient.addColorStop(1, '#f5576c');
                                } else if (color.includes('#4facfe')) {
                                    gradient.addColorStop(0, '#4facfe');
                                    gradient.addColorStop(1, '#00f2fe');
                                } else if (color.includes('#43e97b')) {
                                    gradient.addColorStop(0, '#43e97b');
                                    gradient.addColorStop(1, '#38f9d7');
                                } else {
                                    gradient.addColorStop(0, '#fa709a');
                                    gradient.addColorStop(1, '#fee140');
                                }
                                return gradient;
                            }),
                            borderRadius: 12,
                            borderSkipped: false,
                            hoverBackgroundColor: labels.map((_,i) => {
                                const colors = [
                                    'linear-gradient(135deg, #764ba2 0%, #667eea 100%)',
                                    'linear-gradient(135deg, #f5576c 0%, #f093fb 100%)',
                                    'linear-gradient(135deg, #00f2fe 0%, #4facfe 100%)',
                                    'linear-gradient(135deg, #38f9d7 0%, #43e97b 100%)',
                                    'linear-gradient(135deg, #fee140 0%, #fa709a 100%)'
                                ];
                                const canvas = document.createElement('canvas');
                                const ctx = canvas.getContext('2d');
                                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                                const color = colors[i % colors.length];
                                if (color.includes('#764ba2')) {
                                    gradient.addColorStop(0, '#764ba2');
                                    gradient.addColorStop(1, '#667eea');
                                } else if (color.includes('#f5576c')) {
                                    gradient.addColorStop(0, '#f5576c');
                                    gradient.addColorStop(1, '#f093fb');
                                } else if (color.includes('#00f2fe')) {
                                    gradient.addColorStop(0, '#00f2fe');
                                    gradient.addColorStop(1, '#4facfe');
                                } else if (color.includes('#38f9d7')) {
                                    gradient.addColorStop(0, '#38f9d7');
                                    gradient.addColorStop(1, '#43e97b');
                                } else {
                                    gradient.addColorStop(0, '#fee140');
                                    gradient.addColorStop(1, '#fa709a');
                                }
                                return gradient;
                            })
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
                g.addColorStop(1, 'rgba(167, 139, 250, 0.24)');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['January','February','March','April','May','June','July','August','September','October','November','December'],
                        datasets: [{
                            label: 'Bookings',
                            data: monthlyStats,
                            fill: true,
                            backgroundColor: g,
                            borderColor: 'rgba(44, 212, 241, 0.95)',
                            tension: 0.40,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.02)' } },
                            x: { ticks: { color: '#fff' }, grid: { display: false } }
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
            const mouthFilter = document.getElementById('mouthFilter');
            const yearFilter = document.getElementById('yearFilter');
            const dateFilter = document.getElementById('dateFilter');
            const applyFilter = document.getElementById('applyFilter');
            const resetFilter = document.getElementById('resetFilter');
            const globalSearch = document.getElementById('globalSearch');
            const clearSearch = document.getElementById('clearSearch');
            const rowCount = document.getElementById('rowCount');
            function filterTable(){
                const s = statusFilter.value;
                const r = roomFilter.value;
                const m = mouthFilter.value;
                const y = yearFilter.value;
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
                    const month = rd.split(' ')[1] ? rd.split(' ')[1].toLowerCase() : '';
                    const year = rd.split(' ')[2] ? rd.split(' ')[2] : '';
                    const okStatus = (s === 'all' || rs === s);
                    const okRoom = (r === 'all' || rr === r);
                    const okMonth = (m === 'all' || month === m);
                    const okYear = (y === 'all' || year === y);
                    const okDate = (d === '' || rd === d);
                    const okQuery = (q === '' || text.indexOf(q) !== -1);
                    if(okStatus && okRoom && okMonth && okYear && okDate && okQuery){
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
                statusFilter.value='all'; roomFilter.value='all'; mouthFilter.value='all'; yearFilter.value='all'; dateFilter.value=''; globalSearch.value=''; filterTable();
            });
            statusFilter.addEventListener('change', filterTable);
            roomFilter.addEventListener('change', filterTable);
            mouthFilter.addEventListener('change', filterTable);
            yearFilter.addEventListener('change', filterTable);
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

            const toggleButton = document.getElementById('toggleDarkMode');
            const htmlElement = document.documentElement;
            const iconElement = toggleButton.querySelector('i');

            function updateChartColors(isLight) {
                const textColor = isLight ? '#333' : '#fff';
                const tooltipBg = isLight ? 'rgba(0,0,0,0.8)' : 'rgba(255,255,255,0.8)';
                const tooltipText = isLight ? '#fff' : '#000';
                const pieChart = Chart.getChart('bookingStatusChart');
                if (pieChart) {
                    pieChart.options.plugins.legend.labels.color = textColor;
                    pieChart.options.plugins.tooltip.backgroundColor = tooltipBg;
                    pieChart.options.plugins.tooltip.titleColor = tooltipText;
                    pieChart.options.plugins.tooltip.bodyColor = tooltipText;
                    pieChart.update();
                }
                const barChart = Chart.getChart('roomStatsChart');
                if (barChart) {
                    barChart.options.plugins.tooltip.backgroundColor = tooltipBg;
                    barChart.options.plugins.tooltip.titleColor = tooltipText;
                    barChart.options.plugins.tooltip.bodyColor = tooltipText;
                    barChart.options.scales.y.ticks.color = textColor;
                    barChart.options.scales.x.ticks.color = textColor;
                    barChart.update();
                }
                const lineChart = Chart.getChart('monthlyStatsChart');
                if (lineChart) {
                    lineChart.options.plugins.tooltip.backgroundColor = tooltipBg;
                    lineChart.options.plugins.tooltip.titleColor = tooltipText;
                    lineChart.options.plugins.tooltip.bodyColor = tooltipText;
                    lineChart.options.scales.y.ticks.color = textColor;
                    lineChart.options.scales.x.ticks.color = textColor;
                    lineChart.update();
                }
            }

            function applyMode(isLight) {
                if (isLight) {
                    htmlElement.classList.add('light-mode');
                    iconElement.className = 'fa-solid fa-sun';
                    toggleButton.title = 'Toggle dark mode';
                } else {
                    htmlElement.classList.remove('light-mode');
                    iconElement.className = 'fa-solid fa-moon';
                    toggleButton.title = 'Toggle light mode';
                }
                localStorage.setItem('theme', isLight ? 'light' : 'dark');
                updateChartColors(isLight);
            }

            const savedTheme = localStorage.getItem('theme');
            const isLightMode = savedTheme === 'light';
            applyMode(isLightMode);

            toggleButton.addEventListener('click', () => {
                const currentIsLight = htmlElement.classList.contains('light-mode');
                applyMode(!currentIsLight);
            });
        </script>
    </body>
</html>
