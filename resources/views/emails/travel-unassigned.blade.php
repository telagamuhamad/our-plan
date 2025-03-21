<h2>🚫 Travel Planner Dihapus dari Meeting</h2>
<p>Hai {{ $user_name }},</p>
<p>Rencana perjalanan berikut telah dilepaskan dari jadwal yang ini yaa☹️:</p>

<ul>
    <li><strong>🌍 Destinasi:</strong> {{ $travel->destination }}</li>
    <li><strong>📅 Tanggal Kunjungan:</strong> {{ $travel->formatted_visit_date }}</li>
    <li><strong>📍 Jadwal:</strong> {{ $meeting->location }} ({{ $meeting->formatted_start_date }} - {{ $meeting->formatted_end_date }})</li>
</ul>

<p>Kamu masih bisa meng-assign ulang rencana ini ke jadwal yang lain.</p>
<p>Salam,<br>Planner Team</p>
