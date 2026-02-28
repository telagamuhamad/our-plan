# LDR Couple App - Ionic + Capacitor + Laravel API
## Detailed Planning & Implementation Guide

### 📱 Project Overview
Aplikasi mobile hybrid menggunakan **Ionic Framework** dengan **Capacitor** untuk iOS & Android, dan **Laravel** sebagai backend API. Kombinasi ini memberikan:
- Single codebase untuk iOS & Android
- Native device access (camera, notifications, storage)
- Web technologies (HTML, CSS, JavaScript/TypeScript)
- Fast development cycle
- Easy deployment

**Tech Stack:**
- **Frontend**: Ionic 7 + Angular/React/Vue (pilih salah satu)
- **Mobile Runtime**: Capacitor 5
- **Backend**: Laravel 11 + API
- **State Management**: NgRx (Angular) / Redux (React) / Pinia (Vue)
- **HTTP Client**: Axios / Angular HttpClient
- **Push Notifications**: Firebase Cloud Messaging + Capacitor Push Notifications

---

## 🎯 1. Why Ionic + Capacitor?

### Advantages
✅ **Single Codebase**: Write once, run on iOS, Android, dan Web
✅ **Native Features**: Full access ke camera, storage, notifications, dll
✅ **Fast Development**: Familiar web tech stack
✅ **Live Reload**: Testing langsung di device via Ionic DevApp
✅ **Cost Effective**: Tidak perlu hire iOS & Android developer terpisah
✅ **Web Deployment**: Bisa deploy sebagai PWA juga
✅ **Rich UI Components**: Ionic components sudah optimized untuk mobile
✅ **Easy Updates**: CodePush / Capacitor Live Updates untuk instant updates

### Comparison vs Native
| Feature | Ionic + Capacitor | Native (Flutter/RN) |
|---------|------------------|---------------------|
| Development Speed | ⚡⚡⚡ | ⚡⚡ |
| Performance | ⚡⚡ | ⚡⚡⚡ |
| Native Feel | ⚡⚡ | ⚡⚡⚡ |
| Dev Cost | 💰 | 💰💰 |
| Learning Curve | Easy (web dev) | Medium (new framework) |
| Web Deployment | ✅ Yes | ❌ No (Flutter Web limited) |

---

## 🏗️ 2. Project Structure

### Ionic Project Structure
```
ldr-couple-app/
├── android/                    # Android platform (auto-generated)
├── ios/                        # iOS platform (auto-generated)
├── src/
│   ├── app/
│   │   ├── core/
│   │   │   ├── services/
│   │   │   │   ├── api.service.ts
│   │   │   │   ├── auth.service.ts
│   │   │   │   ├── storage.service.ts
│   │   │   │   ├── notification.service.ts
│   │   │   │   └── camera.service.ts
│   │   │   ├── guards/
│   │   │   │   ├── auth.guard.ts
│   │   │   │   └── couple.guard.ts
│   │   │   ├── interceptors/
│   │   │   │   ├── auth.interceptor.ts
│   │   │   │   └── error.interceptor.ts
│   │   │   └── models/
│   │   │       ├── user.model.ts
│   │   │       ├── couple.model.ts
│   │   │       ├── timeline-post.model.ts
│   │   │       └── ...
│   │   ├── pages/
│   │   │   ├── auth/
│   │   │   │   ├── login/
│   │   │   │   ├── register/
│   │   │   │   └── pairing/
│   │   │   ├── home/
│   │   │   │   └── home.page.ts
│   │   │   ├── timeline/
│   │   │   │   ├── timeline.page.ts
│   │   │   │   └── create-post/
│   │   │   ├── mood/
│   │   │   │   ├── checkin/
│   │   │   │   └── calendar/
│   │   │   ├── countdown/
│   │   │   ├── savings/
│   │   │   ├── memories/
│   │   │   ├── love-letters/
│   │   │   └── profile/
│   │   ├── components/
│   │   │   ├── timeline-post/
│   │   │   ├── mood-selector/
│   │   │   ├── countdown-card/
│   │   │   ├── progress-bar/
│   │   │   └── ...
│   │   ├── tabs/
│   │   │   └── tabs.page.ts
│   │   └── app-routing.module.ts
│   ├── assets/
│   │   ├── images/
│   │   ├── icons/
│   │   └── sounds/
│   ├── theme/
│   │   └── variables.scss
│   └── environments/
│       ├── environment.ts
│       └── environment.prod.ts
├── capacitor.config.ts
├── ionic.config.json
├── package.json
└── tsconfig.json
```

---

## 📦 3. Installation & Setup

### Step 1: Install Ionic CLI
```bash
npm install -g @ionic/cli
```

### Step 2: Create Ionic Project
```bash
# With Angular (Recommended for struktur yang clear)
ionic start ldr-couple-app blank --type=angular --capacitor

# Or with React
ionic start ldr-couple-app blank --type=react --capacitor

# Or with Vue
ionic start ldr-couple-app blank --type=vue --capacitor
```

### Step 3: Install Dependencies
```bash
cd ldr-couple-app

# Core dependencies
npm install axios
npm install @capacitor/camera @capacitor/storage @capacitor/push-notifications
npm install @capacitor/filesystem @capacitor/geolocation
npm install @capacitor/local-notifications
npm install @capacitor/network @capacitor/share

# For state management (if using Angular)
npm install @ngrx/store @ngrx/effects @ngrx/store-devtools

# For charts
npm install chart.js ng2-charts

# For date handling
npm install date-fns
```

### Step 4: Configure Capacitor
```typescript
// capacitor.config.ts
import { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.yourname.ldrcoupleapp',
  appName: 'LDR Couple',
  webDir: 'www',
  bundledWebRuntime: false,
  plugins: {
    PushNotifications: {
      presentationOptions: ['badge', 'sound', 'alert']
    },
    LocalNotifications: {
      smallIcon: 'ic_stat_icon_config_sample',
      iconColor: '#FF6B6B'
    }
  }
};

export default config;
```

### Step 5: Add Platforms
```bash
# Add Android
ionic cap add android

# Add iOS (requires macOS)
ionic cap add ios
```

---

## 🎨 4. UI Implementation (Ionic Components)

### Main App Structure (Tabs)
```typescript
// app/tabs/tabs.page.ts
import { Component } from '@angular/core';

@Component({
  selector: 'app-tabs',
  templateUrl: 'tabs.page.html',
  styleUrls: ['tabs.page.scss']
})
export class TabsPage {
  constructor() {}
}
```

```html
<!-- app/tabs/tabs.page.html -->
<ion-tabs>
  <ion-tab-bar slot="bottom">
    <ion-tab-button tab="home">
      <ion-icon name="home"></ion-icon>
      <ion-label>Home</ion-label>
    </ion-tab-button>

    <ion-tab-button tab="timeline">
      <ion-icon name="images"></ion-icon>
      <ion-label>Timeline</ion-label>
    </ion-tab-button>

    <ion-tab-button tab="savings">
      <ion-icon name="wallet"></ion-icon>
      <ion-label>Savings</ion-label>
      <ion-badge *ngIf="savingsProgress < 100">{{ savingsProgress }}%</ion-badge>
    </ion-tab-button>

    <ion-tab-button tab="memories">
      <ion-icon name="albums"></ion-icon>
      <ion-label>Memories</ion-label>
    </ion-tab-button>

    <ion-tab-button tab="profile">
      <ion-icon name="person"></ion-icon>
      <ion-label>Profile</ion-label>
    </ion-tab-button>
  </ion-tab-bar>
</ion-tabs>
```

### Home Page (Dashboard)
```html
<!-- pages/home/home.page.html -->
<ion-header>
  <ion-toolbar color="primary">
    <ion-title>❤️ LDR Couple</ion-title>
    <ion-buttons slot="end">
      <ion-button (click)="openNotifications()">
        <ion-icon name="notifications"></ion-icon>
        <ion-badge *ngIf="unreadCount > 0" color="danger">{{ unreadCount }}</ion-badge>
      </ion-button>
    </ion-buttons>
  </ion-toolbar>
</ion-header>

<ion-content>
  <ion-refresher slot="fixed" (ionRefresh)="doRefresh($event)">
    <ion-refresher-content></ion-refresher-content>
  </ion-refresher>

  <!-- Countdown Card -->
  <ion-card class="countdown-card">
    <ion-card-header>
      <ion-card-title>Ketemu Lagi</ion-card-title>
    </ion-card-header>
    <ion-card-content>
      <div class="countdown-display">
        <div class="countdown-unit">
          <span class="number">{{ countdown.days }}</span>
          <span class="label">Hari</span>
        </div>
        <div class="countdown-unit">
          <span class="number">{{ countdown.hours }}</span>
          <span class="label">Jam</span>
        </div>
        <div class="countdown-unit">
          <span class="number">{{ countdown.minutes }}</span>
          <span class="label">Menit</span>
        </div>
      </div>
      <p class="location">📍 {{ nextEvent.location }}</p>
    </ion-card-content>
  </ion-card>

  <!-- Mood Section -->
  <ion-card>
    <ion-card-header>
      <ion-card-title>Today's Mood</ion-card-title>
    </ion-card-header>
    <ion-card-content>
      <div class="mood-display">
        <div class="mood-item">
          <div class="emoji">{{ myMood?.emoji || '😊' }}</div>
          <p>You</p>
          <ion-button *ngIf="!myMood" size="small" (click)="openMoodModal()">
            Check in
          </ion-button>
        </div>
        <div class="mood-item">
          <div class="emoji">{{ partnerMood?.emoji || '❓' }}</div>
          <p>{{ partner?.name }}</p>
        </div>
      </div>
    </ion-card-content>
  </ion-card>

  <!-- Quick Actions -->
  <ion-card>
    <ion-card-header>
      <ion-card-title>Quick Actions</ion-card-title>
    </ion-card-header>
    <ion-card-content>
      <ion-grid>
        <ion-row>
          <ion-col>
            <ion-button expand="block" (click)="createPost()">
              <ion-icon name="create" slot="start"></ion-icon>
              New Post
            </ion-button>
          </ion-col>
        </ion-row>
        <ion-row>
          <ion-col>
            <ion-button expand="block" color="danger" (click)="sendMissingYou()" [disabled]="!canSendMissingYou">
              <ion-icon name="heart" slot="start"></ion-icon>
              Missing You
            </ion-button>
          </ion-col>
        </ion-row>
        <ion-row>
          <ion-col>
            <ion-button expand="block" color="secondary" (click)="addMemory()">
              <ion-icon name="camera" slot="start"></ion-icon>
              Add Memory
            </ion-button>
          </ion-col>
        </ion-row>
      </ion-grid>
    </ion-card-content>
  </ion-card>

  <!-- Savings Widget -->
  <ion-card>
    <ion-card-header>
      <ion-card-title>Wedding Savings</ion-card-title>
    </ion-card-header>
    <ion-card-content>
      <div class="progress-container">
        <ion-progress-bar [value]="savingsProgress / 100" color="success"></ion-progress-bar>
        <div class="progress-info">
          <span>{{ savingsProgress }}%</span>
          <span>Rp {{ savingsGoal?.current_amount | number }}</span>
        </div>
      </div>
      <ion-button expand="block" size="small" (click)="viewSavingsDetail()">
        View Details
      </ion-button>
    </ion-card-content>
  </ion-card>

  <!-- Question of the Day -->
  <app-daily-question></app-daily-question>

  <!-- Recent Timeline -->
  <ion-card>
    <ion-card-header>
      <ion-card-title>Recent Updates</ion-card-title>
    </ion-card-header>
    <ion-card-content>
      <app-timeline-post *ngFor="let post of recentPosts" [post]="post"></app-timeline-post>
      <ion-button expand="block" fill="clear" routerLink="/tabs/timeline">
        See All →
      </ion-button>
    </ion-card-content>
  </ion-card>
</ion-content>
```

```typescript
// pages/home/home.page.ts
import { Component, OnInit, OnDestroy } from '@angular/core';
import { ModalController } from '@ionic/angular';
import { interval, Subscription } from 'rxjs';
import { ApiService } from '../../core/services/api.service';
import { NotificationService } from '../../core/services/notification.service';

@Component({
  selector: 'app-home',
  templateUrl: './home.page.html',
  styleUrls: ['./home.page.scss'],
})
export class HomePage implements OnInit, OnDestroy {
  nextEvent: any;
  countdown = { days: 0, hours: 0, minutes: 0 };
  myMood: any;
  partnerMood: any;
  partner: any;
  savingsGoal: any;
  savingsProgress = 0;
  recentPosts: any[] = [];
  unreadCount = 0;
  canSendMissingYou = true;
  
  private countdownSubscription: Subscription;

  constructor(
    private api: ApiService,
    private notification: NotificationService,
    private modalCtrl: ModalController
  ) {}

  ngOnInit() {
    this.loadData();
    this.startCountdown();
  }

  ngOnDestroy() {
    if (this.countdownSubscription) {
      this.countdownSubscription.unsubscribe();
    }
  }

  async loadData() {
    try {
      const [events, moods, savings, posts] = await Promise.all([
        this.api.get('/meet-events?status=upcoming').toPromise(),
        this.api.get('/moods/today').toPromise(),
        this.api.get('/savings-goals').toPromise(),
        this.api.get('/timeline?limit=3').toPromise()
      ]);

      this.nextEvent = events.data[0];
      this.myMood = moods.data.my_mood;
      this.partnerMood = moods.data.partner_mood;
      this.partner = moods.data.partner;
      this.savingsGoal = savings.data[0];
      this.savingsProgress = (this.savingsGoal.current_amount / this.savingsGoal.target_amount) * 100;
      this.recentPosts = posts.data;
    } catch (error) {
      console.error('Error loading data:', error);
    }
  }

  startCountdown() {
    this.updateCountdown();
    this.countdownSubscription = interval(60000).subscribe(() => {
      this.updateCountdown();
    });
  }

  updateCountdown() {
    if (!this.nextEvent) return;
    
    const now = new Date().getTime();
    const target = new Date(this.nextEvent.meet_date).getTime();
    const distance = target - now;

    this.countdown.days = Math.floor(distance / (1000 * 60 * 60 * 24));
    this.countdown.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    this.countdown.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  }

  async doRefresh(event: any) {
    await this.loadData();
    event.target.complete();
  }

  async openMoodModal() {
    const { MoodCheckinModalComponent } = await import('../../components/mood-checkin-modal/mood-checkin-modal.component');
    const modal = await this.modalCtrl.create({
      component: MoodCheckinModalComponent
    });
    
    await modal.present();
    
    const { data } = await modal.onWillDismiss();
    if (data?.success) {
      this.loadData();
    }
  }

  async sendMissingYou() {
    try {
      await this.api.post('/notifications/missing-you', {}).toPromise();
      this.notification.showToast('Notifikasi terkirim ke ' + this.partner.name + ' ❤️');
      this.canSendMissingYou = false;
      
      // Re-enable after 1 hour
      setTimeout(() => {
        this.canSendMissingYou = true;
      }, 3600000);
    } catch (error) {
      this.notification.showToast('Gagal mengirim notifikasi');
    }
  }

  createPost() {
    // Navigate to create post page
  }

  addMemory() {
    // Navigate to add memory page
  }

  viewSavingsDetail() {
    // Navigate to savings page
  }

  openNotifications() {
    // Navigate to notifications page
  }
}
```

### Timeline Page
```html
<!-- pages/timeline/timeline.page.html -->
<ion-header>
  <ion-toolbar color="primary">
    <ion-title>Timeline</ion-title>
    <ion-buttons slot="end">
      <ion-button (click)="openFilter()">
        <ion-icon name="funnel"></ion-icon>
      </ion-button>
    </ion-buttons>
  </ion-toolbar>
  <ion-toolbar>
    <ion-searchbar [(ngModel)]="searchQuery" (ionChange)="onSearch($event)"></ion-searchbar>
  </ion-toolbar>
</ion-header>

<ion-content>
  <ion-refresher slot="fixed" (ionRefresh)="doRefresh($event)">
    <ion-refresher-content></ion-refresher-content>
  </ion-refresher>

  <!-- Timeline Posts -->
  <app-timeline-post 
    *ngFor="let post of posts" 
    [post]="post"
    (onReact)="handleReaction($event)"
    (onComment)="openComments($event)"
  ></app-timeline-post>

  <!-- Infinite Scroll -->
  <ion-infinite-scroll threshold="100px" (ionInfinite)="loadMore($event)">
    <ion-infinite-scroll-content></ion-infinite-scroll-content>
  </ion-infinite-scroll>

  <!-- FAB for Create Post -->
  <ion-fab vertical="bottom" horizontal="end" slot="fixed">
    <ion-fab-button (click)="createPost()">
      <ion-icon name="add"></ion-icon>
    </ion-fab-button>
  </ion-fab>
</ion-content>
```

### Mood Check-in Modal
```html
<!-- components/mood-checkin-modal/mood-checkin-modal.component.html -->
<ion-header>
  <ion-toolbar>
    <ion-title>How are you feeling?</ion-title>
    <ion-buttons slot="end">
      <ion-button (click)="dismiss()">
        <ion-icon name="close"></ion-icon>
      </ion-button>
    </ion-buttons>
  </ion-toolbar>
</ion-header>

<ion-content class="ion-padding">
  <div class="mood-selector">
    <div 
      class="mood-option" 
      *ngFor="let mood of moods"
      [class.selected]="selectedMood === mood.value"
      (click)="selectMood(mood.value)"
    >
      <div class="emoji">{{ mood.emoji }}</div>
      <p>{{ mood.label }}</p>
    </div>
  </div>

  <ion-item>
    <ion-label position="stacked">Optional Note</ion-label>
    <ion-textarea 
      [(ngModel)]="note" 
      placeholder="What's on your mind?"
      rows="3"
      maxlength="200"
    ></ion-textarea>
  </ion-item>

  <ion-button expand="block" [disabled]="!selectedMood" (click)="submit()">
    Check In
  </ion-button>
</ion-content>
```

```typescript
// components/mood-checkin-modal/mood-checkin-modal.component.ts
import { Component } from '@angular/core';
import { ModalController } from '@ionic/angular';
import { ApiService } from '../../core/services/api.service';

@Component({
  selector: 'app-mood-checkin-modal',
  templateUrl: './mood-checkin-modal.component.html',
  styleUrls: ['./mood-checkin-modal.component.scss'],
})
export class MoodCheckinModalComponent {
  moods = [
    { value: 'amazing', emoji: '😄', label: 'Amazing' },
    { value: 'good', emoji: '🙂', label: 'Good' },
    { value: 'okay', emoji: '😐', label: 'Okay' },
    { value: 'sad', emoji: '😔', label: 'Sad' },
    { value: 'terrible', emoji: '😢', label: 'Terrible' }
  ];

  selectedMood: string = '';
  note: string = '';

  constructor(
    private modalCtrl: ModalController,
    private api: ApiService
  ) {}

  selectMood(mood: string) {
    this.selectedMood = mood;
  }

  async submit() {
    try {
      await this.api.post('/moods/checkin', {
        mood: this.selectedMood,
        note: this.note
      }).toPromise();

      this.modalCtrl.dismiss({ success: true });
    } catch (error) {
      console.error('Error checking in mood:', error);
    }
  }

  dismiss() {
    this.modalCtrl.dismiss();
  }
}
```

---

## 🔧 5. Core Services Implementation

### API Service
```typescript
// core/services/api.service.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { StorageService } from './storage.service';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private baseUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    private storage: StorageService
  ) {}

  private async getHeaders(): Promise<HttpHeaders> {
    const token = await this.storage.get('auth_token');
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    });
  }

  async get<T>(endpoint: string): Promise<Observable<T>> {
    const headers = await this.getHeaders();
    return this.http.get<T>(`${this.baseUrl}${endpoint}`, { headers });
  }

  async post<T>(endpoint: string, data: any): Promise<Observable<T>> {
    const headers = await this.getHeaders();
    return this.http.post<T>(`${this.baseUrl}${endpoint}`, data, { headers });
  }

  async put<T>(endpoint: string, data: any): Promise<Observable<T>> {
    const headers = await this.getHeaders();
    return this.http.put<T>(`${this.baseUrl}${endpoint}`, data, { headers });
  }

  async delete<T>(endpoint: string): Promise<Observable<T>> {
    const headers = await this.getHeaders();
    return this.http.delete<T>(`${this.baseUrl}${endpoint}`, { headers });
  }

  async uploadFile(endpoint: string, file: File, additionalData?: any): Promise<Observable<any>> {
    const token = await this.storage.get('auth_token');
    const formData = new FormData();
    formData.append('file', file);
    
    if (additionalData) {
      Object.keys(additionalData).forEach(key => {
        formData.append(key, additionalData[key]);
      });
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`
    });

    return this.http.post(`${this.baseUrl}${endpoint}`, formData, { headers });
  }
}
```

### Storage Service (Capacitor Storage)
```typescript
// core/services/storage.service.ts
import { Injectable } from '@angular/core';
import { Storage } from '@capacitor/storage';

@Injectable({
  providedIn: 'root'
})
export class StorageService {
  constructor() {}

  async set(key: string, value: any): Promise<void> {
    await Storage.set({
      key: key,
      value: JSON.stringify(value)
    });
  }

  async get(key: string): Promise<any> {
    const { value } = await Storage.get({ key: key });
    return value ? JSON.parse(value) : null;
  }

  async remove(key: string): Promise<void> {
    await Storage.remove({ key: key });
  }

  async clear(): Promise<void> {
    await Storage.clear();
  }
}
```

### Auth Service
```typescript
// core/services/auth.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { StorageService } from './storage.service';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private currentUserSubject: BehaviorSubject<any>;
  public currentUser: Observable<any>;

  constructor(
    private http: HttpClient,
    private storage: StorageService
  ) {
    this.currentUserSubject = new BehaviorSubject<any>(null);
    this.currentUser = this.currentUserSubject.asObservable();
    this.loadUser();
  }

  private async loadUser() {
    const user = await this.storage.get('current_user');
    if (user) {
      this.currentUserSubject.next(user);
    }
  }

  login(email: string, password: string): Observable<any> {
    return this.http.post(`${environment.apiUrl}/auth/login`, { email, password })
      .pipe(
        tap(async (response: any) => {
          await this.storage.set('auth_token', response.token);
          await this.storage.set('current_user', response.user);
          this.currentUserSubject.next(response.user);
        })
      );
  }

  register(name: string, email: string, password: string): Observable<any> {
    return this.http.post(`${environment.apiUrl}/auth/register`, { name, email, password });
  }

  async logout(): Promise<void> {
    await this.storage.remove('auth_token');
    await this.storage.remove('current_user');
    this.currentUserSubject.next(null);
  }

  async isAuthenticated(): Promise<boolean> {
    const token = await this.storage.get('auth_token');
    return !!token;
  }

  get currentUserValue(): any {
    return this.currentUserSubject.value;
  }
}
```

### Camera Service
```typescript
// core/services/camera.service.ts
import { Injectable } from '@angular/core';
import { Camera, CameraResultType, CameraSource, Photo } from '@capacitor/camera';

@Injectable({
  providedIn: 'root'
})
export class CameraService {
  constructor() {}

  async takePicture(): Promise<Photo> {
    const image = await Camera.getPhoto({
      quality: 90,
      allowEditing: true,
      resultType: CameraResultType.DataUrl,
      source: CameraSource.Camera
    });

    return image;
  }

  async pickFromGallery(): Promise<Photo> {
    const image = await Camera.getPhoto({
      quality: 90,
      allowEditing: true,
      resultType: CameraResultType.DataUrl,
      source: CameraSource.Photos
    });

    return image;
  }

  async pickMultiple(): Promise<Photo[]> {
    // Note: Capacitor Camera doesn't support multiple selection natively
    // You'll need to use @capacitor-community/media plugin for this
    return [];
  }

  dataUrlToFile(dataUrl: string, filename: string): File {
    const arr = dataUrl.split(',');
    const mime = arr[0].match(/:(.*?);/)[1];
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    
    while (n--) {
      u8arr[n] = bstr.charCodeAt(n);
    }
    
    return new File([u8arr], filename, { type: mime });
  }
}
```

### Push Notification Service
```typescript
// core/services/notification.service.ts
import { Injectable } from '@angular/core';
import { 
  PushNotifications, 
  PushNotificationSchema, 
  ActionPerformed 
} from '@capacitor/push-notifications';
import { LocalNotifications } from '@capacitor/local-notifications';
import { ToastController } from '@ionic/angular';
import { ApiService } from './api.service';

@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  constructor(
    private api: ApiService,
    private toastCtrl: ToastController
  ) {}

  async initialize() {
    // Request permission
    const permission = await PushNotifications.requestPermissions();
    
    if (permission.receive === 'granted') {
      // Register with FCM
      await PushNotifications.register();

      // Listeners
      await PushNotifications.addListener('registration', async (token) => {
        console.log('Push registration success, token: ' + token.value);
        // Send token to backend
        await this.api.post('/users/device-token', { token: token.value }).toPromise();
      });

      await PushNotifications.addListener('registrationError', (error: any) => {
        console.error('Error on registration: ' + JSON.stringify(error));
      });

      await PushNotifications.addListener('pushNotificationReceived', 
        async (notification: PushNotificationSchema) => {
          console.log('Push received: ' + JSON.stringify(notification));
          
          // Show local notification if app is in foreground
          await LocalNotifications.schedule({
            notifications: [{
              title: notification.title,
              body: notification.body,
              id: new Date().getTime(),
              extra: notification.data
            }]
          });
        }
      );

      await PushNotifications.addListener('pushNotificationActionPerformed', 
        (notification: ActionPerformed) => {
          console.log('Push action performed: ' + JSON.stringify(notification));
          // Handle notification tap
          this.handleNotificationTap(notification.notification.data);
        }
      );
    }
  }

  private handleNotificationTap(data: any) {
    // Navigate based on notification type
    switch (data.type) {
      case 'mood_checkin':
        // Navigate to mood page
        break;
      case 'missing_you':
        // Show missing you message
        break;
      case 'timeline_post':
        // Navigate to timeline
        break;
      case 'love_letter_unlocked':
        // Navigate to love letters
        break;
    }
  }

  async showToast(message: string, duration: number = 2000) {
    const toast = await this.toastCtrl.create({
      message,
      duration,
      position: 'bottom'
    });
    toast.present();
  }

  async showLocalNotification(title: string, body: string, data?: any) {
    await LocalNotifications.schedule({
      notifications: [{
        title,
        body,
        id: new Date().getTime(),
        extra: data
      }]
    });
  }
}
```

---

## 📸 6. Media Upload Implementation

### Create Post with Images
```typescript
// pages/timeline/create-post/create-post.page.ts
import { Component } from '@angular/core';
import { ActionSheetController, LoadingController } from '@ionic/angular';
import { CameraService } from '../../../core/services/camera.service';
import { ApiService } from '../../../core/services/api.service';

@Component({
  selector: 'app-create-post',
  templateUrl: './create-post.page.html',
})
export class CreatePostPage {
  postType: 'text' | 'image' | 'voice' = 'text';
  content: string = '';
  selectedImages: any[] = [];

  constructor(
    private camera: CameraService,
    private api: ApiService,
    private actionSheetCtrl: ActionSheetController,
    private loadingCtrl: LoadingController
  ) {}

  async selectImages() {
    const actionSheet = await this.actionSheetCtrl.create({
      header: 'Add Photo',
      buttons: [
        {
          text: 'Take Photo',
          icon: 'camera',
          handler: () => {
            this.takePicture();
          }
        },
        {
          text: 'Choose from Gallery',
          icon: 'images',
          handler: () => {
            this.pickFromGallery();
          }
        },
        {
          text: 'Cancel',
          icon: 'close',
          role: 'cancel'
        }
      ]
    });

    await actionSheet.present();
  }

  async takePicture() {
    try {
      const photo = await this.camera.takePicture();
      this.selectedImages.push(photo);
      this.postType = 'image';
    } catch (error) {
      console.error('Error taking picture:', error);
    }
  }

  async pickFromGallery() {
    try {
      const photo = await this.camera.pickFromGallery();
      this.selectedImages.push(photo);
      this.postType = 'image';
    } catch (error) {
      console.error('Error picking from gallery:', error);
    }
  }

  removeImage(index: number) {
    this.selectedImages.splice(index, 1);
    if (this.selectedImages.length === 0) {
      this.postType = 'text';
    }
  }

  async createPost() {
    const loading = await this.loadingCtrl.create({
      message: 'Creating post...'
    });
    await loading.present();

    try {
      if (this.postType === 'image') {
        // Upload images
        const formData = new FormData();
        formData.append('type', 'image');
        formData.append('content', this.content);

        this.selectedImages.forEach((img, index) => {
          const file = this.camera.dataUrlToFile(img.dataUrl, `image_${index}.jpg`);
          formData.append('media[]', file);
        });

        await this.api.uploadFile('/timeline', formData).toPromise();
      } else {
        await this.api.post('/timeline', {
          type: 'text',
          content: this.content
        }).toPromise();
      }

      loading.dismiss();
      // Navigate back or show success
    } catch (error) {
      loading.dismiss();
      console.error('Error creating post:', error);
    }
  }
}
```

---

## 🔄 7. State Management (NgRx Example)

### Store Structure
```typescript
// store/app.state.ts
export interface AppState {
  auth: AuthState;
  couple: CoupleState;
  timeline: TimelineState;
  mood: MoodState;
  savings: SavingsState;
}

export interface AuthState {
  user: any;
  token: string;
  isAuthenticated: boolean;
}

export interface CoupleState {
  couple: any;
  partner: any;
}

export interface TimelineState {
  posts: any[];
  loading: boolean;
  currentPage: number;
  hasMore: boolean;
}
```

### Actions
```typescript
// store/timeline/timeline.actions.ts
import { createAction, props } from '@ngrx/store';

export const loadPosts = createAction('[Timeline] Load Posts');
export const loadPostsSuccess = createAction(
  '[Timeline] Load Posts Success',
  props<{ posts: any[] }>()
);
export const loadPostsFailure = createAction(
  '[Timeline] Load Posts Failure',
  props<{ error: any }>()
);

export const createPost = createAction(
  '[Timeline] Create Post',
  props<{ post: any }>()
);
```

---

## 🚀 8. Build & Deployment

### Development
```bash
# Run in browser
ionic serve

# Run on Android device
ionic cap run android --livereload --external

# Run on iOS device (requires macOS)
ionic cap run ios --livereload --external
```

### Build for Production
```bash
# Build web assets
ionic build --prod

# Sync to native platforms
ionic cap sync

# Android
cd android
./gradlew assembleRelease
# Output: android/app/build/outputs/apk/release/app-release.apk

# iOS (requires macOS & Xcode)
cd ios/App
xcodebuild -workspace App.xcworkspace -scheme App -configuration Release
```

### Code Push (Instant Updates)
```bash
# Install Capacitor Live Updates
npm install @capacitor/live-updates

# Configure in capacitor.config.ts
{
  plugins: {
    LiveUpdates: {
      appId: 'YOUR_APP_ID',
      channel: 'Production'
    }
  }
}
```

---

## 📊 9. Performance Optimization

### Lazy Loading
```typescript
// app-routing.module.ts
const routes: Routes = [
  {
    path: 'tabs',
    loadChildren: () => import('./tabs/tabs.module').then(m => m.TabsPageModule)
  },
  {
    path: 'timeline',
    loadChildren: () => import('./pages/timeline/timeline.module').then(m => m.TimelinePageModule)
  }
];
```

### Image Optimization
```typescript
// Use Capacitor Filesystem for caching
import { Filesystem, Directory } from '@capacitor/filesystem';

async cacheImage(url: string): Promise<string> {
  const fileName = url.split('/').pop();
  const cachedFile = await Filesystem.readFile({
    path: `images/${fileName}`,
    directory: Directory.Cache
  });
  
  if (cachedFile) {
    return cachedFile.data;
  }
  
  // Download and cache
  const response = await fetch(url);
  const blob = await response.blob();
  const base64 = await this.convertBlobToBase64(blob);
  
  await Filesystem.writeFile({
    path: `images/${fileName}`,
    data: base64,
    directory: Directory.Cache
  });
  
  return base64;
}
```

### Offline Support
```typescript
// core/interceptors/offline.interceptor.ts
import { Injectable } from '@angular/core';
import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor } from '@angular/common/http';
import { Observable, from } from 'rxjs';
import { switchMap } from 'rxjs/operators';
import { Network } from '@capacitor/network';

@Injectable()
export class OfflineInterceptor implements HttpInterceptor {
  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    return from(Network.getStatus()).pipe(
      switchMap(status => {
        if (!status.connected) {
          // Queue request for later or serve from cache
          console.log('Offline: Request queued');
        }
        return next.handle(request);
      })
    );
  }
}
```

---

## 🧪 10. Testing

### Unit Testing
```typescript
// pages/home/home.page.spec.ts
import { TestBed } from '@angular/core/testing';
import { HomePage } from './home.page';

describe('HomePage', () => {
  let component: HomePage;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [HomePage]
    }).compileComponents();

    component = TestBed.createComponent(HomePage).componentInstance;
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should load data on init', () => {
    spyOn(component, 'loadData');
    component.ngOnInit();
    expect(component.loadData).toHaveBeenCalled();
  });
});
```

### E2E Testing
```bash
# Install Appium
npm install -g appium

# Run E2E tests
npm run e2e
```

---

## 📱 11. Platform-Specific Features

### Android Customization
```xml
<!-- android/app/src/main/res/values/strings.xml -->
<resources>
    <string name="app_name">LDR Couple</string>
    <string name="title_activity_main">LDR Couple</string>
    <string name="package_name">com.yourname.ldrcoupleapp</string>
    <string name="custom_url_scheme">ldrcoupleapp</string>
</resources>
```

### iOS Customization
```xml
<!-- ios/App/App/Info.plist -->
<key>CFBundleDisplayName</key>
<string>LDR Couple</string>
<key>NSCameraUsageDescription</key>
<string>To take photos for your memories and timeline</string>
<key>NSPhotoLibraryUsageDescription</key>
<string>To select photos from your gallery</string>
```

---

## 🎯 12. Development Roadmap (Ionic Version)

### Phase 1: MVP (6-8 weeks)
**Week 1-2: Setup**
- Ionic project setup
- Laravel API integration
- Authentication flow
- Basic navigation

**Week 3-4: Core Features**
- Timeline (view, create, upload images)
- Mood check-in
- Countdown widget
- Push notifications setup

**Week 5-6: Financial Features**
- Savings tracker
- Transaction management
- Charts integration

**Week 7-8: Testing & Polish**
- Device testing (Android & iOS)
- Performance optimization
- Bug fixes
- TestFlight & Internal Testing

### Phase 2: Engagement (4-5 weeks)
- Love letters
- Memory vault
- Question of the day
- Real-time sync

### Phase 3: Wedding Planning (3-4 weeks)
- Budget planner
- Expense tracking
- Reports

### Phase 4: Polish (2-3 weeks)
- Offline mode
- Live updates
- Analytics
- Production release

---

## 💰 13. Cost Estimation

### Development
- **Developer time**: 15-20 weeks @ $30-50/hr = $18,000 - $40,000 (if hiring)
- **Self-development**: Free (your time)

### Third-party Services
- **Laravel Hosting**: $10-50/month (DigitalOcean/AWS)
- **Database**: Included
- **Storage (S3)**: $5-20/month
- **Push Notifications (FCM)**: Free
- **Real-time (Pusher)**: $49/month atau gratis pakai Laravel Websockets
- **Domain**: $12/year
- **SSL**: Free (Let's Encrypt)

### App Store Fees
- **Google Play**: $25 one-time
- **Apple App Store**: $99/year

**Total estimated**: $50-100/month + one-time fees

---

## ✅ 14. Advantages of Ionic + Capacitor for This Project

1. **Perfect for Small Team/Solo**: One developer bisa handle iOS + Android
2. **Rapid Development**: Familiar web tech, fast iteration
3. **Cost-Effective**: No separate iOS/Android codebases
4. **Easy Updates**: Deploy updates tanpa app store review (via Live Updates)
5. **Web Deployment**: Bonus PWA dari codebase yang sama
6. **Native Features**: Full access via Capacitor plugins
7. **Great UI**: Ionic components sudah mobile-optimized
8. **Large Community**: Banyak resources & plugins
9. **TypeScript**: Type safety & better tooling
10. **Hot Reload**: Fast development cycle

---

## 🚀 Quick Start Commands

```bash
# Create new Ionic app
ionic start ldr-couple-app blank --type=angular --capacitor

# Install dependencies
cd ldr-couple-app
npm install axios @capacitor/camera @capacitor/storage @capacitor/push-notifications

# Add platforms
ionic cap add android
ionic cap add ios

# Run in browser
ionic serve

# Run on device
ionic cap run android --livereload

# Build for production
ionic build --prod
ionic cap sync
```

---

**End of Document** 🚀

Ionic + Capacitor adalah pilihan sempurna untuk project LDR couple app ini karena:
- ✅ Single developer friendly
- ✅ Faster time to market
- ✅ Cost effective
- ✅ Easy maintenance
- ✅ Native-like experience
- ✅ Web bonus

Good luck with your project! ❤️
