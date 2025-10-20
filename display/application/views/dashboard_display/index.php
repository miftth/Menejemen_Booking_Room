<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Display</title>
    <link href="<?= base_url('assets/css/css2.css') ?>" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: white;
            background-image: url('<?= base_url('assets/image/image.png')?>');
            background-size: cover;
            background-position: center;
            height: 90vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
            position: relative;
        }
        /*
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.65);
            z-index: -1;
        }*/

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid rgba(255, 255, 255, 0.43);
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 40px;
            font-weight: 600;
            line-height: 1.4;
        }
        .time { text-align: right; }
        .time .clock { font-size: 80px; font-weight: 700; margin-bottom: -30px; }
        .time .date { font-size: 25px; color: #ccc; margin-top: 5px; }

        .content { flex: 1; display: flex; margin-top: 25px; gap: 20px; height: 500px;}
        .left { flex: 2; display: flex; flex-direction: column; gap: 15px; overflow-y: scroll; scrollbar-width: none; }
        .right { flex: 1; border-left: 1px solid  rgba(255, 255, 255, 0.43); padding-left: 20px;  overflow-y: scroll; scrollbar-width: none; }

        .meetings { display: flex; flex-direction: column; gap: 20px; }
        .meeting { padding-bottom: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.64); }
        .meeting time { display: block; font-size: 18px; color: #d2d2d2ff; }
        .meeting .title { font-size: 20px; font-weight: 500; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php foreach($data_ruangan as $room){ ?><?= htmlspecialchars($room['nama_ruangan']); ?><?php } ?> 🌍<br>
            <div style="font-size:20px;font-weight:300;">IP Room : <?php if($_SERVER['REMOTE_ADDR'] == '::1'){ ?> 127.0.0.1 </div><?php }else{ ?> <?=$_SERVER['REMOTE_ADDR']?><?php } ?>
        </h1>
        <div class="time">
            <div class="clock" id="clock"></div>
            <div class="date" id="date"></div>
        </div>
    </div>
    
    <div class="content"> 
        <div class="left">
            <strong style="font-size:30px; display:block;">Today's Meetings</strong>
            <div class="meetings">
                <?php foreach($data_booking_room_today as $booking): ?>
                <div class="meeting">
                    <time><?= strftime('%d %B %Y', strtotime($booking->date)) ?> • <?= strftime('%H:%M', strtotime($booking->start_time)) ?> – <?= strftime('%H:%M', strtotime($booking->end_time)) ?></time>
                    <div class="title">Meeting in Room <?= htmlspecialchars($booking->nama_ruangan);?></div>
                    <div class="title">on behalf of <?= htmlspecialchars($booking->name);?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="right">
            <strong style="font-size:30px; display:block; margin-bottom: 15px;">Next Day Meeting</strong>
            <div class="meetings">
                <?php foreach($data_booking_room_nextday as $booking): ?>
                <div class="meeting">
                    <time><?= strftime('%d %B %Y', strtotime($booking->date)) ?> • <?= strftime('%H:%M', strtotime($booking->start_time)) ?> – <?= strftime('%H:%M', strtotime($booking->end_time)) ?></time>
                    <div class="title">Meeting in Room <?= htmlspecialchars($booking->nama_ruangan);?></div>
                    <div class="title">on behalf of <?= htmlspecialchars($booking->name);?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        /*
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');  // format 24 jam
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;

            const options = { weekday: 'long', year: 'numeric', month: '2-digit', day: '2-digit' };
            document.getElementById('date').textContent = now.toLocaleDateString('en-EN', options);
        }*/
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0'); 
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
            const options = { weekday: 'long', year: 'numeric', month: '2-digit', day: '2-digit' };
            document.getElementById('date').textContent = now.toLocaleDateString('en-EN', options);
        } 
        /*
        setInterval(function(){
            window.location.reload();
        }, 1000);
        */
        setInterval(updateClock, 1000);
        updateClock();

        
        /*
        const interval = 5000; // refresh tiap 5 detik
        const idRuangan = 2001;
        const url = `http://localhost/pkl/menegement_booking_room_V1/display/?id_ruangan=${idRuangan}`;

        setInterval(() => {
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("HTTP error " + response.status);
                    }
                    return response.text(); // atau .json() jika api mengembalikan JSON
                })
                .then(data => {
                    document.getElementById("jadwal").innerHTML = data;
                })
                .catch(error => {
                    console.error("Gagal mengambil data jadwal:", error);
                    document.getElementById("jadwal").innerText = "Gagal memuat data.";
                });
        }, interval);
        */
        
    </script>
</body>
</html>
