# 📱 Our Plan Mobile App - Complete Revamp Guide

> **Project Context:** Couple relationship management app for long-distance relationships (LDR)
> **Tech Stack:** Ionic + Angular + TypeScript
> **Backend:** Laravel API (already implemented)
> **Goal:** Complete UI/UX revamp with modern design, push notifications, and seamless navigation

---

## 🎯 PROJECT OVERVIEW

### Application Purpose
Our Plan is a comprehensive couple relationship management platform designed primarily for long-distance relationships. It helps couples stay connected through various interactive features including meeting planning, savings tracking, daily questions, mood check-ins, travel journals, and more.

### Current State
- ✅ Backend API is 100% complete (all endpoints implemented)
- ✅ Existing mobile codebase exists (Ionic Angular)
- ❌ UI needs modern revamp
- ❌ Navigation needs improvement
- ❌ Push notification not implemented
- ❌ Authentication persistence needs work

---

## 🚀 PRIMARY OBJECTIVES

### 1. **Modern UI/UX Design**
- Implement contemporary, visually appealing interface
- Ensure smooth animations and transitions
- Create consistent design system across all screens
- Optimize for both iOS and Android platforms
- Use modern color schemes, typography, and spacing

### 2. **Complete Feature Integration**
- ALL backend features must be accessible via mobile
- No missing or half-implemented features
- All 22+ modules fully functional

### 3. **Seamless Navigation**
- Proper navigation stack management
- Handle hardware back button correctly
- Smooth screen transitions
- Tab-based navigation with clear hierarchy
- Deep linking support

### 4. **Push Notifications**
- Real-time notifications for important events
- Permission handling
- Notification categorization
- Tap-to-navigate to relevant screens

### 5. **Persistent Authentication**
- Secure token storage
- Auto-login on app launch
- Session management
- Handle token refresh

---

## 📋 COMPLETE FEATURE LIST (Must Implement All)

### 🔐 Authentication & User Management
```
✅ User registration (email, username, password)
✅ User login
✅ Auto-login (remember me)
✅ Logout with proper cleanup
✅ Profile management (name, username, email, timezone)
✅ Password change
✅ Avatar upload/remove
```

### 💑 Couple Pairing System
```
✅ Create invite code (6-digit)
✅ Join with invite code
✅ Pairing status tracking
✅ Confirm pairing (two-step process)
✅ Leave couple
✅ View couple info
```

### 📅 Meetings Management
```
✅ List all meetings
✅ View meeting details
✅ Create new meeting
✅ Edit meeting
✅ Delete meeting
✅ Countdown to next meeting
✅ Meeting analytics
✅ Transport readiness tracking
✅ Rest place readiness
✅ Meeting notes
```

### ⭐ Meeting Feedback
```
✅ View feedback for meeting
✅ Submit feedback (1-5 stars + comment)
✅ Update own feedback
✅ Delete own feedback
✅ Check if can give feedback
```

### 🌍 Travels Management
```
✅ List all travels
✅ View travel details
✅ Create travel
✅ Edit travel
✅ Delete travel
✅ Assign to meeting
✅ Complete travel
✅ Remove from meeting
✅ Update visit date
✅ Get unassigned travels
✅ Travel analytics
```

### 📸 Travel Photos
```
✅ List photos for travel
✅ Upload single photo
✅ Upload multiple photos
✅ Update photo caption
✅ Delete photo
✅ Reorder photos
```

### 📝 Travel Journals
```
✅ List all journals
✅ View journal details
✅ Create journal (title, content, date, mood, weather, location)
✅ Edit journal
✅ Delete journal
✅ Toggle favorite
✅ View journals by travel
```

### 💰 Savings Management
```
✅ List all savings
✅ View saving details
✅ Create saving
✅ Edit saving
✅ Delete saving
✅ Add transaction
✅ Transfer between savings
✅ Mark as completed
✅ Upcoming deadlines
✅ Overdue savings
```

### 📁 Saving Categories
```
✅ List categories
✅ Create category (with icon & color)
✅ Update category
✅ Delete category
```

### 🔄 Recurring Savings
```
✅ List recurring savings
✅ View stats
✅ Create recurring (daily, weekly, biweekly, monthly)
✅ Update recurring
✅ Delete recurring
✅ Pause recurring
✅ Resume recurring
✅ Skip next deposit
```

### 📊 Savings Analytics
```
✅ Overview statistics
✅ Trend analysis
✅ Goal tracking
✅ Growth metrics
✅ Category breakdown
✅ Upcoming targets
✅ Partner comparison
✅ Export data
```

### 📈 Savings Comparison
```
✅ Overview comparison
✅ Savings list comparison
✅ Monthly contributions comparison
✅ Categories comparison
✅ Goals comparison
✅ Achievements comparison
```

### 📱 Timeline / Social Feed
```
✅ List posts (pagination)
✅ View post details
✅ Create post (text, photo, voice note)
✅ Edit post
✅ Delete post
✅ React to post (emoji)
✅ Remove reaction
✅ Comment on post
✅ View comments
✅ Delete comment
```

### 😊 Daily Mood Check-In
```
✅ List mood history
✅ Get today's mood
✅ Check in mood (happy, sad, angry, loved, tired, anxious, excited)
✅ Update mood
✅ Mood statistics
✅ Delete mood entry
```

### 💕 Missing You Feature
```
✅ List messages
✅ Send missing you message
✅ Get status
✅ Get message templates
```

### ❓ Daily Questions
```
✅ List questions
✅ Get today's question
✅ Answer question
✅ Update answer
✅ Get statistics
✅ Get categories
✅ Get answer modes
✅ Set answer mode
✅ Get question by date
```

### 🎯 Couple Goals
```
✅ List goals
✅ View goal details
✅ Create goal
✅ Edit goal
✅ Delete goal
✅ Get statistics
✅ Get upcoming goals
✅ Mark as completed
✅ Mark as in progress
```

### ✅ Couple Tasks
```
✅ List tasks
✅ View task details
✅ Create task
✅ Edit task
✅ Delete task
✅ Toggle completion
✅ Get pending tasks
✅ Get my tasks
✅ Get statistics
```

### 🔔 Notifications
```
✅ List notifications (pagination)
✅ Get unread count
✅ Mark as read
✅ Mark all as read
✅ Delete notification
✅ PUSH NOTIFICATIONS (Real-time)
```

---

## 🔌 API BASE URL & ENDPOINTS

### Base URL
```typescript
// Development
const API_BASE_URL = 'https://your-api-domain.com/api';

// Or from environment
const API_BASE_URL = environment.apiUrl;
```

### Authentication Header
```typescript
// All requests (except login/register) need:
headers: {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

### Key API Endpoints Reference

#### Auth Endpoints
```
POST   /api/register                  - Register new user
POST   /api/login                     - Login user
POST   /api/logout                    - Logout user
GET    /api/user                      - Get current user info
```

#### Profile Endpoints
```
GET    /api/profile                   - Get profile
PUT    /api/profile                   - Update profile
PUT    /api/profile/password          - Change password
POST   /api/profile/avatar            - Upload avatar
POST   /api/profile/avatar/remove     - Remove avatar
```

#### Pairing Endpoints
```
POST   /api/pairing/create-invite     - Create invite code
POST   /api/pairing/join              - Join with code
GET    /api/pairing/status            - Get pairing status
POST   /api/pairing/confirm           - Confirm pairing
POST   /api/pairing/leave             - Leave couple
GET    /api/pairing/                  - Get couple info
```

#### Meetings Endpoints
```
GET    /api/meetings/index            - List meetings
GET    /api/meetings/countdown        - Get countdown
GET    /api/meetings/analytics        - Get analytics
GET    /api/meetings/show/{id}        - Get details
POST   /api/meetings/store            - Create meeting
PUT    /api/meetings/update/{id}      - Update meeting
DELETE /api/meetings/destroy/{id}     - Delete meeting
```

#### Travels Endpoints
```
GET    /api/travels/index             - List travels
GET    /api/travels/analytics         - Get analytics
GET    /api/travels/show/{id}         - Get details
POST   /api/travels/store             - Create travel
PUT    /api/travels/update/{id}       - Update travel
DELETE /api/travels/destroy/{id}      - Delete travel
POST   /api/travels/assign-to-meeting/{id}  - Assign to meeting
PATCH  /api/travels/complete-travel/{id}    - Mark complete
PATCH  /api/travels/remove-from-meeting/{id} - Remove from meeting
PUT    /api/travels/update-visit-date/{id}   - Update visit date
```

#### Travel Photos Endpoints
```
GET    /api/travels/{id}/photos              - List photos
POST   /api/travels/{id}/photos              - Upload photo
POST   /api/travels/{id}/photos/multiple     - Upload multiple
PUT    /api/travels/photos/{id}              - Update caption
DELETE /api/travels/photos/{id}              - Delete photo
POST   /api/travels/photos/order             - Reorder photos
```

#### Travel Journals Endpoints
```
GET    /api/journals                         - List journals
GET    /api/journals/{id}                    - Get details
POST   /api/journals                         - Create journal
PUT    /api/journals/{id}                    - Update journal
DELETE /api/journals/{id}                    - Delete journal
POST   /api/journals/{id}/favorite           - Toggle favorite
GET    /api/travels/{id}/journals            - Get by travel
```

#### Savings Endpoints
```
GET    /api/savings/index                    - List savings
POST   /api/savings/store                    - Create saving
GET    /api/savings/show/{id}                - Get details
PUT    /api/savings/update/{id}              - Update saving
DELETE /api/savings/destroy/{id}             - Delete saving
POST   /api/savings/{id}/transactions        - Add transaction
POST   /api/savings/transfer                 - Transfer
GET    /api/savings/upcoming-deadlines       - Upcoming deadlines
GET    /api/savings/overdue                  - Overdue savings
POST   /api/savings/{id}/mark-completed      - Mark completed
```

#### Notifications Endpoints
```
GET    /api/notifications                    - List notifications
GET    /api/notifications/unread-count       - Unread count
POST   /api/notifications/{id}/mark-read     - Mark as read
POST   /api/notifications/mark-all-read      - Mark all read
DELETE /api/notifications/{id}               - Delete notification
```

---

## 🎨 DESIGN SYSTEM & UI GUIDELINES

### Color Palette (Recommended)
```typescript
// Primary Colors
primary: '#6C63FF',        // Main brand color
primaryDark: '#5A52D5',    // Darker variant
primaryLight: '#E8E6FF',   // Lighter variant

// Secondary Colors
secondary: '#FF6B9D',      // Accent color
secondaryDark: '#E85A8A',  // Darker variant

// Neutral Colors
background: '#F8F9FA',     // App background
surface: '#FFFFFF',        // Card/surface
textPrimary: '#2D3436',    // Main text
textSecondary: '#636E72',  // Secondary text
border: '#E0E0E0',         // Borders

// Status Colors
success: '#00B894',        // Completed/success
warning: '#FDCB6E',        // Pending/warning
danger: '#FF7675',         // Error/delete
info: '#74B9FF',           // Information

// Mood Colors
moodHappy: '#FDCB6E',
moodSad: '#74B9FF',
moodAngry: '#FF7675',
moodLoved: '#FF6B9D',
moodTired: '#A29BFE',
moodAnxious: '#DFE6E9',
moodExcited: '#00B894',
```

### Typography
```typescript
// Font Family
fontFamily: {
  ios: '-apple-system, SF Pro Display',
  android: 'Roboto, sans-serif',
  fallback: 'system-ui, sans-serif'
}

// Font Sizes
fontSize: {
  xs: '12px',
  sm: '14px',
  md: '16px',    // Base
  lg: '18px',
  xl: '20px',
  '2xl': '24px',
  '3xl': '32px',
}
```

### Spacing
```typescript
spacing: {
  xs: '4px',
  sm: '8px',
  md: '16px',
  lg: '24px',
  xl: '32px',
  '2xl': '48px',
}
```

### Border Radius
```typescript
borderRadius: {
  sm: '4px',
  md: '8px',
  lg: '16px',
  xl: '24px',
  full: '9999px',
}
```

---

## 📱 SCREEN ARCHITECTURE

### Navigation Structure

#### Main Navigation (Tabs)
```
┌─────────────────────────────────────┐
│         HOME / DASHBOARD            │
├─────────────────────────────────────┤
│         MEETINGS & TRAVELS          │
├─────────────────────────────────────┤
│         SAVINGS & GOALS             │
├─────────────────────────────────────┤
│         TIMELINE & SOCIAL           │
├─────────────────────────────────────┤
│         PROFILE & SETTINGS          │
└─────────────────────────────────────┘
```

#### Auth Flow
```
Splash Screen
    ↓
Check Auth Status
    ↓
    ├─→ No Token/Invalid → Login/Register
    │                          ↓
    │                      Pairing Flow (if needed)
    │                          ↓
    │                      Main Tabs
    │
    └─→ Valid Token → Main Tabs
```

#### Key Navigation Patterns

1. **Tab Navigation** - Main app sections
2. **Stack Navigation** - Within each tab (push/pop)
3. **Modal Navigation** - Forms, details, overlays
4. **Sheet/Bottom Sheet** - Quick actions, options
5. **Card Navigation** - Click card to view details

### Critical Screens (Must Have)

#### 1. Authentication Screens
- Splash Screen (with logo & loading)
- Login Screen
- Register Screen
- Forgot Password Screen (if needed)

#### 2. Pairing Screens
- Invite Code Screen (create/join)
- Pairing Status Screen
- Waiting for Partner Screen

#### 3. Dashboard Screen
- Quick stats overview
- Recent activities
- Upcoming meetings countdown
- Quick actions
- Notifications badge

#### 4. Meeting Screens
- Meetings List (past & upcoming)
- Meeting Detail
- Create/Edit Meeting Form
- Meeting Analytics
- Meeting Feedback

#### 5. Travel Screens
- Travels List
- Travel Detail
- Create/Edit Travel Form
- Travel Photos Gallery
- Travel Journals List
- Journal Detail/Form
- Travel Analytics

#### 6. Savings Screens
- Savings List (with progress)
- Saving Detail
- Create/Edit Saving Form
- Add Transaction
- Categories Management
- Recurring Savings List
- Savings Analytics Charts
- Savings Comparison

#### 7. Timeline/Social Screens
- Timeline Feed (infinite scroll)
- Create Post Modal
- Post Detail (with comments)
- Photo Viewer (fullscreen)

#### 8. Daily Interactions Screens
- Mood Check-In
- Daily Question
- Missing You Messages

#### 9. Goals & Tasks Screens
- Goals List
- Goal Detail
- Create/Edit Goal Form
- Tasks List
- Task Detail
- Create/Edit Task Form

#### 10. Profile & Settings Screens
- Profile Screen
- Edit Profile
- Settings
- Notifications Settings
- About/Help

---

## 🔐 AUTHENTICATION IMPLEMENTATION

### Token Storage Strategy
```typescript
// Use Ionic Secure Storage for tokens
import { SecureStorageService } from '@/services/secure-storage.service';

// Store token securely
await this.secureStorage.set('auth_token', token);
await this.secureStorage.set('refresh_token', refreshToken);

// Get token
const token = await this.secureStorage.get('auth_token');

// Clear on logout
await this.secureStorage.remove('auth_token');
await this.secureStorage.remove('refresh_token');
```

### Auto-Login Flow
```typescript
// In app.component.ts
async initializeApp() {
  await this.platform.ready();

  // Check for stored token
  const token = await this.secureStorage.get('auth_token');

  if (token) {
    try {
      // Validate token with API
      await this.authService.validateToken();
      // Navigate to main tabs
      this.router.navigate(['/tabs']);
    } catch (error) {
      // Token invalid, clear and show login
      await this.secureStorage.remove('auth_token');
      this.router.navigate(['/login']);
    }
  } else {
    // No token, show login
    this.router.navigate(['/login']);
  }
}
```

### HTTP Interceptor
```typescript
// auth.interceptor.ts
@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(private secureStorage: SecureStorageService) {}

  async intercept(req: HttpRequest<any>, next: HttpHandler): Promise<HttpEvent<any>> {
    // Get token
    const token = await this.secureStorage.get('auth_token');

    if (token) {
      // Clone request and add auth header
      const authReq = req.clone({
        setHeaders: {
          Authorization: `Bearer ${token}`
        }
      });

      return next.handle(authReq).pipe(
        catchError(async (error: HttpErrorResponse) => {
          if (error.status === 401) {
            // Token expired, attempt refresh
            const refreshed = await this.tryRefreshToken();

            if (refreshed) {
              // Retry original request
              const newToken = await this.secureStorage.get('auth_token');
              const retryReq = req.clone({
                setHeaders: {
                  Authorization: `Bearer ${newToken}`
                }
              });
              return next.handle(retryReq);
            } else {
              // Refresh failed, logout
              this.handleLogout();
            }
          }
          throw error;
        })
      );
    }

    return next.handle(req);
  }
}
```

---

## 🔔 PUSH NOTIFICATION IMPLEMENTATION

### Setup Required
```bash
# Install required packages
npm install @capacitor/push-notifications
npm install @capacitor/local-notifications
npm install @capacitor/background-task
```

### Notification Service Structure
```typescript
// notification.service.ts
import { PushNotifications } from '@capacitor/push-notifications';
import { LocalNotifications } from '@capacitor/local-notifications';
import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class NotificationService {
  constructor(private http: HttpClient) {}

  // Initialize push notifications
  async initialize() {
    // Request permission
    const result = await PushNotifications.requestPermissions();

    if (result.receive === 'granted') {
      // Register with FCM/APNs
      await PushNotifications.register();

      // Listen for registration
      await PushNotifications.addListener('registration', async (token) => {
        // Send token to backend
        await this.sendTokenToBackend(token.value);
      });

      // Listen for notifications
      await PushNotifications.addListener('pushNotificationReceived', (notification) => {
        // Handle received notification (app in foreground)
        this.showLocalNotification(notification);
      });

      // Listen for notification tap
      await PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
        // Navigate to relevant screen
        this.handleNotificationTap(notification);
      });
    }
  }

  // Send device token to backend
  private async sendTokenToBackend(token: string) {
    // You'll need to create this endpoint
    this.http.post(`${API_BASE_URL}/user/device-token`, {
      token: token,
      platform: this.getPlatform()
    }).subscribe();
  }

  // Show local notification
  async showLocalNotification(notification: any) {
    await LocalNotifications.schedule({
      notifications: [{
        title: notification.title,
        body: notification.body,
        id: notification.id,
        schedule: { at: new Date() },
        sound: 'beep.wav',
        attachments: null,
        actionTypeId: '',
        extra: notification.data
      }]
    });
  }

  // Handle notification tap
  private handleNotificationTap(notification: any) {
    const data = notification.notification.data;

    // Navigate based on notification type
    switch(data.type) {
      case 'reaction':
        this.router.navigate(['/tabs/timeline', data.postId]);
        break;
      case 'comment':
        this.router.navigate(['/tabs/timeline', data.postId]);
        break;
      case 'meeting':
        this.router.navigate(['/tabs/meetings', data.meetingId]);
        break;
      case 'saving_milestone':
        this.router.navigate(['/tabs/savings', data.savingId]);
        break;
      // ... handle all notification types
    }
  }
}
```

### Backend Notification Endpoint (to be created)
You'll need to create this endpoint in your Laravel backend:
```php
// routes/api.php
Route::post('/user/device-token', [DeviceTokenController::class, 'store']);
Route::delete('/user/device-token', [DeviceTokenController::class, 'delete']);
```

---

## ⚠️ NAVIGATION BEST PRACTICES

### Hardware Back Button Handling
```typescript
// In app.component.ts or relevant page
setupBackButton() {
  this.platform.backButton.subscribeWithPriority(10, () => {
    const currentUrl = this.router.url;

    // Define back button behavior for specific screens
    if (currentUrl.includes('/tabs/')) {
      // On main tabs, show exit confirmation
      this.showExitConfirmation();
    } else if (currentUrl.includes('/login')) {
      // On login screen, exit app
      navigator['app'].exitApp();
    } else {
      // Default: navigate back
      this.location.back();
    }
  });
}

showExitConfirmation() {
  this.alertCtrl.create({
    header: 'Exit App?',
    message: 'Are you sure you want to exit?',
    buttons: [
      { text: 'Cancel', role: 'cancel' },
      {
        text: 'Exit',
        handler: () => navigator['app'].exitApp()
      }
    ]
  }).then(alert => alert.present());
}
```

### Proper Navigation Guards
```typescript
// auth.guard.ts
@Injectable({ providedIn: 'root' })
export class AuthGuard implements CanActivate {
  constructor(
    private secureStorage: SecureStorageService,
    private router: Router
  ) {}

  async canActivate(): Promise<boolean> {
    const token = await this.secureStorage.get('auth_token');

    if (token) {
      return true;
    } else {
      this.router.navigate(['/login']);
      return false;
    }
  }
}

// couple.guard.ts - Ensure user is paired
@Injectable({ providedIn: 'root' })
export class CoupleGuard implements CanActivate {
  constructor(
    private secureStorage: SecureStorageService,
    private router: Router,
    private http: HttpClient
  ) {}

  async canActivate(): Promise<boolean> {
    // Check if user has active couple
    // Return true if paired, false otherwise
    // If false, redirect to pairing screen
  }
}
```

### Navigation with Proper History
```typescript
// Use NavController for better control
constructor(private navCtrl: NavController) {}

// Navigate forward
this.navCtrl.navigateForward('/tabs/meetings/new');

// Navigate back
this.navCtrl.pop();

// Navigate back to root
this.navCtrl.popToRoot();

// Navigate with options
this.navCtrl.navigateForward('/tabs/meetings/123', {
  animationDirection: 'forward'
});
```

---

## 📊 STATE MANAGEMENT

### Recommended: NgRx or Akita (Choose one)

Using NgRx example:
```typescript
// State structure
export interface AppState {
  auth: AuthState;
  user: UserState;
  couple: CoupleState;
  meetings: MeetingsState;
  savings: SavingsState;
  // ... other features
}

// Store key data
interface AuthState {
  token: string | null;
  isAuthenticated: boolean;
  user: User | null;
}

interface UserState {
  profile: UserProfile | null;
  couple: Couple | null;
}

interface MeetingsState {
  meetings: Meeting[];
  currentMeeting: Meeting | null;
  loading: boolean;
  error: string | null;
}
```

---

## 🎯 PRIORITY TASKS (Implementation Order)

### Phase 1: Foundation (Week 1)
1. ✅ Setup project structure
2. ✅ Implement authentication service
3. ✅ Setup HTTP interceptors
4. ✅ Create base UI components
5. ✅ Setup navigation structure
6. ✅ Implement token storage

### Phase 2: Core Features (Week 2)
1. ✅ Profile management screens
2. ✅ Pairing flow screens
3. ✅ Dashboard screen
4. ✅ Settings screen
5. ✅ Navigation polish

### Phase 3: Planning Features (Week 3)
1. ✅ Meetings screens (CRUD)
2. ✅ Travels screens (CRUD)
3. ✅ Travel photos (upload/display)
4. ✅ Travel journals (CRUD)
5. ✅ Meeting feedback

### Phase 4: Financial Features (Week 4)
1. ✅ Savings screens (CRUD)
2. ✅ Categories management
3. ✅ Recurring savings
4. ✅ Transactions
5. ✅ Analytics charts

### Phase 5: Social Features (Week 5)
1. ✅ Timeline feed
2. ✅ Post creation
3. ✅ Reactions & comments
4. ✅ Mood check-in
5. ✅ Daily questions

### Phase 6: Goals & Tasks (Week 6)
1. ✅ Goals management
2. ✅ Tasks management
3. ✅ Progress tracking
4. ✅ Statistics

### Phase 7: Polish & Advanced (Week 7)
1. ✅ Push notifications
2. ✅ Offline mode (optional)
3. ✅ Animations polish
4. ✅ Error handling
5. ✅ Loading states

---

## 📦 REQUIRED IONIC PACKAGES

```json
{
  "dependencies": {
    "@angular/animations": "^17.0.0",
    "@angular/cdk": "^17.0.0",
    "@angular/fire": "^17.0.0",
    "@capacitor/app": "^5.0.0",
    "@capacitor/camera": "^5.0.0",
    "@capacitor/filesystem": "^5.0.0",
    "@capacitor/push-notifications": "^5.0.0",
    "@capacitor/local-notifications": "^5.0.0",
    "@capacitor/storage": "^5.0.0",
    "@capacitor/haptics": "^5.0.0",
    "@capacitor/keyboard": "^5.0.0",
    "@capacitor/splash-screen": "^5.0.0",
    "@capacitor/status-bar": "^5.0.0",
    "@ionic/angular": "^7.0.0",
    "@ionic/storage": "^4.0.0",
    "rxjs": "^7.8.0",
    "chart.js": "^4.0.0",
    "ng-zorro-antd": "^17.0.0"
  }
}
```

---

## 🎨 UI COMPONENTS TO USE/CREATE

### Ionic Components (Built-in)
- `ion-tabs` - Main navigation
- `ion-card` - Content cards
- `ion-list` - Data lists
- `ion-item` - List items
- `ion-avatar` - User avatars
- `ion-badge` - Notification badges
- `ion-chip` - Tags/categories
- `ion-progress-bar` - Progress indicators
- `ion-segment` - Tab switches
- `ion-toast` - Notifications
- `ion-alert` - Alerts
- `ion-modal` - Overlays
- `ion-action-sheet` - Bottom sheets
- `ion-refresher` - Pull to refresh
- `ion-infinite-scroll` - Infinite scroll

### Custom Components to Create
- `app-header` - Consistent page headers
- `app-stat-card` - Statistics display card
- `app-savings-card` - Savings progress card
- `app-timeline-post` - Timeline post component
- `app-mood-selector` - Mood selection picker
- `app-photo-grid` - Photo gallery grid
- `app-analytics-chart` - Chart display
- `app-empty-state` - Empty state placeholder
- `app-loading-skeleton` - Loading skeletons

---

## 📸 IMAGE UPLOAD HANDLING

```typescript
// image.service.ts
import { Camera, CameraResultType, CameraSource } from '@capacitor/camera';

@Injectable({ providedIn: 'root' })
export class ImageService {
  async selectImage(source: 'camera' | 'gallery'): Promise<string> {
    const image = await Camera.getPhoto({
      quality: 80,
      allowEditing: true,
      resultType: CameraResultType.DataUrl,
      source: source === 'camera' ? CameraSource.Camera : CameraSource.Photos
    });

    return image.dataUrl;
  }

  async uploadImage(dataUrl: string, endpoint: string): Promise<any> {
    // Convert to blob
    const blob = this.dataUrlToBlob(dataUrl);

    // Create FormData
    const formData = new FormData();
    formData.append('photo', blob, 'image.jpg');

    // Upload
    return this.http.post(endpoint, formData).toPromise();
  }

  private dataUrlToBlob(dataUrl: string): Blob {
    const arr = dataUrl.split(',');
    const mime = arr[0].match(/:(.*?);/)[1];
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    while (n--) {
      u8arr[n] = bstr.charCodeAt(n);
    }
    return new Blob([u8arr], { type: mime });
  }
}
```

---

## 🚨 ERROR HANDLING STRATEGY

```typescript
// error.handler.ts
import { Injectable } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';
import { ToastController } from '@ionic/angular';

@Injectable({ providedIn: 'root' })
export class ErrorHandler {
  constructor(private toastCtrl: ToastController) {}

  async handleError(error: HttpErrorResponse) {
    let message = 'Something went wrong';

    switch (error.status) {
      case 400:
        message = error.error?.message || 'Invalid request';
        break;
      case 401:
        message = 'Please login again';
        // Redirect to login
        break;
      case 403:
        message = 'You don\'t have permission';
        break;
      case 404:
        message = 'Resource not found';
        break;
      case 422:
        message = error.error?.message || 'Validation error';
        break;
      case 500:
        message = 'Server error, please try again';
        break;
    }

    const toast = await this.toastCtrl.create({
      message,
      duration: 3000,
      color: 'danger',
      position: 'top'
    });

    await toast.present();
  }
}
```

---

## 📱 RESPONSIVE DESIGN CONSIDERATIONS

```scss
// Use Ionic's grid system
ion-grid {
  ion-col {
    // Mobile: 1 column
    size: 12;

    // Tablet: 2 columns
    @media (min-width: 768px) {
      size: 6;
    }

    // Desktop: 3 columns
    @media (min-width: 1024px) {
      size: 4;
    }
  }
}

// Platform-specific styling
.ios {
  // iOS-specific styles
}

.md {
  // Material Design-specific styles
}
```

---

## ✅ CHECKLIST BEFORE RELEASE

### Functionality
- [ ] All features working end-to-end
- [ ] Push notifications working
- [ ] Authentication persists correctly
- [ ] Token refresh working
- [ ] All API calls have error handling
- [ ] Forms have proper validation
- [ ] Images upload correctly
- [ ] Charts display correctly

### Navigation
- [ ] Hardware back button works
- [ ] Tab navigation smooth
- [ ] Deep linking works
- [ ] Browser back button works (PWA)
- [ ] No duplicate history entries
- [ ] Modals close correctly

### UX/UI
- [ ] Loading states everywhere
- [ ] Empty states for all lists
- [ ] Pull-to-refresh on list pages
- [ ] Infinite scroll working
- [ ] Consistent spacing
- [ ] Consistent colors
- [ ] Fonts readable
- [ ] Touch targets minimum 44px

### Performance
- [ ] No memory leaks
- [ ] Images optimized
- [ ] Lazy loading implemented
- [ ] Bundle size optimized
- [ ] Smooth animations (60fps)

### Testing
- [ ] Tested on iOS
- [ ] Tested on Android
- [ ] Tested on tablet
- [ ] Tested on different screen sizes
- [ ] Tested offline scenario
- [ ] Tested poor network

---

## 🎯 SUCCESS METRICS

### Primary Goals
1. ✅ All 22+ features fully functional
2. ✅ Modern, polished UI
3. ✅ Seamless navigation
4. ✅ Push notifications working
5. ✅ Persistent authentication
6. ✅ No critical bugs
7. ✅ Smooth performance

### User Experience Goals
1. ✅ Intuitive navigation
2. ✅ Fast load times
3. ✅ Beautiful, modern design
4. ✅ Responsive interactions
5. ✅ Clear visual feedback
6. ✅ Helpful error messages

---

## 📚 REFERENCE MATERIALS

### Official Documentation
- [Ionic Documentation](https://ionicframework.com/docs)
- [Angular Documentation](https://angular.io/docs)
- [Capacitor Documentation](https://capacitorjs.com/docs)

### Design Inspiration
- [Dribbble - Mobile Apps](https://dribbble.com/tags/mobile-app)
- [Mobbin - App Designs](https://mobbin.com)
- [Material Design 3](https://m3.material.io)

### API Documentation
- Backend API: Available at `/api` endpoint
- Full API list: See "API BASE URL & ENDPOINTS" section above

---

## 💡 PRO TIPS

1. **Start with the base** - Get auth working first, everything depends on it
2. **Component library** - Build reusable components early
3. **State management** - Don't skip this, it will save you time
4. **Error handling** - Handle errors gracefully from day one
5. **Testing** - Test on real devices, not just browser
6. **Performance** - Use lazy loading for routes and components
7. **User feedback** - Provide visual feedback for every action
8. **Accessibility** - Don't forget ARIA labels and screen readers

---

## 🚀 GETTING STARTED

```bash
# 1. Clone the mobile app repository
git clone <your-mobile-repo-url>
cd our-plan-mobile

# 2. Install dependencies
npm install

# 3. Setup environment
cp src/environments/environment.template.ts src/environments/environment.ts
# Edit environment.ts with your API URL

# 4. Run development server
ionic serve

# 5. Build for iOS
ionic capacitor build ios

# 6. Build for Android
ionic capacitor build android
```

---

## 📞 SUPPORT

For questions or issues:
1. Check this document first
2. Review API documentation
3. Check existing codebase for patterns
4. Ask the backend team (you!)

---

**Remember:** The goal is to create a beautiful, modern, fully-functional mobile app that users love to use. Focus on user experience, smooth interactions, and visual polish. Good luck! 🚀

---

*Generated for Our Plan Mobile App Revamp Project*
*Version 1.0 - API Complete*
