<?php

namespace App\Services;

use App\Models\DailyQuestionTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuestionGeneratorService
{
    private string $provider;
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    // Provider configurations
    private const PROVIDERS = [
        'zai' => [
            'base_url' => 'https://api.z.ai/api/paas/v4/', // Correct endpoint for z.ai
            'model' => 'glm-4.7', // GLM-4.7 model
        ],
        'groq' => [
            'base_url' => 'https://api.groq.com/openai/v1/',
            'model' => 'llama-3.3-70b-versatile', // or 'mixtral-8x7b-32768'
        ],
        'gemini' => [
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta/models/',
            'model' => 'gemini-2.0-flash',
        ],
        'openai' => [
            'base_url' => 'https://api.openai.com/v1/',
            'model' => 'gpt-4o-mini',
        ],
    ];

    public function __construct()
    {
        $this->provider = config('services.question_generator.provider', 'zai');
        $this->apiKey = $this->getApiKey();

        if (!isset(self::PROVIDERS[$this->provider])) {
            throw new \Exception("Unsupported provider: {$this->provider}. Available: " . implode(', ', array_keys(self::PROVIDERS)));
        }

        $config = self::PROVIDERS[$this->provider];
        $this->baseUrl = $config['base_url'];
        $this->model = config('services.question_generator.model') ?? $config['model'];
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
            $response = $this->callAI($prompt);
            $question = $this->extractQuestion($response, $locale);

            return [
                'question' => $question,
                'category' => $category,
                'locale' => $locale,
                'ai_model' => $this->model,
                'ai_response' => $response,
                'is_fallback' => false,
                'provider' => $this->provider,
            ];
        } catch (\Exception $e) {
            Log::error('Question Generator Error: ' . $e->getMessage(), [
                'provider' => $this->provider,
                'category' => $category,
                'locale' => $locale,
            ]);

            return $this->getFallbackQuestion($category);
        }
    }

    /**
     * Build the prompt for AI.
     */
    private function buildPrompt(string $category, string $categoryDescription, string $locale): string
    {
        $languageInstruction = $locale === 'id'
            ? 'Jawab dalam Bahasa Indonesia yang santai, natural, dan seperti percakapan sehari-hari.'
            : 'Answer in casual, natural, conversational English.';

        $currentDate = now()->translatedFormat('l, d F Y');
        $dayOfWeek = now()->dayOfWeek;

        $contextHint = '';
        if ($dayOfWeek === 5 || $dayOfWeek === 6) {
            $contextHint = 'Hari ini menjelang akhir pekan, pertanyaan boleh sedikit lebih playful.';
        } elseif ($dayOfWeek === 0) {
            $contextHint = 'Hari ini hari Minggu, buat suasana yang lebih relaxed dan reflective.';
        }

        return <<<PROMPT
Kamu adalah assistant yang kreatif dan romantis untuk aplikasi couple pasangan LDR. Tugas kamu adalah membuat SATU pertanyaan yang UNIK, KREATIF, dan PERSONAL untuk pasangan LDR.

📅 Tanggal hari ini: {$currentDate}
🎯 Kategori pertanyaan: {$category}
📝 Deskripsi kategori: {$categoryDescription}

{$languageInstruction}

{$contextHint}

🎨 KREATIVE GUIDELINES:
1. Buat pertanyaan yang BENAR-BENAR UNIK - jangan gunakan pertanyaan generik
2. Pertanyaan harus spesifik untuk konteks LDR (long distance relationship)
3. Gunakan imajinasi dan kreativitas - pertanyaan harus membuat pasangan berpikir dan senyum
4. Pertanyaan boleh tentang hal-hal kecil yang sepele tapi bermakna
5. Pertanyaan bisa tentang "what if", hipotetikal, atau reflektif
6. Hindari pertanyaan yang terlalu formal atau kaku
7. Jangan terlalu panjang (ideal 10-20 kata)
8. JANGAN gunakan emoji dalam pertanyaan
9. JANGAN berikan penjelasan atau intro, langsung pertanyaan saja

💡 CONTOH (namun jangan copy, buat yang ORIGINAL):
"Apa lagu yang tiba-tiba keputar di kepalamu dan langsung bikin kamu mikir aku?"
"Hal kecil apa yang tiba-tiba bikin kamu kangen di waktu nggak diduga?"

Output HANYA satu pertanyaan, tanpa tanda kutip. Contoh:
Apa lagu yang tiba-tiba keputar di kepalamu dan langsung bikin kamu mikir aku?

PROMPT;
    }

    /**
     * Call the AI API based on provider.
     */
    private function callAI(string $prompt): array
    {
        return match($this->provider) {
            'zai' => $this->callZai($prompt),
            'groq' => $this->callGroq($prompt),
            'gemini' => $this->callGemini($prompt),
            'openai' => $this->callOpenAI($prompt),
            default => throw new \Exception("Unsupported provider: {$this->provider}"),
        };
    }

    /**
     * Call Z.ai API (OpenAI-compatible).
     */
    private function callZai(string $prompt): array
    {
        $url = $this->baseUrl . 'chat/completions';

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 1.0,
            'max_tokens' => 150,
        ];

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($url, $payload);

        if (!$response->successful()) {
            Log::error('Z.ai API Error Details', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url,
                'model' => $this->model,
            ]);
            throw new \Exception('Z.ai API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Call Groq API (OpenAI-compatible).
     */
    private function callGroq(string $prompt): array
    {
        $url = $this->baseUrl . 'chat/completions';

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 1.0,
            'max_tokens' => 150,
        ];

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception('Groq API request failed: ' . $response->body());
        }

        return $response->json();
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
                'temperature' => 1.0,
                'topK' => 64,
                'topP' => 0.95,
                'maxOutputTokens' => 150,
                'candidateCount' => 1,
            ],
        ];

        $response = Http::timeout(30)->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Call OpenAI API.
     */
    private function callOpenAI(string $prompt): array
    {
        $url = $this->baseUrl . 'chat/completions';

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 1.0,
            'max_tokens' => 150,
        ];

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Extract question from AI response based on provider.
     */
    private function extractQuestion(array $response, string $locale): string
    {
        $text = match($this->provider) {
            'zai', 'groq', 'openai' => $response['choices'][0]['message']['content'] ?? '',
            'gemini' => $response['candidates'][0]['content']['parts'][0]['text'] ?? '',
            default => '',
        };

        $question = trim($text);

        // Remove markdown code blocks
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $question, $matches)) {
            $question = $matches[1];
        }

        // Remove quotes
        $question = trim($question, '"\'');

        // Remove common prefixes
        $patternsToRemove = [
            '/^Jawaban:\s*/i',
            '/^Pertanyaan:\s*/i',
            '/^Question:\s*/i',
            '/^Answer:\s*/i',
            '/^Berikut\s+(pertanyaannya|question):\s*/i',
        ];

        foreach ($patternsToRemove as $pattern) {
            $question = preg_replace($pattern, '', $question);
        }

        $question = trim($question);
        $question = rtrim($question, '.!');

        // Ensure proper punctuation
        if (!empty($question) && !in_array(substr($question, -1), ['?', '.', '!'])) {
            if (preg_match('/^(apa|bagaimana|mengapa|kalau|siapa|kapan|dimana|adakah|bukankah)/i', $question)) {
                $question .= '?';
            } else {
                $question .= '.';
            }
        }

        return $question;
    }

    /**
     * Get fallback question when API fails.
     */
    private function getFallbackQuestion(string $preferredCategory): array
    {
        $fallbackQuestions = [
            'romantic' => [
                'Apa yang bikin kamu senyum hari ini karena mikir aku?',
                'Hal apa yang paling kamu kangen dari aku?',
                'Apa moment terbaik kita yang belum pernah luput dari pikiranmu?',
            ],
            'fun' => [
                'Kalau bisa teleport sekarang, tempat apa yang paling pengin kamu kunjungi bareng aku?',
                'Apa reaksi kamu kalau tiba-tiba aku muncul depan pintu sekarang?',
                'Aktivitas apa yang paling pengin kamu lakuin kalau kita ketemuan hari ini?',
            ],
            'deep' => [
                'Apa yang kamu pelajari tentang diri kamu sejak bersama aku?',
                'Apa yang bikin kamu merasa paling dicintai dan dihargai?',
                'Apa harapanmu yang sebenarnya tentang hubungan kita?',
            ],
            'future' => [
                'Apa yang paling kamu nanti-nantikan dari pertemuan kita selanjutnya?',
                'Ada nggak target bareng yang pengin kamu capai sama aku tahun ini?',
                'Kamu lihat kita masih di mana dalam 5 tahun ke depan?',
            ],
            'memories' => [
                'Kenangan mana yang paling sering kamu inget pas lagi kangen?',
                'Apa hal kecil yang aku pernah lakuin yang ternyata kamu inget sampe sekarang?',
                'Moment apa yang nggak pernah kamu lupa dari kita berdua?',
            ],
            'preferences' => [
                'Apa yang bikin kamu paling nyaman waktu bareng aku?',
                'Kamu lebih suka cara aku nunjukkin perhatian dengan cara apa?',
                'Hal apa yang sebenarnya pengin kamu lebih sering kita lakuin bareng?',
            ],
        ];

        $category = $preferredCategory;
        if (!isset($fallbackQuestions[$category])) {
            $category = array_rand($fallbackQuestions);
        }

        $questions = $fallbackQuestions[$category];
        $question = $questions[array_rand($questions)];

        return [
            'question' => $question,
            'category' => $category,
            'locale' => 'id',
            'ai_model' => null,
            'ai_response' => null,
            'is_fallback' => true,
            'provider' => 'fallback',
        ];
    }

    /**
     * Get API key based on provider.
     */
    private function getApiKey(): string
    {
        return match($this->provider) {
            'zai' => config('services.question_generator.zai_api_key') ?: env('ZAI_API_KEY'),
            'groq' => config('services.question_generator.groq_api_key') ?: env('GROQ_API_KEY'),
            'gemini' => config('services.question_generator.gemini_api_key') ?: env('GEMINI_API_KEY'),
            'openai' => config('services.question_generator.openai_api_key') ?: env('OPENAI_API_KEY'),
            default => throw new \Exception("No API key configured for provider: {$this->provider}"),
        };
    }

    /**
     * Check if the service is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get the current provider being used.
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Get the current model being used.
     */
    public function getModel(): string
    {
        return $this->model;
    }
}
