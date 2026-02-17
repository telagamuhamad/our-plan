<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'couple_id',
        'question_date',
        'question',
        'category',
        'answer_one',
        'answered_by_one',
        'answered_one_at',
        'answer_two',
        'answered_by_two',
        'answered_two_at',
    ];

    protected $casts = [
        'question_date' => 'date',
        'answered_one_at' => 'datetime',
        'answered_two_at' => 'datetime',
    ];

    /**
     * The couple that owns this question.
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * User who answered as user one.
     */
    public function answererOne()
    {
        return $this->belongsTo(User::class, 'answered_by_one');
    }

    /**
     * User who answered as user two.
     */
    public function answererTwo()
    {
        return $this->belongsTo(User::class, 'answered_by_two');
    }

    /**
     * Check if the question is for today.
     */
    public function isToday(): bool
    {
        return $this->question_date->isToday();
    }

    /**
     * Get answer for specific user.
     */
    public function getAnswerForUser(User $user): ?string
    {
        $couple = $user->couple;
        if (!$couple) {
            return null;
        }

        if ($couple->isUserOne($user)) {
            return $this->answer_one;
        }

        if ($couple->isUserTwo($user)) {
            return $this->answer_two;
        }

        return null;
    }

    /**
     * Get answer timestamp for specific user.
     */
    public function getAnsweredAtForUser(User $user): ?\Carbon\Carbon
    {
        $couple = $user->couple;
        if (!$couple) {
            return null;
        }

        if ($couple->isUserOne($user)) {
            return $this->answered_one_at;
        }

        if ($couple->isUserTwo($user)) {
            return $this->answered_two_at;
        }

        return null;
    }

    /**
     * Check if user has answered.
     */
    public function hasUserAnswered(User $user): bool
    {
        return $this->getAnswerForUser($user) !== null;
    }

    /**
     * Check if both partners have answered.
     */
    public function bothAnswered(): bool
    {
        return $this->answer_one !== null && $this->answer_two !== null;
    }

    /**
     * Get formatted date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->question_date->translatedFormat('l, d F Y');
    }

    /**
     * Get available question categories.
     */
    public static function getCategories(): array
    {
        return [
            'romantic' => '💕 Romantis',
            'fun' => '😂 Seru',
            'deep' => '🤔 Dalam',
            'future' => '🌟 Masa Depan',
            'memories' => '💭 Kenangan',
            'preferences' => '🎯 Preferensi',
        ];
    }

    /**
     * Get bank of predefined questions.
     */
    public static function getQuestionBank(): array
    {
        return [
            'romantic' => [
                'Apa hal pertama yang bikin kamu jatuh cinta sama aku?',
                'Kalau bisa describe hubungan kita dalam satu kata, kata apa?',
                'Apa momen favorit kita bersama sejauh ini?',
                'Apa yang paling kamu kangen dari aku pas kita LDR?',
                'Kalau bisa kasih aku satu nickname baru, apa?',
            ],
            'fun' => [
                'Kalau kita bisa teleport sekarang, kamu mau ke mana sama aku?',
                'Apa reaksi kamu kalau tiba-tiba aku muncul di depan pintu sekarang?',
                'Film apa yang pengin banget kita tonton bareng nanti?',
                'Kalau kita punya waktu 24 jam bareng, aktivitas apa yang kamu mau?',
                'Apa makanan favorit kamu yang pengin aku masakin?',
            ],
            'deep' => [
                'Apa yang kamu pelajari tentang diri kamu selama hubungan kita?',
                'Apa harapan buat hubungan kita ke depan?',
                'Apa hal yang membuat kamu merasa paling dicintai sama aku?',
                'Kalau ada satu hal yang bisa di-improve dari hubungan kita, apa?',
                'Apa arti "committed" buat kamu?',
            ],
            'future' => [
                'Apa yang paling kamu nanti-nantikan dari ketemuan kita selanjutnya?',
                'Apa target bareng yang pengin kita capai tahun ini?',
                'Kalau bisa bahas rencana 5 tahun ke depan, kamu lihat aku di mana?',
                'Apa pengalaman baru yang pengin kita coba bareng?',
                'Apa goals pribadi kamu yang bisa aku support?',
            ],
            'memories' => [
                'Apa kenangan paling lucu kita bersama?',
                'Apa momen yang nggak kamu lupa dari first date kita?',
                'Apa hal kecil yang aku lakuin yang ternyata kamu inget?',
                'Kenangan mana yang paling sering kamu pikirin pas kangen?',
                'Apa first impression kamu pas pertama kali ketemu aku?',
            ],
            'preferences' => [
                'Apa love language kamu yang paling dominant?',
                'Kamu lebih suka surprise besar atau kecil-kecil terus?',
                'Apa yang bikin kamu merasa paling nyaman dalam hubungan?',
                'Kamu lebih suka texting atau video call?',
                'Apa yang bikin kamu senyum sepanjang hari?',
            ],
        ];
    }

    /**
     * Get random question from bank.
     */
    public static function getRandomQuestion(?string $category = null): array
    {
        $bank = self::getQuestionBank();

        if ($category && isset($bank[$category])) {
            $questions = $bank[$category];
        } else {
            // Flatten all categories
            $questions = array_merge(...array_values($bank));
        }

        return [
            'question' => $questions[array_rand($questions)],
            'category' => $category ?? array_rand($bank),
        ];
    }
}
