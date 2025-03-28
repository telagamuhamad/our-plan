<h2>🔁 Transfer Saldo Antar Tabungan</h2>
<p>Hai {{ $user_name }},</p>

<p>Barusan ada transfer saldo antar tabungan nih😁. Berikut detailnya:</p>

<ul>
    <li><strong>💰 Nominal Transfer:</strong> Rp {{ number_format($amount, 0, ',', '.') }}</li>
</ul>

<h4>📤 Dari Tabungan:</h4>
<ul>
    <li><strong>Nama:</strong> {{ $fromSaving->name }}</li>
    <li><strong>Saldo Sekarang:</strong> Rp {{ number_format($fromSaving->current_amount, 0, ',', '.') }}</li>
</ul>

<h4>📥 Ke Tabungan:</h4>
<ul>
    <li><strong>Nama:</strong> {{ $toSaving->name }}</li>
    <li><strong>Saldo Sekarang:</strong> Rp {{ number_format($toSaving->current_amount, 0, ',', '.') }}</li>
</ul>

<p>Terima kasih telah menggunakan Savings Tracker untuk merencanakan keuangan bersama 💖</p>

<p>Salam hangat,<br><strong>Planner Team</strong></p>
