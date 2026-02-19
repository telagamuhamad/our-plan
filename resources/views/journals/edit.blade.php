@extends('layouts.app')

@section('title', 'Edit Journal')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">✏️ Edit Travel Journal</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('journals.update', $journal->id) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $journal->title) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="journal_date" class="form-label">Tanggal Journal</label>
                                    <input type="date" name="journal_date" id="journal_date" class="form-control" value="{{ old('journal_date', $journal->journal_date?->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mood" class="form-label">Mood saat itu</label>
                                    <select name="mood" id="mood" class="form-select">
                                        <option value="">-- Pilih Mood --</option>
                                        <option value="happy" {{ $journal->mood === 'happy' ? 'selected' : '' }}>😊 Happy</option>
                                        <option value="excited" {{ $journal->mood === 'excited' ? 'selected' : '' }}>🤩 Excited</option>
                                        <option value="love" {{ $journal->mood === 'love' ? 'selected' : '' }}>🥰 Love</option>
                                        <option value="sad" {{ $journal->mood === 'sad' ? 'selected' : '' }}>😢 Sad</option>
                                        <option value="tired" {{ $journal->mood === 'tired' ? 'selected' : '' }}>😫 Tired</option>
                                        <option value="adventurous" {{ $journal->mood === 'adventurous' ? 'selected' : '' }}>🤠 Adventurous</option>
                                        <option value="relaxed" {{ $journal->mood === 'relaxed' ? 'selected' : '' }}>😌 Relaxed</option>
                                        <option value="surprised" {{ $journal->mood === 'surprised' ? 'selected' : '' }}>😲 Surprised</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="weather" class="form-label">Cuaca</label>
                                    <input type="text" name="weather" id="weather" class="form-control" value="{{ old('weather', $journal->weather) }}" placeholder="Cerah, Hujan, Berawan...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Lokasi Spesifik</label>
                                    <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $journal->location) }}" placeholder="Nama tempat, cafe, spot...">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="travel_id" class="form-label">Link ke Travel (opsional)</label>
                            <select name="travel_id" id="travel_id" class="form-select">
                                <option value="">-- Tidak ada travel terkait --</option>
                                @foreach($travels as $travel)
                                    <option value="{{ $travel->id }}" {{ old('travel_id', $journal->travel_id) == $travel->id ? 'selected' : '' }}>
                                        {{ $travel->destination }} ({{ $travel->formatted_visit_date }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Isi Journal <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" class="form-control" rows="10" placeholder="Ceritakan tentang perjalanan, pengalaman, perasaan, momen spesial..." required>{{ old('content', $journal->content) }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">💾 Update Journal</button>
                            <a href="{{ route('journals.show', $journal->id) }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
