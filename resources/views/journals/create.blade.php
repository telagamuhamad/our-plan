@extends('layouts.app')

@section('title', 'Tulis Journal Baru')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">✍️ Tulis Travel Journal Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('journals.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="Contoh: Hari Pertama di Bali" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="journal_date" class="form-label">Tanggal Journal</label>
                                    <input type="date" name="journal_date" id="journal_date" class="form-control" value="{{ old('journal_date') ?? now()->format('Y-m-d') }}">
                                    <small class="text-muted">Default: hari ini</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mood" class="form-label">Mood saat itu</label>
                                    <select name="mood" id="mood" class="form-select">
                                        <option value="">-- Pilih Mood --</option>
                                        <option value="happy">😊 Happy</option>
                                        <option value="excited">🤩 Excited</option>
                                        <option value="love">🥰 Love</option>
                                        <option value="sad">😢 Sad</option>
                                        <option value="tired">😫 Tired</option>
                                        <option value="adventurous">🤠 Adventurous</option>
                                        <option value="relaxed">😌 Relaxed</option>
                                        <option value="surprised">😲 Surprised</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weather" class="form-label">Cuaca</label>
                                    <input type="text" name="weather" id="weather" class="form-control" placeholder="Cerah, Hujan, Berawan...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Lokasi Spesifik</label>
                                    <input type="text" name="location" id="location" class="form-control" placeholder="Nama tempat, cafe, spot...">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="travel_id" class="form-label">Link ke Travel (opsional)</label>
                            <select name="travel_id" id="travel_id" class="form-select">
                                <option value="">-- Tidak ada travel terkait --</option>
                                @foreach($travels as $travel)
                                    <option value="{{ $travel->id }}" {{ old('travel_id') == $travel->id ? 'selected' : '' }}>
                                        {{ $travel->destination }} ({{ $travel->formatted_visit_date }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Opsional: Hubungkan journal ini dengan travel planner yang sudah dibuat</small>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Isi Journal <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" class="form-control" rows="10" placeholder="Ceritakan tentang perjalanan, pengalaman, perasaan, momen spesial..." required>{{ old('content') }}</textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_favorite" id="is_favorite" class="form-check-input" value="1">
                            <label class="form-check-label" for="is_favorite">
                                ⭐ Tandai sebagai favorit
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">💾 Simpan Journal</button>
                            <a href="{{ route('journals.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
