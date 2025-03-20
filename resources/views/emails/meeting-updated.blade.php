<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Ketemu diperbarui!</title>
</head>
<body>
    <h2>✏️ Jadwal Ketemu diperbarui!</h2>
    <p>Hai {{ $user_name }},</p>
    <p>Jadwal ketemu kita yang ini habis di update nih! Ini detailnya yaa😊</p>

    <ul>
        <li><strong>📍 Lokasi:</strong> {{ $meeting->location }}</li>
        <li><strong>🗓️ Tanggal:</strong> {{ $meeting->formatted_start_date }} - {{ $meeting->formatted_end_date }}</li>
        <li><strong>📝 Catatan:</strong> {{ $meeting->note ?? 'Tidak ada catatan' }}</li>
    </ul>

    <p>Pastikan untuk mengecek kembali apakah ada perubahan yang perlu kamu lakukan.</p>

    <p>Salam, <br><strong>Planner Team</strong></p>
</body>
</html>
