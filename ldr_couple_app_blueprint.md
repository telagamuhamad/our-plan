# LDR Couple App — Product Blueprint

> Dokumen perencanaan: ide fitur, roadmap, database schema, arsitektur API, dan UI flow.  
> **Target:** Aplikasi mobile private untuk pasangan LDR dengan backend API Laravel.

---

## 1. Ide Fitur

### 1.1 Core Features (MVP)

- **Pairing account** (invite code) supaya hanya dua user dalam satu "couple space".
- **Shared Timeline:** post text, foto, voice note, reaction, dan komentar singkat.
- **Daily Mood Check-In:** pilih mood + optional note; tampilkan history mood pasangan.
- **Countdown Ketemu Lagi:** hitung mundur otomatis ke tanggal meet-up berikutnya.
- **Couple Goals & To-Do List:** checklist aktivitas bersama dan target mingguan.

### 1.2 Financial Features (Wedding Saving Focus)

- **Savings Tracker:** target dana, progress bar, kontribusi masing-masing, dan milestone.
- **Simple Wedding Budget Planner:** kategori biaya (venue, catering, outfit, foto/video, dll).
- **Expense Note** ringan untuk tracking biaya persiapan tanpa kompleksitas aplikasi finance.

### 1.3 Emotional / Unique Features

- **Love Letter / Time Capsule:** pesan yang hanya bisa dibuka pada tanggal tertentu.
- **Memory Vault:** galeri momen bersama dengan auto grouping per bulan/event.
- **"Missing You" Button:** kirim notifikasi spontan ke pasangan.
- **Question of the Day:** pertanyaan harian untuk menjaga percakapan tetap fresh.

### 1.4 Nice-to-Have (Future)

- AI weekly recap (ringkasan aktivitas dan mood).
- Widget homescreen (countdown + status mood).
- Virtual date planner (randomizer aktivitas online).

---

## 2. Roadmap Pengembangan

| Phase | Goal | Fokus | Output |
|---|---|---|---|
| **Phase 1 (MVP)** | App usable daily | Auth, pairing, timeline, mood, countdown, savings tracker | Versi internal dipakai berdua |
| **Phase 2** | Increase engagement | Memory vault, love letter, notifications, shared goals | Retention meningkat |
| **Phase 3** | Planning menikah | Budget planner, milestone, analytics sederhana | Planning terstruktur |
| **Phase 4** | Delight features | AI recap, widgets, smart suggestions | Experience terasa personal |

> **Catatan:** Fokus utama adalah membuat fitur kecil tapi dipakai setiap hari, bukan banyak fitur yang jarang disentuh.

---

## 3. Database Schema (High-Level)

Model inti didesain untuk 2 user dalam satu relasi couple.

### `users`
```
id, name, email, password
avatar_url, timezone
created_at, updated_at
```

### `couples`
```
id, invite_code
user_one_id, user_two_id
status (pending/active)
created_at
```

### `timeline_posts`
```
id, couple_id, user_id
type (text/image/voice)
content, media_url
created_at
```

### `post_reactions`
```
id, post_id, user_id
reaction_type
```

### `mood_checkins`
```
id, couple_id, user_id
mood, note, checkin_date
```

### `meet_events`
```
id, couple_id
title, meet_date, location (optional)
```

### `savings_goals`
```
id, couple_id
target_amount, current_amount
target_date
```

### `savings_transactions`
```
id, goal_id, user_id
amount, note, created_at
```

### `love_letters`
```
id, couple_id, sender_id, receiver_id
content, unlock_at, opened_at
```

### `memories`
```
id, couple_id, uploader_id
media_url, caption, taken_at
```

### `notifications`
```
id, user_id
type, payload (json), read_at
```

> **Relasi utama:** `users ↔ couples` (1–1 dalam konteks app), `couples → banyak posts/moods/goals/events`.

---

## 4. Arsitektur API (Laravel)

**Pattern:** REST API + token auth. Semua endpoint scoped ke couple yang aktif untuk keamanan data.

- **Auth:** register, login, logout (Sanctum token).
- **Pairing flow:** create invite code, join via code, accept pairing.
- **Middleware:** ensure user belongs to requested `couple_id`.
- **Resource-based controllers** (`TimelineController`, `MoodController`, `SavingsController`, dll).
- Gunakan **Form Request validation** untuk input sanitization.
- **Soft delete** untuk konten emosional (post/memory/letter).
- **Event + Notification system** untuk push updates (mis. missing you button).

### Contoh Endpoint Struktur

```
POST   /api/auth/register
POST   /api/couples/invite
POST   /api/couples/join
GET    /api/timeline
POST   /api/timeline
POST   /api/moods/checkin
GET    /api/countdown
GET    /api/savings-goals
POST   /api/savings-transactions
POST   /api/love-letters
```

> **Recommended structure:** Controllers tipis → logic di Service layer → query di Repository (opsional). Gunakan API Resources untuk response consistency.

---

## 5. Ide UI Flow (Mobile)

### 5.1 Onboarding

1. Splash → Login / Register
2. Pairing screen: create code atau join code
3. Set relationship milestone (anniversary, next meet date)

### 5.2 Main Navigation (Bottom Tabs)

| Tab | Konten |
|---|---|
| **Home** | Timeline + quick actions (post, missing you, mood) |
| **Countdown** | Event dan planning ketemu |
| **Savings** | Progress target nikah + transaksi |
| **Memories** | Gallery bersama |
| **Profile/Settings** | Account & notification |

### 5.3 UX Principles (biar terasa intimate)

- **Minimize clutter:** 1–2 primary actions per screen.
- **Visual progress** (countdown & savings) selalu terlihat.
- Gunakan **soft emotional cues** (micro-copy, animasi ringan).
- **Private by default:** tidak ada fitur sosial publik.

### 5.4 Suggested UI Sequence (Daily Use)

1. Open app → lihat countdown & mood pasangan.
2. Quick check-in mood.
3. Scroll timeline / drop small update.
4. Lihat progress tabungan.

---

## Appendix — Tech Suggestions

| Layer | Stack |
|---|---|
| **Backend** | Laravel + Sanctum + Queue + Notifications |
| **Mobile** | Flutter (single codebase, fast iteration) |
| **Storage** | S3-compatible object storage for media |
| **Realtime** (optional) | WebSocket / Pusher for instant updates |
