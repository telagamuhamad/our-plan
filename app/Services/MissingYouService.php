<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\Notification;
use App\Models\User;
use Exception;

class MissingYouService
{
    /**
     * Send a "Missing You" notification to the partner.
     *
     * @throws Exception If validation fails.
     */
    public function sendMissingYou(User $user, ?string $message = null): Notification
    {
        $couple = $user->couple;

        if (!$couple || !$couple->isActive()) {
            throw new Exception('Anda belum memiliki pasangan aktif');
        }

        $partner = $couple->getPartner($user);

        if (!$partner) {
            throw new Exception('Pasangan tidak ditemukan');
        }

        // Create customizable messages or use the provided one
        $notificationMessage = $message ?? $this->getRandomMessage($user);

        return Notification::create([
            'user_id' => $partner->id,
            'couple_id' => $couple->id,
            'actor_id' => $user->id,
            'type' => 'missing_you',
            'title' => 'Missing You! 💕',
            'message' => $notificationMessage,
            'is_read' => false,
        ]);
    }

    /**
     * Check if user can send "Missing You" (rate limiting).
     * Allow maximum 3 times per hour.
     */
    public function canSendMissingYou(User $user): bool
    {
        $oneHourAgo = now()->subHour();

        $recentCount = Notification::where('type', 'missing_you')
            ->where('actor_id', $user->id)
            ->where('created_at', '>=', $oneHourAgo)
            ->count();

        return $recentCount < 3;
    }

    /**
     * Get remaining "Missing You" quota for the current hour.
     */
    public function getRemainingQuota(User $user): int
    {
        $oneHourAgo = now()->subHour();

        $recentCount = Notification::where('type', 'missing_you')
            ->where('actor_id', $user->id)
            ->where('created_at', '>=', $oneHourAgo)
            ->count();

        return max(0, 3 - $recentCount);
    }

    /**
     * Get time until next "Missing You" is available.
     */
    public function getTimeUntilNextAvailable(User $user): ?int
    {
        if ($this->canSendMissingYou($user)) {
            return null;
        }

        $oldestRecent = Notification::where('type', 'missing_you')
            ->where('actor_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->orderBy('created_at')
            ->first();

        if (!$oldestRecent) {
            return null;
        }

        $availableAt = $oldestRecent->created_at->addHour();
        $secondsRemaining = now()->diffInSeconds($availableAt, false);

        return $secondsRemaining > 0 ? $secondsRemaining : 0;
    }

    /**
     * Get "Missing You" history between couple.
     */
    public function getMissingYouHistory(Couple $couple, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::where('couple_id', $couple->id)
            ->where('type', 'missing_you')
            ->with('actor')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get random "Missing You" message.
     */
    protected function getRandomMessage(User $user): string
    {
        $messages = [
            "{$user->name} merindukanmu! 🥺",
            "{$user->name} mengirimkan peluk virtual! 🤗",
            "Ada yang rindu nih... {$user->name} kangen banget! 💕",
            "{$user->name} lagi mikirin kamu sekarang! 🥰",
            "Dari {$user->name}: Aku kangen kamu! ❤️",
            "{$user->name} mau peluk kamu sekarang! 🫂",
            "Hayang ah (hayang jumpa) - {$user->name} 👋",
        ];

        return $messages[array_rand($messages)];
    }

    /**
     * Get all available message templates.
     */
    public function getMessageTemplates(): array
    {
        return [
            'default' => '{name} merindukanmu! 🥺',
            'hug' => '{name} mengirimkan peluk virtual! 🤗',
            'thinking' => '{name} lagi mikirin kamu sekarang! 🥰',
            'love' => 'Dari {name}: Aku kangen kamu! ❤️',
            'hug_now' => '{name} mau peluk kamu sekarang! 🫂',
            'hayang' => 'Hayang ah (hayang jumpa) - {name} 👋',
        ];
    }
}
