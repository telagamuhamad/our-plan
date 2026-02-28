# Our Plan API Documentation

**Base URL:** `https://your-domain.com/api`

**Version:** 1.0.0

**Authentication:** Bearer Token (Laravel Sanctum)

---

## Table of Contents

1. [Authentication](#authentication)
2. [General Response Format](#general-response-format)
3. [Error Codes](#error-codes)
4. [Auth Endpoints](#auth-endpoints)
5. [Profile Endpoints](#profile-endpoints)
6. [Pairing Endpoints](#pairing-endpoints)
7. [Meetings Endpoints](#meetings-endpoints)
8. [Travels Endpoints](#travels-endpoints)
9. [Travel Photos Endpoints](#travel-photos-endpoints)
10. [Travel Journals Endpoints](#travel-journals-endpoints)
11. [Savings Endpoints](#savings-endpoints)
12. [Saving Categories Endpoints](#saving-categories-endpoints)
13. [Recurring Savings Endpoints](#recurring-savings-endpoints)
14. [Savings Analytics Endpoints](#savings-analytics-endpoints)
15. [Savings Comparison Endpoints](#savings-comparison-endpoints)
16. [Timeline Endpoints](#timeline-endpoints)
17. [Mood Check-In Endpoints](#mood-check-in-endpoints)
18. [Missing You Endpoints](#missing-you-endpoints)
19. [Question of the Day Endpoints](#question-of-the-day-endpoints)
20. [Goals Endpoints](#goals-endpoints)
21. [Tasks Endpoints](#tasks-endpoints)
22. [Notifications Endpoints](#notifications-endpoints)

---

## Authentication

### How to Authenticate

All protected endpoints require a Bearer token in the Authorization header:

```http
Authorization: Bearer {token}
```

The token is obtained from `/api/register` or `/api/login` endpoints.

---

## General Response Format

### Success Response

```json
{
  "success": true,
  "message": "Success message (optional)",
  "data": { ... } | [ ... ]
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error message describing what went wrong",
  "errors": {
    "field_name": ["Error message for this field"]
  }
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 201  | Created |
| 400  | Bad Request (validation/business logic error) |
| 401  | Unauthorized (token invalid/expired) |
| 403  | Forbidden (not paired with partner) |
| 404  | Not Found |
| 422  | Validation Error |
| 429  | Too Many Requests (rate limited) |
| 500  | Internal Server Error |

---

## Auth Endpoints

### Register

Create a new user account.

**Endpoint:** `POST /api/register`

**Auth Required:** No

**Request Body:**
```json
{
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "timezone": "Asia/Jakarta"
}
```

**Validation Rules:**
- `name`: required, string, max 255
- `username`: required, string, max 255, unique, regex `/^[a-zA-Z0-9_]+$/`
- `email`: required, email, unique
- `password`: required, min 6, confirmed
- `timezone`: optional, string, max 255

**Success Response (201):**
```json
{
  "success": true,
  "message": "Registrasi berhasil.",
  "token": "plainTextTokenHere",
  "user": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "avatar_url": null,
    "timezone": "Asia/Jakarta",
    "has_couple": false,
    "has_active_couple": false,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### Login

Authenticate with username/email and password.

**Endpoint:** `POST /api/login`

**Auth Required:** No

**Request Body:**
```json
{
  "login": "johndoe",  // username or email
  "password": "password123"
}
```

**Validation Rules:**
- `login`: required, string
- `password`: required, min 6

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login berhasil.",
  "token": "plainTextTokenHere",
  "user": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "avatar_url": null,
    "timezone": "Asia/Jakarta",
    "has_couple": false,
    "has_active_couple": false,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Username/Email atau password salah"
}
```

---

### Logout

Invalidate the current access token.

**Endpoint:** `POST /api/logout`

**Auth Required:** Yes

**Request Body:** None

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logout berhasil."
}
```

---

### Get Current User

Get the authenticated user's information.

**Endpoint:** `GET /api/user`

**Auth Required:** Yes

**Success Response (200):**
```json
{
  "id": 1,
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "avatar_url": null,
  "timezone": "Asia/Jakarta",
  "has_couple": false,
  "has_active_couple": false,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

---

## Profile Endpoints

### Get Profile

Get the authenticated user's profile.

**Endpoint:** `GET /api/profile`

**Auth Required:** Yes

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "avatar_url": null,
    "timezone": "Asia/Jakarta",
    "couple_id": null,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### Update Profile

Update the user's profile information.

**Endpoint:** `PUT /api/profile`

**Auth Required:** Yes

**Request Body:**
```json
{
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "timezone": "Asia/Jakarta"
}
```

**Validation Rules:**
- `name`: required, string, max 255
- `username`: required, string, max 255, regex `/^[a-zA-Z0-9_]+$/`, unique (ignore current)
- `email`: required, email, unique (ignore current)
- `timezone`: optional, timezone

**Success Response (200):**
```json
{
  "success": true,
  "message": "Profil berhasil diperbarui!",
  "data": { ... }
}
```

---

### Update Password

Update the user's password.

**Endpoint:** `PUT /api/profile/password`

**Auth Required:** Yes

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Validation Rules:**
- `current_password`: required
- `password`: required, min 6, confirmed

**Success Response (200):**
```json
{
  "success": true,
  "message": "Password berhasil diperbarui!"
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Password saat ini salah.",
  "errors": {
    "current_password": ["Password saat ini salah."]
  }
}
```

---

### Update Avatar

Upload a new profile picture.

**Endpoint:** `POST /api/profile/avatar`

**Auth Required:** Yes

**Content-Type:** `multipart/form-data`

**Request Body:**
```
avatar: (file) - Image file (jpeg, png, jpg, gif, webp), max 2MB
```

**Validation Rules:**
- `avatar`: required, image, mimes: jpeg,png,jpg,gif,webp, max 2048 KB

**Success Response (200):**
```json
{
  "success": true,
  "message": "Foto profil berhasil diperbarui!",
  "data": {
    "avatar_url": "/storage/avatars/filename.jpg"
  }
}
```

---

### Remove Avatar

Remove the profile picture.

**Endpoint:** `POST /api/profile/avatar/remove`

**Auth Required:** Yes

**Success Response (200):**
```json
{
  "success": true,
  "message": "Foto profil berhasil dihapus!",
  "data": {
    "avatar_url": null
  }
}
```

---

## Pairing Endpoints

### Create Invite Code

Create an invite code to pair with a partner.

**Endpoint:** `POST /api/pairing/create-invite`

**Auth Required:** Yes

**Rate Limit:** 3 requests per 10 minutes

**Request Body:** None

**Success Response (201):**
```json
{
  "success": true,
  "message": "Kode undangan berhasil dibuat.",
  "data": {
    "id": 1,
    "invite_code": "ABCD1234",
    "status": "pending",
    "user_one": { ... },
    "user_two": null,
    "both_confirmed": false,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### Join Couple

Join a couple using an invite code.

**Endpoint:** `POST /api/pairing/join`

**Auth Required:** Yes

**Rate Limit:** 5 requests per 10 minutes

**Request Body:**
```json
{
  "invite_code": "ABCD1234"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Berhasil bergabung! Tunggu konfirmasi dari pasangan.",
  "data": {
    "id": 1,
    "status": "pending",
    "invite_code": "ABCD1234",
    "user_one": { ... },
    "user_two": { ... },
    "both_confirmed": false
  }
}
```

---

### Get Pairing Status

Get the current pairing status.

**Endpoint:** `GET /api/pairing/status`

**Auth Required:** Yes

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "has_couple": true,
    "status": "active",
    "is_confirmed": true,
    "partner": {
      "id": 2,
      "name": "Jane Doe",
      "username": "janedoe",
      "email": "jane@example.com",
      "avatar_url": null,
      "timezone": "Asia/Jakarta"
    }
  }
}
```

---

### Confirm Pairing

Confirm and activate the pairing.

**Endpoint:** `POST /api/pairing/confirm`

**Auth Required:** Yes

**Request Body:**
```json
{
  "couple_id": 1
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Pairing berhasil! Selamat datang.",
  "data": {
    "id": 1,
    "status": "active",
    "user_one": { ... },
    "user_two": { ... },
    "both_confirmed": true
  }
}
```

---

### Leave Couple

Leave the current couple (unpair).

**Endpoint:** `POST /api/pairing/leave`

**Auth Required:** Yes

**Success Response (200):**
```json
{
  "success": true,
  "message": "Pasangan telah dihapus. Kedua user sekarang sudah terunpair."
}
```

---

### Get Couple Details

Get couple information.

**Endpoint:** `GET /api/pairing`

**Auth Required:** Yes

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "active",
    "user_one": { ... },
    "user_two": { ... },
    "both_confirmed": true
  }
}
```

---

## Meetings Endpoints

All meeting endpoints require the user to be paired with a partner (`belongs.to.couple` middleware).

### List Meetings

Get all meetings with optional filters.

**Endpoint:** `GET /api/meetings/index`

**Auth Required:** Yes | Couple Required: Yes

**Query Parameters:**
- `traveler_name` (optional): Filter by traveler name
- `location` (optional): Filter by location
- `meeting_date` (optional): Filter by meeting date

**Success Response (200):**
```json
{
  "meetings": [
    {
      "id": 1,
      "travelling_user_id": 1,
      "location": "Bali",
      "start_date": "2024-02-01",
      "end_date": "2024-02-05",
      "note": "Vacation time!",
      "is_departure_transport_ready": false,
      "is_return_transport_ready": false,
      "is_rest_place_ready": false,
      "travels": [ ... ],
      "count_data_need_update": 0
    }
  ],
  "user": { ... }
}
```

---

### Get Meeting Countdown

Get countdown to the next upcoming meeting.

**Endpoint:** `GET /api/meetings/countdown`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "next_meeting": { ... },
    "days_remaining": 30,
    "hours_remaining": 720
  }
}
```

---

### Get Meeting Analytics

Get meeting statistics and analytics.

**Endpoint:** `GET /api/meetings/analytics`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "total_meetings": 10,
    "completed_meetings": 5,
    "upcoming_meetings": 2,
    "most_visited_location": "Bali"
  }
}
```

---

### Get Meeting Details

Get a specific meeting by ID.

**Endpoint:** `GET /api/meetings/show/{meetingId}`

**Auth Required:** Yes | Couple Required: Yes

**URL Parameters:**
- `meetingId`: The ID of the meeting

**Success Response (200):**
```json
{
  "meeting": { ... },
  "travels": [ ... ],
  "travelling_user": { ... }
}
```

---

### Create Meeting

Create a new meeting.

**Endpoint:** `POST /api/meetings/store`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "location": "Bali",
  "start_date": "2024-02-01",
  "end_date": "2024-02-05",
  "note": "Vacation time!",
  "is_departure_transport_ready": false,
  "is_return_transport_ready": false,
  "is_rest_place_ready": false
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Meeting created successfully.",
  "data": { ... }
}
```

---

### Update Meeting

Update an existing meeting.

**Endpoint:** `PUT /api/meetings/update/{meetingId}`

**Auth Required:** Yes | Couple Required: Yes

**URL Parameters:**
- `meetingId`: The ID of the meeting

**Request Body:**
```json
{
  "location": "Bali",
  "start_date": "2024-02-01",
  "end_date": "2024-02-05",
  "note": "Updated note",
  "is_departure_transport_ready": true,
  "is_return_transport_ready": true,
  "is_rest_place_ready": true
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Meeting updated successfully.",
  "data": { ... }
}
```

---

### Delete Meeting

Delete a meeting.

**Endpoint:** `DELETE /api/meetings/destroy/{meetingId}`

**Auth Required:** Yes | Couple Required: Yes

**URL Parameters:**
- `meetingId`: The ID of the meeting

**Success Response (200):**
```json
{
  "success": true,
  "message": "Meeting deleted successfully."
}
```

---

### Get Meeting Feedback

Get feedback for a specific meeting.

**Endpoint:** `GET /api/meetings/{meetingId}/feedback`

**Auth Required:** Yes | Couple Required: Yes

**URL Parameters:**
- `meetingId`: The ID of the meeting

**Success Response (200):**
```json
{
  "success": true,
  "data": [ ... ]
}
```

---

### Submit Meeting Feedback

Submit feedback for a meeting.

**Endpoint:** `POST /api/meetings/{meetingId}/feedback`

**Auth Required:** Yes | Couple Required: Yes

**URL Parameters:**
- `meetingId`: The ID of the meeting

**Request Body:**
```json
{
  "rating": 5,
  "comment": "Great meeting!"
}
```

---

### Check if Can Give Feedback

Check if the user can give feedback for a meeting.

**Endpoint:** `GET /api/meetings/{meetingId}/feedback/can-give`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "can_give": true
}
```

---

## Travels Endpoints

All travel endpoints require the user to be paired with a partner.

### List Travels

Get all travels with optional filters.

**Endpoint:** `GET /api/travels/index`

**Auth Required:** Yes | Couple Required: Yes

**Query Parameters:**
- `destination` (optional): Filter by destination
- `visit_date` (optional): Filter by visit date
- `completed` (optional): Filter by completion status (true/false)

**Success Response (200):**
```json
{
  "success": true,
  "travels": [
    {
      "id": 1,
      "meeting_id": 1,
      "destination": "Ubud",
      "visit_date": "2024-02-02",
      "completed": false
    }
  ]
}
```

---

### Get Travel Analytics

Get travel statistics and analytics.

**Endpoint:** `GET /api/travels/analytics`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "total_travels": 20,
    "completed_travels": 15,
    "pending_travels": 5
  }
}
```

---

### Get Travel Details

Get a specific travel by ID.

**Endpoint:** `GET /api/travels/show/{travelId}`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "travel": { ... }
}
```

---

### Create Travel

Create a new travel plan.

**Endpoint:** `POST /api/travels/store`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "meeting_id": 1,
  "destination": "Ubud",
  "completed": false
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Travel created successfully."
}
```

---

### Update Travel

Update an existing travel.

**Endpoint:** `PUT /api/travels/update/{travelId}`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "meeting_id": 1,
  "destination": "Ubud",
  "completed": false
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Travel updated successfully."
}
```

---

### Delete Travel

Delete a travel.

**Endpoint:** `DELETE /api/travels/destroy/{travelId}`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "message": "Travel deleted successfully."
}
```

---

### Assign Travel to Meeting

Assign a travel plan to a meeting.

**Endpoint:** `POST /api/travels/assign-to-meeting/{meetingId}`

**Auth Required:** Yes | Couple Required: Yes

**URL Parameters:**
- `meetingId`: The ID of the meeting

**Request Body:**
```json
{
  "travel_id": 1,
  "visit_date": "2024-02-02"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Travel Planner berhasil di-assign ke Meeting."
}
```

---

### Complete Travel

Mark a travel as completed.

**Endpoint:** `PATCH /api/travels/complete-travel/{travelId}`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "message": "Travel Planner berhasil diselesaikan."
}
```

---

### Remove Travel from Meeting

Remove a travel from its assigned meeting.

**Endpoint:** `PATCH /api/travels/remove-from-meeting/{travelId}`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "message": "Travel Planner berhasil dihapus dari Meeting."
}
```

---

### Get Unassigned Travels

Get all travels that are not assigned to any meeting.

**Endpoint:** `GET /api/travels/get-unassigned-travels`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "data": [ ... ]
}
```

---

### Update Visit Date

Update the visit date for a travel.

**Endpoint:** `PUT /api/travels/update-visit-date/{travelId}`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "visit_date": "2024-02-03"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Visit Date berhasil diubah."
}
```

---

## Travel Photos Endpoints

### List Travel Photos

Get all photos for a travel.

**Endpoint:** `GET /api/travels/{travelId}/photos`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "data": [ ... ]
}
```

---

### Upload Travel Photo

Upload a single photo for a travel.

**Endpoint:** `POST /api/travels/{travelId}/photos`

**Auth Required:** Yes | Couple Required: Yes

**Content-Type:** `multipart/form-data`

**Request Body:**
```
photo: (file) - Image file
caption: (optional) - Photo caption
```

---

### Upload Multiple Photos

Upload multiple photos for a travel.

**Endpoint:** `POST /api/travels/{travelId}/photos/multiple`

**Auth Required:** Yes | Couple Required: Yes

**Content-Type:** `multipart/form-data`

**Request Body:**
```
photos[]: (files) - Array of image files
```

---

### Update Photo

Update a travel photo (caption, etc.).

**Endpoint:** `PUT /api/travels/photos/{photoId}`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "caption": "Updated caption"
}
```

---

### Delete Photo

Delete a travel photo.

**Endpoint:** `DELETE /api/travels/photos/{photoId}`

**Auth Required:** Yes | Couple Required: Yes

---

### Update Photo Order

Update the order of photos.

**Endpoint:** `POST /api/travels/photos/order`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "photos": [
    { "id": 1, "order": 1 },
    { "id": 2, "order": 2 }
  ]
}
```

---

## Travel Journals Endpoints

### List All Journals

Get all travel journals.

**Endpoint:** `GET /api/journals`

**Auth Required:** Yes | Couple Required: Yes

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page

---

### Create Journal

Create a new travel journal.

**Endpoint:** `POST /api/journals`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "title": "Amazing Day in Bali",
  "content": "Today we visited...",
  "travel_id": 1,
  "mood": "happy",
  "weather": "sunny"
}
```

---

### Get Journal Details

Get a specific journal.

**Endpoint:** `GET /api/journals/{journalId}`

**Auth Required:** Yes | Couple Required: Yes

---

### Update Journal

Update a journal.

**Endpoint:** `PUT /api/journals/{journalId}`

**Auth Required:** Yes | Couple Required: Yes

---

### Delete Journal

Delete a journal.

**Endpoint:** `DELETE /api/journals/{journalId}`

**Auth Required:** Yes | Couple Required: Yes

---

### Toggle Favorite

Toggle favorite status of a journal.

**Endpoint:** `POST /api/journals/{journalId}/favorite`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Journals by Travel

Get all journals for a specific travel.

**Endpoint:** `GET /api/travels/{travelId}/journals`

**Auth Required:** Yes | Couple Required: Yes

---

## Savings Endpoints

### List Savings

Get all savings with optional category filter.

**Endpoint:** `GET /api/savings/index`

**Auth Required:** Yes | Couple Required: Yes

**Query Parameters:**
- `category` (optional): Filter by category name

**Success Response (200):**
```json
{
  "success": true,
  "savings": [ ... ],
  "categoryData": { ... },
  "categories": [ ... ],
  "selectedCategory": "vacation"
}
```

---

### Get Saving Details

Get a specific saving with its transactions.

**Endpoint:** `GET /api/savings/show/{savingId}`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "saving": { ... },
  "transactions": [ ... ]
}
```

---

### Create Saving

Create a new saving goal.

**Endpoint:** `POST /api/savings/store`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "name": "Vacation Fund",
  "target_amount": 5000000,
  "target_date": "2024-12-31",
  "is_shared": true
}
```

---

### Update Saving

Update a saving goal.

**Endpoint:** `PUT /api/savings/update/{savingId}`

**Auth Required:** Yes | Couple Required: Yes

---

### Delete Saving

Delete a saving goal.

**Endpoint:** `DELETE /api/savings/destroy/{savingId}`

**Auth Required:** Yes | Couple Required: Yes

---

### Add Transaction

Add a transaction (deposit/withdraw) to a saving.

**Endpoint:** `POST /api/savings/{savingId}/transactions`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "amount": 100000,
  "type": "deposit",
  "note": "Monthly savings"
}
```

---

### Transfer Savings

Transfer amount between savings.

**Endpoint:** `POST /api/savings/transfer`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "source_saving_id": 1,
  "target_saving_id": 2,
  "amount": 50000
}
```

---

### Get Upcoming Deadlines

Get savings with upcoming target deadlines.

**Endpoint:** `GET /api/savings/upcoming-deadlines`

**Auth Required:** Yes | Couple Required: Yes

**Query Parameters:**
- `days` (optional): Number of days to look ahead (default: 7)

---

### Get Overdue Savings

Get savings that have passed their target date.

**Endpoint:** `GET /api/savings/overdue`

**Auth Required:** Yes | Couple Required: Yes

---

### Mark as Completed

Mark a saving as completed.

**Endpoint:** `POST /api/savings/{savingId}/mark-completed`

**Auth Required:** Yes | Couple Required: Yes

---

## Saving Categories Endpoints

### List Categories

Get all saving categories.

**Endpoint:** `GET /api/saving-categories`

**Auth Required:** Yes | Couple Required: Yes

---

### Create Category

Create a new saving category.

**Endpoint:** `POST /api/saving-categories`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "name": "Emergency Fund",
  "icon": "💰",
  "color": "#FF5733"
}
```

---

### Update Category

Update a saving category.

**Endpoint:** `PUT /api/saving-categories/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

### Delete Category

Delete a saving category.

**Endpoint:** `DELETE /api/saving-categories/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

## Recurring Savings Endpoints

### List Recurring Savings

Get all recurring savings.

**Endpoint:** `GET /api/recurring-savings`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Recurring Stats

Get statistics for recurring savings.

**Endpoint:** `GET /api/recurring-savings/stats`

**Auth Required:** Yes | Couple Required: Yes

---

### Create Recurring Saving

Create a new recurring saving.

**Endpoint:** `POST /api/recurring-savings`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "name": "Monthly Savings",
  "amount": 100000,
  "frequency": "monthly",
  "start_date": "2024-01-01"
}
```

---

### Pause Recurring Saving

Pause a recurring saving.

**Endpoint:** `POST /api/recurring-savings/{id}/pause`

**Auth Required:** Yes | Couple Required: Yes

---

### Resume Recurring Saving

Resume a paused recurring saving.

**Endpoint:** `POST /api/recurring-savings/{id}/resume`

**Auth Required:** Yes | Couple Required: Yes

---

### Skip Next Cycle

Skip the next recurring saving cycle.

**Endpoint:** `POST /api/recurring-savings/{id}/skip`

**Auth Required:** Yes | Couple Required: Yes

---

## Savings Analytics Endpoints

### Get Overview

Get savings overview analytics.

**Endpoint:** `GET /api/savings-analytics/overview`

**Auth Required:** Yes | Couple Required: Yes

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "total_savings": 15000000,
    "total_target": 50000000,
    "completion_percentage": 30
  }
}
```

---

### Get Trends

Get savings trends over time.

**Endpoint:** `GET /api/savings-analytics/trends`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Goals

Get savings goals progress.

**Endpoint:** `GET /api/savings-analytics/goals`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Growth

Get savings growth analytics.

**Endpoint:** `GET /api/savings-analytics/growth`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Categories

Get savings by category analytics.

**Endpoint:** `GET /api/savings-analytics/categories`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Upcoming

Get upcoming savings deadlines.

**Endpoint:** `GET /api/savings-analytics/upcoming`

**Auth Required:** Yes | Couple Required: Yes

---

### Compare

Get savings comparison with partner.

**Endpoint:** `GET /api/savings-analytics/compare`

**Auth Required:** Yes | Couple Required: Yes

---

### Export

Export savings data.

**Endpoint:** `GET /api/savings-analytics/export`

**Auth Required:** Yes | Couple Required: Yes

---

## Savings Comparison Endpoints

### Get Comparison Overview

Get overview comparison with partner.

**Endpoint:** `GET /api/savings-comparison/overview`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Savings List Comparison

Get side-by-side savings list comparison.

**Endpoint:** `GET /api/savings-comparison/savings-list`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Monthly Contributions

Compare monthly contributions with partner.

**Endpoint:** `GET /api/savings-comparison/monthly-contributions`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Categories Comparison

Compare savings categories with partner.

**Endpoint:** `GET /api/savings-comparison/categories`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Goals Comparison

Compare savings goals with partner.

**Endpoint:** `GET /api/savings-comparison/goals`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Achievements

Get joint and individual achievements.

**Endpoint:** `GET /api/savings-comparison/achievements`

**Auth Required:** Yes | Couple Required: Yes

---

## Timeline Endpoints

### Get Timeline Feed

Get the timeline feed with pagination.

**Endpoint:** `GET /api/timeline/index`

**Auth Required:** Yes | Couple Required: Yes

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15)

**Success Response (200):**
```json
{
  "success": true,
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "per_page": 15,
    "total": 75
  }
}
```

---

### Get Post Details

Get a specific timeline post.

**Endpoint:** `GET /api/timeline/show/{postId}`

**Auth Required:** Yes | Couple Required: Yes

---

### Create Post

Create a new timeline post.

**Endpoint:** `POST /api/timeline/store`

**Auth Required:** Yes | Couple Required: Yes

**Content-Type:** `multipart/form-data` (if attachment)

**Request Body:**
```json
{
  "post_type": "text",
  "content": "Today was amazing!",
  "attachment": (file, optional)
}
```

---

### Update Post

Update a timeline post.

**Endpoint:** `POST /api/timeline/update/{postId}`

**Auth Required:** Yes | Couple Required: Yes

---

### Delete Post

Delete a timeline post.

**Endpoint:** `DELETE /api/timeline/destroy/{postId}`

**Auth Required:** Yes | Couple Required: Yes

---

### React to Post

Add or toggle reaction on a post.

**Endpoint:** `POST /api/timeline/react/{postId}`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "emoji": "❤️"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Reaksi berhasil ditambahkan.",
  "data": {
    "removed": false,
    "reactions": [ ... ]
  }
}
```

---

### Remove Reaction

Remove reaction from a post.

**Endpoint:** `DELETE /api/timeline/unreact/{postId}`

**Auth Required:** Yes | Couple Required: Yes

---

### Comment on Post

Add a comment to a post.

**Endpoint:** `POST /api/timeline/comment/{postId}`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "content": "Great post!"
}
```

---

### Get Post Comments

Get comments for a post.

**Endpoint:** `GET /api/timeline/comments/{postId}`

**Auth Required:** Yes | Couple Required: Yes

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)

---

### Delete Comment

Delete a comment.

**Endpoint:** `DELETE /api/timeline/comment/{commentId}`

**Auth Required:** Yes | Couple Required: Yes

---

## Mood Check-In Endpoints

### List Moods

Get all mood check-ins.

**Endpoint:** `GET /api/mood`

**Auth Required:** Yes | Couple Required: Yes

---

### Check In

Submit a daily mood check-in.

**Endpoint:** `POST /api/mood`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "mood": "happy",
  "note": "Had a great day!",
  "activities": ["work", "exercise"]
}
```

---

### Get Today's Mood

Get today's mood check-in.

**Endpoint:** `GET /api/mood/today`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Mood Stats

Get mood statistics.

**Endpoint:** `GET /api/mood/stats`

**Auth Required:** Yes | Couple Required: Yes

---

### Update Mood

Update a mood check-in.

**Endpoint:** `PUT /api/mood/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

### Delete Mood

Delete a mood check-in.

**Endpoint:** `DELETE /api/mood/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

## Missing You Endpoints

### List Missing You Messages

Get all "Missing You" messages.

**Endpoint:** `GET /api/missing-you`

**Auth Required:** Yes | Couple Required: Yes

---

### Send Missing You

Send a "Missing You" message.

**Endpoint:** `POST /api/missing-you`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "message": "I miss you so much!",
  "template_id": 1
}
```

---

### Get Status

Get the current "Missing You" status.

**Endpoint:** `GET /api/missing-you/status`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Templates

Get available "Missing You" templates.

**Endpoint:** `GET /api/missing-you/templates`

**Auth Required:** Yes | Couple Required: Yes

---

## Question of the Day Endpoints

### List Questions

Get all questions.

**Endpoint:** `GET /api/questions`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Today's Question

Get today's question.

**Endpoint:** `GET /api/questions/today`

**Auth Required:** Yes | Couple Required: Yes

---

### Answer Question

Submit an answer to a question.

**Endpoint:** `POST /api/questions/answer`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "question_id": 1,
  "answer": "My answer here..."
}
```

---

### Update Answer

Update an existing answer.

**Endpoint:** `PUT /api/questions/answer`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "answer_id": 1,
  "answer": "Updated answer..."
}
```

---

### Get Question Stats

Get question statistics.

**Endpoint:** `GET /api/questions/stats`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Categories

Get question categories.

**Endpoint:** `GET /api/questions/categories`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Answer Modes

Get available answer modes.

**Endpoint:** `GET /api/questions/answer-modes`

**Auth Required:** Yes | Couple Required: Yes

---

### Set Answer Mode

Set the answer mode preference.

**Endpoint:** `POST /api/questions/answer-mode`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "mode": "private"
}
```

---

### Get Question by Date

Get a question for a specific date.

**Endpoint:** `GET /api/questions/{date}`

**Auth Required:** Yes | Couple Required: Yes

**URL Parameters:**
- `date`: Date in Y-m-d format (e.g., "2024-01-15")

---

## Goals Endpoints

### List Goals

Get all goals.

**Endpoint:** `GET /api/goals`

**Auth Required:** Yes | Couple Required: Yes

---

### Create Goal

Create a new goal.

**Endpoint:** `POST /api/goals`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "title": "Run a marathon",
  "description": "Complete a full marathon",
  "target_date": "2024-12-31",
  "category": "health"
}
```

---

### Get Goal Stats

Get goal statistics.

**Endpoint:** `GET /api/goals/stats`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Upcoming Goals

Get upcoming goals.

**Endpoint:** `GET /api/goals/upcoming`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Goal Details

Get a specific goal.

**Endpoint:** `GET /api/goals/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

### Update Goal

Update a goal.

**Endpoint:** `PUT /api/goals/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

### Delete Goal

Delete a goal.

**Endpoint:** `DELETE /api/goals/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

### Mark as Completed

Mark a goal as completed.

**Endpoint:** `POST /api/goals/{id}/mark-completed`

**Auth Required:** Yes | Couple Required: Yes

---

### Mark as In Progress

Mark a goal as in progress.

**Endpoint:** `POST /api/goals/{id}/mark-in-progress`

**Auth Required:** Yes | Couple Required: Yes

---

## Tasks Endpoints

### List Tasks

Get all tasks.

**Endpoint:** `GET /api/tasks`

**Auth Required:** Yes | Couple Required: Yes

---

### Create Task

Create a new task.

**Endpoint:** `POST /api/tasks`

**Auth Required:** Yes | Couple Required: Yes

**Request Body:**
```json
{
  "title": "Buy groceries",
  "description": "Milk, eggs, bread",
  "due_date": "2024-01-15",
  "priority": "medium",
  "assigned_to": "user_id"
}
```

---

### Get Pending Tasks

Get all pending/incomplete tasks.

**Endpoint:** `GET /api/tasks/pending`

**Auth Required:** Yes | Couple Required: Yes

---

### Get My Tasks

Get tasks assigned to the current user.

**Endpoint:** `GET /api/tasks/my-tasks`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Task Stats

Get task statistics.

**Endpoint:** `GET /api/tasks/stats`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Task Details

Get a specific task.

**Endpoint:** `GET /api/tasks/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

### Update Task

Update a task.

**Endpoint:** `PUT /api/tasks/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

### Toggle Task

Toggle task completion status.

**Endpoint:** `POST /api/tasks/toggle/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

### Delete Task

Delete a task.

**Endpoint:** `DELETE /api/tasks/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

## Notifications Endpoints

### List Notifications

Get all notifications for the current user.

**Endpoint:** `GET /api/notifications`

**Auth Required:** Yes | Couple Required: Yes

---

### Get Unread Count

Get count of unread notifications.

**Endpoint:** `GET /api/notifications/unread-count`

**Auth Required:** Yes | Couple Required: Yes

---

### Mark as Read

Mark a notification as read.

**Endpoint:** `POST /api/notifications/{id}/mark-read`

**Auth Required:** Yes | Couple Required: Yes

---

### Mark All as Read

Mark all notifications as read.

**Endpoint:** `POST /api/notifications/mark-all-read`

**Auth Required:** Yes | Couple Required: Yes

---

### Delete Notification

Delete a notification.

**Endpoint:** `DELETE /api/notifications/{id}`

**Auth Required:** Yes | Couple Required: Yes

---

## Health Check

### Health Check

Check if the API is running.

**Endpoint:** `GET /api/health`

**Auth Required:** No

**Success Response (200):**
```json
{
  "status": "ok",
  "message": "API is running",
  "timestamp": "2024-01-01T00:00:00.000000Z",
  "version": "10.x.x"
}
```

---

## Important Notes

### Rate Limiting

Some endpoints have rate limiting:
- `/api/pairing/create-invite`: 3 requests per 10 minutes
- `/api/pairing/join`: 5 requests per 10 minutes

When rate limited, you'll receive a 429 status code.

### File Uploads

For file uploads (avatar, photos, attachments):
- Use `multipart/form-data` content type
- Max file size: 2MB for avatars
- Supported formats: JPEG, PNG, JPG, GIF, WEBP

### Couple Requirement

Most endpoints require the user to be paired with a partner. If not paired, you'll receive:
```json
{
  "success": false,
  "message": "Anda harus terhubung dengan pasangan terlebih dahulu."
}
```

### Token Management

- Tokens do not expire by default (can be changed in config)
- Store tokens securely
- Implement token refresh if needed
- Call `/api/logout` when user logs out to invalidate token

### Pagination

For paginated endpoints:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default varies by endpoint)

Response includes:
```json
{
  "pagination": {
    "current_page": 1,
    "total_pages": 10,
    "per_page": 15,
    "total": 150
  }
}
```

---

## Support

For issues or questions, please contact the development team.
