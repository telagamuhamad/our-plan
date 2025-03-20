<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Dibatalkan</title>
</head>
<body>
    <h2>❌ Jadwal Dibatalkan</h2>
    <p>Hai {{ $user_name }},</p>
    <p>Jadwal ketemu kita yang ini dibatalkan yaa☹️</p>

    <ul>
        <li><strong>📍 Lokasi:</strong> {{ $meeting->location }}</li>
        <li><strong>🗓️ Tanggal:</strong> {{ $meeting->formatted_start_date }} - {{ $meeting->formatted_end_date }}</li>
        <li><strong>📝 Catatan:</strong> {{ $meeting->note ?? 'Tidak ada catatan' }}</li>
    </ul>

    <p>Jika ini kesalahan atau perlu dijadwalkan ulang, silakan buat jadwal baru di Planner😊.</p>

    <p>Salam, <br><strong>Planner Team</strong></p>
</body>
</html>
