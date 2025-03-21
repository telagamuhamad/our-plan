<h2>🎉 Rencana Perjalanan Telah Selesai!</h2>
<p>Hai {{ $user_name }},</p>

<p>Selamat! kita udah jalan-jalan ke {{ $travel->destination }} nih!😁</p>

<ul>
    <li><strong>🌍 Destinasi:</strong> {{ $travel->destination }}</li>
    <li><strong>📅 Tanggal Kunjungan:</strong> {{ $travel->formatted_visit_date }}</li>
    <li><strong>📍 Meeting:</strong> {{ $meeting->location }} ({{ $meeting->formatted_start_date }} - {{ $meeting->formatted_end_date }})</li>
</ul>

<p>Semoga perjalanannya menyenangkan dan penuh kenangan indah! 💖</p>

<p>Jangan lupa catat hal-hal penting di catatan pribadi kamu, ya!</p>

<p>Salam hangat,<br><strong>Planner Team</strong></p>
