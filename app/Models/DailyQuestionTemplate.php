<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyQuestionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_date',
        'question',
        'category',
        'locale',
        'ai_model',
        'ai_response',
        'is_fallback',
    ];

    protected $casts = [
        'question_date' => 'date',
        'ai_response' => 'array',
        'is_fallback' => 'boolean',
    ];

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
     * Get category descriptions for AI prompt.
     */
    public static function getCategoryDescriptions(): array
    {
        return [
            'romantic' => 'Pertanyaan tentang cinta, kasih sayang, dan kekaguman satu sama lain',
            'fun' => 'Pertanyaan seru, lucu, dan ringan untuk bikin suasana happy',
            'deep' => 'Pertanyaan mendalam tentang perasaan, nilai, dan pemikiran',
            'future' => 'Pertanyaan tentang rencana, harapan, dan goals masa depan bersama',
            'memories' => 'Pertanyaan tentang kenangan indah, momen berharga, dan pengalaman bersama',
            'preferences' => 'Pertanyaan tentang preferensi pribadi, love language, dan kebiasaan',
        ];
    }

    /**
     * Get today's template.
     */
    public static function getToday(): ?self
    {
        return static::where('question_date', now()->toDateString())->first();
    }

    /**
     * Get template by date.
     */
    public static function getByDate(string $date): ?self
    {
        return static::where('question_date', $date)->first();
    }

    /**
     * Get or create template for a specific date.
     */
    public static function getOrCreateForDate(string $date): self
    {
        $template = static::getByDate($date);

        if (!$template) {
            // Use fallback from static question bank
            $random = DailyQuestion::getRandomQuestion();
            $template = static::create([
                'question_date' => $date,
                'question' => $random['question'],
                'category' => $random['category'],
                'locale' => 'id',
                'is_fallback' => true,
            ]);
        }

        return $template;
    }

    /**
     * Get formatted date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->question_date->translatedFormat('l, d F Y');
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::getCategories()[$this->category] ?? $this->category;
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, string $start, string $end)
    {
        return $query->whereBetween('question_date', [$start, $end]);
    }

    /**
     * Scope to get only AI generated questions.
     */
    public function scopeAiGenerated($query)
    {
        return $query->where('is_fallback', false);
    }

    /**
     * Scope to get only fallback questions.
     */
    public function scopeFallback($query)
    {
        return $query->where('is_fallback', true);
    }
}
