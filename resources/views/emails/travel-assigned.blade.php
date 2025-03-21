<h2>📌 Travel Planner Telah Di-Assign ke Meeting</h2>
<p>Hai {{ $user_name }},</p>
<p>Rencana perjalanan yang ini udah di-assign ke jadwal ketemu kita yaa😊</p>

<ul>
    <li><strong>🌍 Destinasi:</strong> {{ $travel->destination }}</li>
    <li><strong>📅 Tanggal Kunjungan:</strong> {{ $travel->formatted_visit_date }}</li>
    <li><strong>📍 Meeting:</strong> {{ $meeting->location }} ({{ $meeting->formatted_start_date }} - {{ $meeting->formatted_end_date }})</li>
</ul>

<p>Silakan cek kembali perjalananmu di halaman Planner.</p>
<p>Salam hangat,<br>See ya!</p>
