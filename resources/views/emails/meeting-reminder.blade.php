<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Jadwal Ketemu</title>
</head>
<body>
    <h2>🔔 Pengingat Jadwal Ketemu</h2>
    <p>Hai {{ $user_name }},</p>
    <p>Jangan lupa, kita sebentar lagi akan ketemu loh! Ini detailnya yaa😊</p>

    <ul>
        <li><strong>📍 Lokasi:</strong> {{ $meeting->location }}</li>
        <li><strong>🗓️ Tanggal:</strong> {{ $meeting->formatted_start_date }} - {{ $meeting->formatted_end_date }}</li>
    </ul>

    <p>Pastikan semua persiapan sudah dilakukan!</p>
    <p>Ini list persiapan yang harus dilakukan:</p>

    <ul>
        <li><strong>🚅 Kendaraan Berangkat:</strong> {{ $meeting->is_departure_transport_ready ? 'Aman😁' : 'Belum nih☹️' }}</li>
        <li><strong>🚅 Kendaraan Pulang:</strong> {{ $meeting->is_return_transport_ready ? 'Aman😁' : 'Belum nih☹️' }}</li>
        <li><strong>🏠 Tempat Istirahat:</strong> {{ $meeting->is_rest_place_ready ? 'Aman😁' : 'Belum nih☹️' }}</li>
    </ul>

    <p>Salam, <br><strong>See ya!</strong></p>
</body>
</html>
