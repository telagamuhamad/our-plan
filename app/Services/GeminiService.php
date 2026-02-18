<?php

namespace App\Services;

use App\Models\DailyQuestionTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
        $this->model = config('services.gemini.model', 'gemini-2.0-flash-exp');
    }

    /**
     * Generate a daily question for couples.
     */
    public function generateDailyQuestion(?string $category = null, string $locale = 'id'): array
    {
        // If no category specified, pick random
        if (!$category) {
            $categories = array_keys(DailyQuestionTemplate::getCategories());
            $category = $categories[array_rand($categories)];
        }

        $categoryDescription = DailyQuestionTemplate::getCategoryDescriptions()[$category] ?? $category;

        $prompt = $this->buildPrompt($category, $categoryDescription, $locale);

        try {
            $response = $this->callGemini($prompt);

            // Extract question from AI response
            $question = $this->extractQuestion($response, $locale);

            return [
                'question' => $question,
                'category' => $category,
                'locale' => $locale,
                'ai_model' => $this->model,
                'ai_response' => $response,
                'is_fallback' => false,
            ];
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage(), [
                'category' => $category,
                'locale' => $locale,
            ]);

            // Fallback to static question bank
            return $this->getFallbackQuestion($category);
        }
    }

    /**
     * Build the prompt for AI.
     */
    private function buildPrompt(string $category, string $categoryDescription, string $locale): string
    {
        $languageInstruction = $locale === 'id'
            ? 'Jawab dalam Bahasa Indonesia yang santai dan natural.'
            : 'Answer in casual and natural English.';

        return <<<PROMPT
Kamu adalah assistant yang kreatif untuk aplikasi couple. Tugas kamu adalah membuat satu pertanyaan untuk pasangan (couple) LDR.

Kategori pertanyaan: {$category}
Deskripsi kategori: {$categoryDescription}

{$languageInstruction}

Rules:
1. Buat SATU pertanyaan saja yang menarik dan engaging
2. Pertanyaan harus relevan untuk pasangan LDR
3. Hindari pertanyaan yang sudah umum/bosan
4. Jangan terlalu panjang (maksimal 20 kata)
5. Jangan gunakan emoji dalam pertanyaan
6. Jawab HANYA dengan pertanyaan, tanpa penjelasan tambahan

Contoh format jawaban yang benar:
"Apa hal kecil yang kamu lakukan hari ini yang bikin kamu mikir aku?"

PROMPT;
    }

    /**
     * Call Gemini API.
     */
    private function callGemini(string $prompt): array
    {
        $url = $this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.9,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 100,
            ],
        ];

        $response = Http::timeout(30)->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Extract question from AI response.
     */
    private function extractQuestion(array $response, string $locale): string
    {
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Clean up the response
        $question = trim($text);

        // Remove quotes if present
        $question = trim($question, '"\'');

        // Remove common prefixes
        $prefixesToRemove = ['Jawaban:', 'Pertanyaan:', 'Question:', 'Answer:'];
        foreach ($prefixesToRemove as $prefix) {
            if (str_starts_with($question, $prefix)) {
                $question = trim(substr($question, strlen($prefix)));
            }
        }

        // Remove trailing punctuation that's excessive
        $question = rtrim($question, '.!');

        return $question;
    }

    /**
     * Get fallback question from static bank.
     */
    private function getFallbackQuestion(string $preferredCategory): array
    {
        $random = DailyQuestionTemplate::getCategories();

        // Try to get from preferred category first
        $staticBank = \App\Models\DailyQuestion::getQuestionBank();
        if (isset($staticBank[$preferredCategory])) {
            $questions = $staticBank[$preferredCategory];
            $question = $questions[array_rand($questions)];
        } else {
            // Fallback to any category
            $category = array_rand($staticBank);
            $questions = $staticBank[$category];
            $question = $questions[array_rand($questions)];
            $preferredCategory = $category;
        }

        return [
            'question' => $question,
            'category' => $preferredCategory,
            'locale' => 'id',
            'ai_model' => null,
            'ai_response' => null,
            'is_fallback' => true,
        ];
    }

    /**
     * Check if the API is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get the current model being used.
     */
    public function getModel(): string
    {
        return $this->model;
    }
}
