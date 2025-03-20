<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Ketemu</title>
</head>
<body>
    <h2>📅 Konfirmasi Jadwal Ketemu</h2>
    <p>Hai {{ $user_name }},</p>
    <p>Kita ada jadwal ketemu nih! ini detailnya yaa😊</p>

    <ul>
        <li><strong>🧑 Siapa yang Berangkat:</strong> {{ $meeting->user->name }}</li>
        <li><strong>📍 Lokasi:</strong> {{ $meeting->location }}</li>
        <li><strong>🗓️ Tanggal:</strong> {{ $meeting->formatted_start_date }} - {{ $meeting->formatted_end_date }}</li>
        <li><strong>📝 Catatan:</strong> {{ $meeting->note ?? 'Tidak ada catatan' }}</li>
    </ul>

    <p>Pastikan semua persiapan sudah dilakukan sebelum ketemu yaa😊</p>
    <p>Ini list persiapan yang harus dilakukan:</p>
    <ul>
        <li>
            <strong>🚅 Kendaraan Berangkat : </strong>
            @if($meeting->is_departure_transport_ready)
                <span class="badge bg-success">Aman😁</span>
            @else
                <span class="badge bg-danger">Belum nih☹️</span>
            @endif
        </li>
        <li>
            <strong>🚅 Kendaraan Pulang : </strong>
            @if($meeting->is_return_transport_ready)
                <span class="badge bg-success">Aman😁</span>
            @else
                <span class="badge bg-danger">Belum nih☹️</span>
            @endif
        </li>
        <li>
            <strong>🏠 Tempat Istirahat : </strong>
            @if($meeting->is_rest_place_ready)
                <span class="badge bg-success">Aman😁</span>
            @else
                <span class="badge bg-danger">Belum nih☹️</span>
            @endif
        </li>
    </ul>

    <p>Salam, <br><strong>See ya!</strong></p>
</body>
</html>
