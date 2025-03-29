<h2>💸 Transaksi Tabungan</h2>
<p>Hai {{ $user_name }},</p>

<p>Ada transaksi {{ $type }} di tabungan kita nih!😲</p>
<p>Berikut ringkasan transaksi tabungan kamu:</p>

<ul>
    <li><strong>💼 Jenis Transaksi:</strong> {{ ucfirst($type) }}</li>
    <li><strong>🏷️ Nama Tabungan:</strong> {{ $saving->name }}</li>
    <li><strong>💰 Nominal:</strong> Rp {{ number_format($amount, 0, ',', '.') }}</li>
    <li><strong>📊 Saldo Sekarang:</strong> Rp {{ number_format($saving->current_amount, 0, ',', '.') }}</li>
    @if ($note)
        <li><strong>📝 Catatan:</strong> {{ $note }}</li>
    @endif
</ul>

<p>Terima kasih telah menggunakan fitur Savings Tracker.</p>

<p>Salam, <br>Planner Team</p>
