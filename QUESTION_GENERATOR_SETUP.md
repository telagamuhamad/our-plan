# Question Generator Setup

This app supports multiple AI providers for generating daily questions. Choose the one that fits your needs best!

## 🌟 Current: Z.ai (Subscribed)

**Why Z.ai?**
- ✅ Already subscribed!
- ✅ Access to multiple models (GPT-4o, Claude, Gemini)
- ✅ Good rate limits for paid plan
- ✅ Unified API for multiple AI models

### Setup Z.ai (Already Configured):

Your `.env` is already configured with:
```bash
QUESTION_GENERATOR_PROVIDER=zai
ZAI_API_KEY=87020f820dcf44a5af6d347c7e9e2ed4.VyTpsqUKRcdAg1Uj
```

### Available Z.ai Models:
- `gpt-4o` (default, most capable)
- `gpt-4o-mini` (faster, cheaper)
- `claude-3-5-sonnet` (great for creative tasks)
- `gemini-2.0-flash` (fast)

To change model:
```bash
QUESTION_GENERATOR_MODEL=claude-3-5-sonnet
```

---

## 🆓 Free Alternative: Groq

**Why Groq?**
- ✅ Completely FREE with generous rate limits
- ✅ Very fast inference (world's fastest!)
- ✅ No credit card required for free tier
- ✅ Supports powerful models like Llama 3.3 70B

### Setup Groq (if needed as backup):

1. **Get API Key:**
   - Go to [https://console.groq.com/](https://console.groq.com/)
   - Sign up (free)
   - Go to API Keys section
   - Create new API key

2. **Configure `.env`:**
   ```bash
   QUESTION_GENERATOR_PROVIDER=groq
   GROQ_API_KEY=your_groq_api_key_here
   ```

### Available Groq Models:
- `llama-3.3-70b-versatile` (default, great balance)
- `mixtral-8x7b-32768` (good for creative tasks)
- `llama-3.1-8b-instant` (faster, lighter)

To change model:
```bash
QUESTION_GENERATOR_MODEL=mixtral-8x7b-32768
```

---

**Why Groq?**
- ✅ Completely FREE with generous rate limits
- ✅ Very fast inference (world's fastest!)
- ✅ No credit card required for free tier
- ✅ Supports powerful models like Llama 3.3 70B

### Setup Groq:

1. **Get API Key:**
   - Go to [https://console.groq.com/](https://console.groq.com/)
   - Sign up (free)
   - Go to API Keys section
   - Create new API key

2. **Configure `.env`:**
   ```bash
   QUESTION_GENERATOR_PROVIDER=groq
   GROQ_API_KEY=your_groq_api_key_here
   ```

### Available Groq Models:
- `llama-3.3-70b-versatile` (default, great balance)
- `mixtral-8x7b-32768` (good for creative tasks)
- `llama-3.1-8b-instant` (faster, lighter)

To change model:
```bash
QUESTION_GENERATOR_MODEL=mixtral-8x7b-32768
```

---

## 📊 Comparison

| Provider | Pricing | Speed | Quality | Setup |
|----------|---------|-------|---------|-------|
| **Z.ai** ⭐ | Subscribed | ⭐⭐⭐⭐ Fast | ⭐⭐⭐⭐⭐ Best | ✅ Done |
| Groq | Free | ⭐⭐⭐⭐⭐ Fastest | ⭐⭐⭐⭐ Excellent | Easy |
| OpenAI | $5 free then paid | ⭐⭐⭐⭐ Fast | ⭐⭐⭐⭐⭐ Best | Moderate |
| Gemini | Limited free | ⭐⭐⭐⭐ Fast | ⭐⭐⭐⭐ Very Good | Moderate |

---

**Pros:** High quality, reliable
**Cons:** $5 free credits only, then paid

1. Get API key from [platform.openai.com](https://platform.openai.com/)
2. Configure `.env`:
   ```bash
   QUESTION_GENERATOR_PROVIDER=openai
   OPENAI_API_KEY=your_openai_api_key_here
   ```

### Gemini (Google)

**Pros:** Good free tier
**Cons:** Can hit quota limits

1. Get API key from [ai.google.dev](https://ai.google.dev/)
2. Configure `.env`:
   ```bash
   QUESTION_GENERATOR_PROVIDER=gemini
   GEMINI_API_KEY=your_gemini_api_key_here
   ```

---

---

## 🧪 Test Your Setup

Run the command to generate today's question:

```bash
php artisan app:generate-daily-question
```

Or generate for specific date:

```bash
php artisan app:generate-daily-question --date=2026-02-22 --category=romantic
```

---

## 🔧 Troubleshooting

### "API key is not configured"
- Make sure you added the API key to `.env`
- Check that `QUESTION_GENERATOR_PROVIDER` is set correctly
- Run `php artisan config:clear` after changing `.env`

### "Quota exceeded"
- For Z.ai: Check your subscription status
- For Groq: Switch to Groq as backup (it's free!)
- **Solution:** Update `.env`: `QUESTION_GENERATOR_PROVIDER=groq`

### Fallback questions being used
- Check your logs: `storage/logs/laravel.log`
- Verify API key is correct
- Test API key directly with provider's console

---

## 💡 Tips

Run the command to generate today's question:

```bash
php artisan app:generate-daily-question
```

Or generate for specific date:

```bash
php artisan app:generate-daily-question --date=2026-02-22 --category=romantic
```

---

## 🔧 Troubleshooting

### "API key is not configured"
- Make sure you added the API key to `.env`
- Check that `QUESTION_GENERATOR_PROVIDER` is set correctly
- Run `php artisan config:clear` after changing `.env`

### "Quota exceeded"
- For Gemini: Wait a few hours or switch to Groq
- For OpenAI: Check your billing
- **Solution:** Switch to Groq! 🚀

### Fallback questions being used
- Check your logs: `storage/logs/laravel.log`
- Verify API key is correct
- Test API key directly with provider's console

---

## 💡 Tips

1. **Start with Groq** - It's free, fast, and no credit card needed!

2. **Schedule daily generation:**
   Add to `app/Console/Kernel.php`:
   ```php
   $schedule->command('app:generate-daily-question')->daily();
   ```

3. **Generate questions ahead:**
   ```bash
   php artisan app:generate-daily-question --days=7
   ```

4. **Regenerate if not happy:**
   ```bash
   php artisan app:generate-daily-question --force
   ```
