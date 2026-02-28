# LDR Couple App - Full Laravel (Blade) Version
## Detailed Planning & Implementation Guide

### 📱 Project Overview
Aplikasi web fullstack menggunakan Laravel dengan Blade templating untuk pasangan LDR. Dapat diakses melalui browser (desktop & mobile responsive).

**Keuntungan Pendekatan Full Laravel:**
- Development lebih cepat (single codebase)
- Tidak perlu build mobile app
- Lebih mudah di-maintain oleh 1-2 developer
- Push notification via browser notification API
- Progressive Web App (PWA) untuk install di homescreen
- No app store approval process

**Kekurangan:**
- Requires internet connection
- Tidak se-native mobile app
- Camera/media access terbatas (depends on browser)

---

## 🎯 1. Feature Specifications

### Core Features (sama seperti versi mobile, tapi dengan approach web)

#### A. Authentication & Pairing
**Tech Stack:**
- Laravel Breeze / Jetstream untuk auth scaffolding
- Session-based authentication
- Remember me functionality
- Email verification

**User Flow:**
```
[Landing Page] 
    → [Register] → Email verification
    → [Login] → Dashboard
    → [Pairing Modal]
        ├─ Create invite code
        └─ Join using code
    → [Couple Dashboard]
```

**Implementation:**
```php
// routes/web.php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/couples/invite', [CoupleController::class, 'createInvite']);
    Route::post('/couples/join', [CoupleController::class, 'join']);
});
```

#### B. Shared Timeline
**Display:**
- Card-based layout (Bootstrap/Tailwind cards)
- Infinite scroll dengan Livewire atau Alpine.js
- Modal untuk create post
- Lightbox untuk view images

**Features:**
- WYSIWYG editor untuk text posts (TinyMCE/Quill)
- Drag & drop image upload
- Voice recording via browser MediaRecorder API
- Real-time reactions (Livewire polling atau Pusher)

**Blade Components:**
```blade
{{-- resources/views/timeline/index.blade.php --}}
<div class="timeline-container">
    @foreach($posts as $post)
        <x-timeline-post :post="$post" />
    @endforeach
    
    {{ $posts->links() }}
</div>

{{-- components/timeline-post.blade.php --}}
<div class="post-card">
    <div class="post-header">
        <img src="{{ $post->user->avatar }}" />
        <span>{{ $post->user->name }}</span>
        <small>{{ $post->created_at->diffForHumans() }}</small>
    </div>
    <div class="post-content">
        @if($post->type === 'text')
            {!! $post->content !!}
        @elseif($post->type === 'image')
            <div class="post-images">
                @foreach($post->media as $media)
                    <img src="{{ $media->url }}" />
                @endforeach
            </div>
        @endif
    </div>
    <div class="post-actions">
        <livewire:post-reactions :post="$post" />
        <button class="btn-comment">Comment</button>
    </div>
</div>
```

#### C. Daily Mood Check-in
**UI Approach:**
- Modal popup atau dedicated page
- Large emoji buttons (easy to tap on mobile)
- Optional textarea untuk note
- Calendar view dengan color-coded days

**Livewire Component:**
```php
// app/Http/Livewire/MoodCheckin.php
class MoodCheckin extends Component
{
    public $mood;
    public $note;
    
    public function submit()
    {
        $this->validate([
            'mood' => 'required|in:amazing,good,okay,sad,terrible',
            'note' => 'nullable|max:200'
        ]);
        
        auth()->user()->couple->moodCheckins()->create([
            'user_id' => auth()->id(),
            'mood' => $this->mood,
            'note' => $this->note,
            'checkin_date' => now()->toDateString()
        ]);
        
        event(new MoodCheckedIn(auth()->user(), $this->mood));
        
        session()->flash('message', 'Mood checked in! ❤️');
        $this->reset();
    }
}
```

**Calendar View:**
```blade
<div class="mood-calendar">
    @foreach($calendar as $week)
        <div class="week-row">
            @foreach($week as $day)
                <div class="day-cell {{ $day['mood'] ? 'has-mood mood-' . $day['mood'] : '' }}">
                    <span class="date">{{ $day['date'] }}</span>
                    @if($day['mood'])
                        <span class="emoji">{{ $moodEmojis[$day['mood']] }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
</div>
```

#### D. Countdown Ketemu Lagi
**Display Options:**
1. **Hero Section di Dashboard**
```blade
<div class="countdown-hero">
    <h2>Ketemu Lagi</h2>
    <div class="countdown-timer" data-target="{{ $nextEvent->meet_date }}">
        <div class="time-unit">
            <span class="number" id="days">--</span>
            <span class="label">Hari</span>
        </div>
        <div class="time-unit">
            <span class="number" id="hours">--</span>
            <span class="label">Jam</span>
        </div>
        <div class="time-unit">
            <span class="number" id="minutes">--</span>
            <span class="label">Menit</span>
        </div>
    </div>
    <p class="location">📍 {{ $nextEvent->location }}</p>
</div>
```

2. **JavaScript Countdown:**
```javascript
// resources/js/countdown.js
function updateCountdown(targetDate) {
    const now = new Date().getTime();
    const target = new Date(targetDate).getTime();
    const distance = target - now;
    
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    
    document.getElementById('days').textContent = days;
    document.getElementById('hours').textContent = hours;
    document.getElementById('minutes').textContent = minutes;
}

// Update every minute
setInterval(() => updateCountdown(targetDate), 60000);
```

#### E. Couple Goals & To-Do
**UI:**
- Kanban-style board atau simple list view
- Drag & drop dengan SortableJS
- Filter: All / My Tasks / Shared / Completed

**Livewire Component:**
```php
class GoalsList extends Component
{
    public $goals;
    public $filter = 'all';
    
    public function mount()
    {
        $this->loadGoals();
    }
    
    public function loadGoals()
    {
        $query = auth()->user()->couple->goals();
        
        if ($this->filter === 'my') {
            $query->where('assigned_to', auth()->id());
        } elseif ($this->filter === 'shared') {
            $query->whereNull('assigned_to');
        }
        
        $this->goals = $query->orderBy('priority', 'desc')
                            ->orderBy('due_date')
                            ->get();
    }
    
    public function toggleComplete($goalId)
    {
        $goal = Goal::findOrFail($goalId);
        
        if ($goal->completed_at) {
            $goal->update(['completed_at' => null, 'completed_by' => null]);
        } else {
            $goal->update([
                'completed_at' => now(),
                'completed_by' => auth()->id()
            ]);
        }
        
        $this->loadGoals();
    }
}
```

---

### Financial Features

#### A. Savings Tracker
**Dashboard Widget:**
```blade
<div class="savings-widget">
    <div class="progress-section">
        <h3>Tabungan Nikah</h3>
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $savingsGoal->progress }}%"></div>
        </div>
        <div class="amounts">
            <span class="current">Rp {{ number_format($savingsGoal->current_amount) }}</span>
            <span class="target">/ Rp {{ number_format($savingsGoal->target_amount) }}</span>
        </div>
    </div>
    
    <div class="breakdown">
        <div class="contribution">
            <img src="{{ auth()->user()->avatar }}" />
            <span>You: Rp {{ number_format($myContribution) }}</span>
        </div>
        <div class="contribution">
            <img src="{{ $partner->avatar }}" />
            <span>{{ $partner->name }}: Rp {{ number_format($partnerContribution) }}</span>
        </div>
    </div>
    
    <button onclick="openAddTransactionModal()" class="btn-primary">
        💰 Tambah Tabungan
    </button>
</div>
```

**Transaction Form (Modal):**
```blade
<livewire:add-transaction-form :goal="$savingsGoal" />
```

**Charts dengan Chart.js:**
```javascript
// Monthly trend chart
const ctx = document.getElementById('savingsChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthlyData.labels,
        datasets: [{
            label: 'Monthly Savings',
            data: monthlyData.amounts,
            borderColor: '#FF6B6B',
            tension: 0.4
        }]
    }
});
```

#### B. Wedding Budget Planner
**Category Management:**
```blade
<div class="budget-planner">
    <div class="categories-list">
        @foreach($categories as $category)
            <div class="category-row">
                <div class="category-info">
                    <h4>{{ $category->name }}</h4>
                    <small>{{ $category->expenses->count() }} items</small>
                </div>
                <div class="category-amounts">
                    <span class="estimated">Est: {{ number_format($category->estimated_amount) }}</span>
                    <span class="actual {{ $category->isOverBudget() ? 'over-budget' : '' }}">
                        Actual: {{ number_format($category->actual_amount) }}
                    </span>
                </div>
                <button onclick="openExpenseModal({{ $category->id }})">
                    + Add Expense
                </button>
            </div>
        @endforeach
    </div>
    
    <div class="budget-summary">
        <canvas id="budgetChart"></canvas>
    </div>
</div>
```

---

### Emotional Features

#### A. Love Letter / Time Capsule
**Create Letter Page:**
```blade
<form action="{{ route('love-letters.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label>Untuk: {{ $partner->name }} ❤️</label>
    </div>
    
    <div class="form-group">
        <label>Pesan</label>
        <textarea name="content" id="letter-editor" rows="10"></textarea>
    </div>
    
    <div class="form-group">
        <label>Lampirkan Foto (Optional)</label>
        <input type="file" name="image" accept="image/*">
    </div>
    
    <div class="form-group">
        <label>Buka Tanggal</label>
        <input type="datetime-local" name="unlock_at" min="{{ now()->format('Y-m-d\TH:i') }}" required>
        <small>Surat ini akan terkunci sampai tanggal yang dipilih</small>
    </div>
    
    <button type="submit" class="btn-primary">✉️ Kirim Surat</button>
</form>

<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#letter-editor',
        plugins: 'emoticons',
        toolbar: 'bold italic | emoticons'
    });
</script>
```

**Letter Inbox:**
```blade
<div class="letters-inbox">
    @foreach($letters as $letter)
        <div class="letter-card {{ $letter->isLocked() ? 'locked' : '' }}">
            <div class="letter-header">
                <span class="from">From: {{ $letter->sender->name }}</span>
                <span class="date">{{ $letter->created_at->format('d M Y') }}</span>
            </div>
            
            @if($letter->isLocked())
                <div class="locked-content">
                    <i class="fas fa-lock"></i>
                    <p>Terkunci sampai {{ $letter->unlock_at->format('d M Y H:i') }}</p>
                    <div class="countdown-small" data-target="{{ $letter->unlock_at }}"></div>
                </div>
            @else
                <div class="letter-preview">
                    <p>{{ Str::limit(strip_tags($letter->content), 100) }}</p>
                    <a href="{{ route('love-letters.show', $letter) }}">Baca Surat</a>
                </div>
            @endif
        </div>
    @endforeach
</div>
```

#### B. Memory Vault
**Gallery View:**
```blade
<div class="memory-vault">
    <div class="upload-section">
        <button onclick="document.getElementById('memoryUpload').click()">
            📸 Upload Memory
        </button>
        <input type="file" id="memoryUpload" multiple accept="image/*,video/*" style="display:none" onchange="handleMemoryUpload(event)">
    </div>
    
    <div class="filter-tabs">
        <button class="active" data-view="grid">Grid View</button>
        <button data-view="timeline">Timeline</button>
        <button data-view="album">Albums</button>
    </div>
    
    <div class="memories-grid">
        @foreach($memories as $memory)
            <div class="memory-item" onclick="openMemoryModal({{ $memory->id }})">
                @if($memory->media_type === 'image')
                    <img src="{{ $memory->thumbnail_url }}" loading="lazy" />
                @else
                    <video src="{{ $memory->media_url }}" />
                    <i class="play-icon fas fa-play"></i>
                @endif
                
                <div class="memory-overlay">
                    <span class="date">{{ $memory->taken_at->format('d M Y') }}</span>
                    @if($memory->is_favorite)
                        <i class="fas fa-heart favorite"></i>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Lightbox Modal --}}
<div id="memoryModal" class="modal">
    <div class="modal-content">
        <img id="modalImage" />
        <div class="modal-caption">
            <p id="modalCaptionText"></p>
            <button onclick="toggleFavorite()">❤️</button>
        </div>
    </div>
</div>
```

#### C. "Missing You" Button
**Implementation:**
```blade
{{-- On Dashboard --}}
<button 
    onclick="sendMissingYou()" 
    class="missing-you-btn"
    @if($canSend) enabled @else disabled @endif
>
    💌 Kangen Kamu
</button>

<script>
function sendMissingYou() {
    fetch('/api/missing-you', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Notifikasi terkirim ke ' + partnerName + ' ❤️');
            // Disable button for 1 hour
            disableButton(3600);
        }
    });
}

// Browser notification on receive
if ('Notification' in window && Notification.permission === 'granted') {
    // Listen via Pusher/Laravel Echo
    Echo.private('couple.' + coupleId)
        .listen('MissingYouSent', (e) => {
            new Notification(e.sender.name + ' kangen kamu!', {
                body: e.message || 'Kangen banget nih ❤️',
                icon: e.sender.avatar
            });
        });
}
</script>
```

#### D. Question of the Day
**Daily Question Widget:**
```blade
<div class="daily-question-card">
    <h3>Question of the Day</h3>
    <p class="question-text">{{ $todayQuestion->question_text }}</p>
    
    @if($myAnswer)
        <div class="my-answer">
            <strong>Your Answer:</strong>
            <p>{{ $myAnswer->answer_text }}</p>
        </div>
    @else
        <form action="{{ route('questions.answer') }}" method="POST">
            @csrf
            <textarea name="answer" placeholder="Your answer..." required></textarea>
            <button type="submit">Submit Answer</button>
        </form>
    @endif
    
    @if($partnerAnswer && $myAnswer)
        <div class="partner-answer">
            <strong>{{ $partner->name }}'s Answer:</strong>
            <p>{{ $partnerAnswer->answer_text }}</p>
        </div>
    @elseif($myAnswer)
        <p class="waiting">Waiting for {{ $partner->name }} to answer...</p>
    @endif
</div>
```

---

## 🏗️ 2. Laravel Project Structure

### Directory Layout
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── CoupleController.php
│   │   ├── TimelineController.php
│   │   ├── MoodController.php
│   │   ├── MeetEventController.php
│   │   ├── GoalController.php
│   │   ├── SavingsController.php
│   │   ├── WeddingBudgetController.php
│   │   ├── LoveLetterController.php
│   │   ├── MemoryController.php
│   │   └── QuestionController.php
│   ├── Livewire/
│   │   ├── MoodCheckin.php
│   │   ├── TimelinePost.php
│   │   ├── PostReactions.php
│   │   ├── GoalsList.php
│   │   ├── AddTransactionForm.php
│   │   └── MemoryUploader.php
│   ├── Middleware/
│   │   ├── EnsureUserHasCouple.php
│   │   └── EnsureCoupleIsActive.php
│   └── Requests/
│       ├── StoreTimelinePostRequest.php
│       ├── MoodCheckinRequest.php
│       └── ...
├── Models/
│   ├── User.php
│   ├── Couple.php
│   ├── TimelinePost.php
│   ├── MoodCheckin.php
│   ├── MeetEvent.php
│   ├── Goal.php
│   ├── SavingsGoal.php
│   ├── LoveLetter.php
│   ├── Memory.php
│   └── ...
├── Services/
│   ├── CoupleService.php
│   ├── NotificationService.php
│   ├── MediaUploadService.php
│   └── AnalyticsService.php
├── Events/
│   ├── MoodCheckedIn.php
│   ├── MissingYouSent.php
│   └── LoveLetterUnlocked.php
└── Listeners/
    ├── SendMoodNotification.php
    ├── SendMissingYouNotification.php
    └── SendLoveLetterNotification.php

resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php
│   │   ├── guest.blade.php
│   │   └── navigation.blade.php
│   ├── dashboard/
│   │   └── index.blade.php
│   ├── timeline/
│   │   ├── index.blade.php
│   │   └── create.blade.php
│   ├── mood/
│   │   ├── checkin.blade.php
│   │   └── calendar.blade.php
│   ├── savings/
│   │   ├── index.blade.php
│   │   └── transactions.blade.php
│   ├── memories/
│   │   └── index.blade.php
│   ├── love-letters/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── show.blade.php
│   └── components/
│       ├── timeline-post.blade.php
│       ├── mood-emoji.blade.php
│       ├── countdown-card.blade.php
│       └── ...
├── js/
│   ├── app.js
│   ├── countdown.js
│   ├── notifications.js
│   └── charts.js
└── css/
    ├── app.css
    └── custom.css
```

---

## 🎨 3. Frontend Implementation

### Layout Template (Tailwind CSS)
```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50">
    {{-- Top Navigation --}}
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-pink-600">
                    ❤️ {{ config('app.name') }}
                </a>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('notifications') }}" class="relative">
                        <i class="fas fa-bell"></i>
                        @if($unreadCount > 0)
                            <span class="notification-badge">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    
                    <div class="flex items-center space-x-2">
                        <img src="{{ auth()->user()->avatar }}" class="w-8 h-8 rounded-full" />
                        <span>{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    {{-- Main Content --}}
    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>
    
    {{-- Bottom Nav (Mobile) --}}
    <nav class="mobile-bottom-nav md:hidden">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('timeline') }}" class="{{ request()->routeIs('timeline*') ? 'active' : '' }}">
            <i class="fas fa-photo-video"></i>
            <span>Timeline</span>
        </a>
        <a href="{{ route('savings') }}" class="{{ request()->routeIs('savings*') ? 'active' : '' }}">
            <i class="fas fa-piggy-bank"></i>
            <span>Savings</span>
        </a>
        <a href="{{ route('memories') }}" class="{{ request()->routeIs('memories*') ? 'active' : '' }}">
            <i class="fas fa-images"></i>
            <span>Memories</span>
        </a>
        <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>
    
    @livewireScripts
    @stack('scripts')
</body>
</html>
```

### Dashboard View
```blade
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    {{-- Countdown Widget --}}
    <div class="col-span-full">
        <x-countdown-card :event="$nextEvent" />
    </div>
    
    {{-- Mood Section --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Today's Mood</h3>
        <div class="flex justify-around items-center">
            <div class="text-center">
                <div class="text-4xl mb-2">{{ $myMood ? $moodEmojis[$myMood->mood] : '😊' }}</div>
                <p class="text-sm text-gray-600">You</p>
                @if(!$myMood)
                    <button onclick="openMoodModal()" class="text-sm text-blue-500 mt-2">
                        Check in
                    </button>
                @endif
            </div>
            
            <div class="text-center">
                <div class="text-4xl mb-2">{{ $partnerMood ? $moodEmojis[$partnerMood->mood] : '❓' }}</div>
                <p class="text-sm text-gray-600">{{ $partner->name }}</p>
            </div>
        </div>
    </div>
    
    {{-- Quick Actions --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
        <div class="space-y-3">
            <button onclick="openNewPostModal()" class="w-full btn-primary">
                ✏️ New Post
            </button>
            <button onclick="sendMissingYou()" class="w-full btn-secondary" id="missingYouBtn">
                💌 Missing You
            </button>
            <a href="{{ route('memories.create') }}" class="w-full btn-secondary block text-center">
                📸 Add Memory
            </a>
        </div>
    </div>
    
    {{-- Savings Widget --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Wedding Savings</h3>
        <div class="mb-4">
            <div class="flex justify-between text-sm mb-2">
                <span>{{ $savingsGoal->progress }}%</span>
                <span>Rp {{ number_format($savingsGoal->current_amount, 0, ',', '.') }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-pink-500 h-3 rounded-full" style="width: {{ $savingsGoal->progress }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Target: Rp {{ number_format($savingsGoal->target_amount, 0, ',', '.') }}</p>
        </div>
        <a href="{{ route('savings') }}" class="text-sm text-blue-500">View Details →</a>
    </div>
    
    {{-- Question of the Day --}}
    <div class="col-span-full">
        <livewire:daily-question />
    </div>
    
    {{-- Recent Timeline --}}
    <div class="col-span-full">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Updates</h3>
            @foreach($recentPosts->take(3) as $post)
                <x-timeline-post :post="$post" />
            @endforeach
            <a href="{{ route('timeline') }}" class="block text-center text-blue-500 mt-4">
                See All Updates →
            </a>
        </div>
    </div>
    
</div>

{{-- Modals --}}
<livewire:mood-checkin-modal />
<livewire:new-post-modal />

@endsection

@push('scripts')
<script>
    // Countdown update
    updateCountdown('{{ $nextEvent->meet_date }}');
    setInterval(() => updateCountdown('{{ $nextEvent->meet_date }}'), 60000);
    
    // Missing you cooldown
    checkMissingYouCooldown();
</script>
@endpush
```

---

## 🔔 4. Real-time Features

### Laravel Echo + Pusher Setup
```javascript
// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// Listen to couple channel
Echo.private(`couple.${coupleId}`)
    .listen('MoodCheckedIn', (e) => {
        // Update partner mood display
        updatePartnerMood(e.mood);
        
        // Show notification
        showToast(`${e.user.name} checked in: ${e.mood}`);
    })
    .listen('MissingYouSent', (e) => {
        // Show browser notification
        if (Notification.permission === 'granted') {
            new Notification(`${e.sender.name} kangen kamu!`, {
                body: e.message,
                icon: e.sender.avatar,
                badge: '/icon-badge.png'
            });
        }
        
        // Play sound
        new Audio('/sounds/notification.mp3').play();
    })
    .listen('TimelinePostCreated', (e) => {
        // Prepend new post to timeline
        prependPost(e.post);
    });
```

### Browser Notifications
```javascript
// resources/js/notifications.js
function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                console.log('Notification permission granted');
            }
        });
    }
}

// Call on page load
document.addEventListener('DOMContentLoaded', requestNotificationPermission);
```

---

## 📱 5. Progressive Web App (PWA)

### Manifest File
```json
// public/manifest.json
{
  "name": "LDR Couple App",
  "short_name": "LDR App",
  "description": "Private app for long-distance couples",
  "start_url": "/dashboard",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#FF6B6B",
  "orientation": "portrait",
  "icons": [
    {
      "src": "/icons/icon-72x72.png",
      "sizes": "72x72",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

### Service Worker
```javascript
// public/sw.js
const CACHE_NAME = 'ldr-app-v1';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/offline.html'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
      .catch(() => caches.match('/offline.html'))
  );
});
```

### Register Service Worker
```blade
{{-- In layouts/app.blade.php --}}
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
        .then(reg => console.log('Service Worker registered'))
        .catch(err => console.log('Service Worker registration failed'));
}
</script>
```

---

## 🗄️ 6. Database Schema
(Same as mobile version - refer to previous document)

---

## 🚀 7. Development Workflow

### Phase 1: MVP (6-8 weeks)
**Week 1-2: Setup & Auth**
- Laravel project setup
- Authentication (Breeze/Jetstream)
- Couple pairing system
- Database migrations

**Week 3-4: Core Features**
- Timeline (CRUD posts)
- Mood check-in (Livewire component)
- Countdown (dashboard widget)
- Basic notifications

**Week 5-6: Financial Features**
- Savings tracker
- Transaction management
- Charts (Chart.js)

**Week 7-8: Testing & Polish**
- Responsive design
- Browser testing
- Bug fixes
- Deploy to staging

### Phase 2: Emotional Features (4-5 weeks)
- Love letters
- Memory vault
- Question of the day
- Real-time features (Echo + Pusher)

### Phase 3: Wedding Planning (3-4 weeks)
- Budget planner
- Expense tracking
- Analytics dashboard

### Phase 4: Polish & PWA (2-3 weeks)
- PWA setup
- Performance optimization
- Offline mode
- Production deployment

---

## ⚙️ 8. Technical Stack

### Backend
- Laravel 11.x
- PHP 8.2+
- MySQL 8.0+
- Redis (cache & queue)

### Frontend
- Livewire 3.x
- Alpine.js 3.x
- Tailwind CSS 3.x
- Chart.js
- TinyMCE (rich text editor)
- SortableJS (drag & drop)

### Real-time
- Laravel Echo
- Pusher (or Laravel Websockets)

### Media Upload
- Spatie Media Library
- Image intervention (resize/optimize)
- S3 or local storage

### Deployment
- Laravel Forge / Ploi
- DigitalOcean / AWS
- Cloudflare (CDN & cache)

---

## 🔒 9. Security Features

### CSRF Protection
```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Rate Limiting
```php
// routes/web.php
Route::middleware(['throttle:60,1'])->group(function () {
    // Protected routes
});
```

### XSS Prevention
```blade
{{-- Auto-escaped --}}
{{ $user->name }}

{{-- Raw HTML (careful!) --}}
{!! $post->content !!}
```

### SQL Injection Prevention
```php
// Always use Eloquent or Query Builder
$posts = TimelinePost::where('couple_id', $coupleId)->get();

// Never raw queries with user input
// Bad: DB::select("SELECT * FROM posts WHERE id = " . $id);
```

---

## 📊 10. Performance Optimization

### Lazy Loading
```blade
<img src="{{ $memory->url }}" loading="lazy" />
```

### Eager Loading
```php
$posts = TimelinePost::with(['user', 'media', 'reactions'])
    ->where('couple_id', $coupleId)
    ->paginate(20);
```

### Caching
```php
$savingsGoal = Cache::remember("couple.{$coupleId}.savings", 3600, function () {
    return SavingsGoal::with('transactions')->first();
});
```

### Asset Optimization
```bash
npm run build
```

---

## 🎯 11. Unique Features for Web Version

### Desktop Advantages
1. **Side-by-side View**: Timeline + mood calendar simultaneously
2. **Keyboard Shortcuts**: Quick post (Ctrl+N), search (Ctrl+K)
3. **Multi-tab Support**: Open different sections in tabs
4. **Larger Charts**: Better data visualization on big screens

### Mobile Web Optimizations
1. **Touch Gestures**: Swipe to delete, pull to refresh
2. **Bottom Navigation**: Easy thumb access
3. **Modal Sheets**: Native-feeling bottom sheets
4. **Camera Access**: Direct camera integration via `<input capture="camera">`

### Browser-specific Features
1. **Web Share API**: Share memories to other apps
2. **Web Speech API**: Voice-to-text for notes
3. **Geolocation API**: Auto-fill location for meet events
4. **Local Storage**: Offline draft saving

---

## 📝 12. Migration from Web to Native Later

If you decide to build native apps later, the web version serves as:
1. **API Foundation**: Backend already RESTful
2. **Feature Validation**: Test what users actually use
3. **UI Reference**: Proven user flows
4. **Backup Access**: Always available via browser

---

## 🚀 13. Deployment Checklist

### Pre-deployment
- [ ] Environment variables configured
- [ ] Database migrations tested
- [ ] SSL certificate installed
- [ ] Cron jobs scheduled
- [ ] Queue workers running
- [ ] Cache configured (Redis)
- [ ] Backup strategy in place

### Post-deployment
- [ ] Monitor error logs
- [ ] Check performance metrics
- [ ] Test all features
- [ ] Verify notifications work
- [ ] Test on multiple devices/browsers

---

## 💡 14. Additional Tips

### Development Best Practices
1. Use Laravel Telescope for debugging
2. Implement automated tests (Feature tests for critical flows)
3. Use Laravel Debugbar in development
4. Set up CI/CD pipeline (GitHub Actions)
5. Regular database backups

### User Experience
1. Loading states for all async actions
2. Error messages in Indonesian
3. Success feedback (toasts, animations)
4. Smooth transitions between pages
5. Accessible (keyboard navigation, screen readers)

### Maintenance
1. Monitor uptime (UptimeRobot)
2. Track errors (Sentry/Bugsnag)
3. Analytics (Google Analytics / Plausible)
4. Regular updates (Laravel, packages)
5. Performance monitoring (New Relic)

---

**End of Document** 🚀

**Quick Start Command:**
```bash
# Clone starter template
laravel new ldr-couple-app --git --jet

# Or start from scratch
composer create-project laravel/laravel ldr-couple-app
cd ldr-couple-app
composer require livewire/livewire
npm install && npm run build
php artisan migrate
php artisan serve
```
