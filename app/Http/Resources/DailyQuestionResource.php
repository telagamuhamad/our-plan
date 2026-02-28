<?php

namespace App\Http\Resources;

use App\Models\DailyQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DailyQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        $couple = $user?->couple;

        // Determine which answer mode belongs to current user
        $myAnswerMode = null;
        $partnerAnswerMode = null;
        $canAnswerViaApp = true;
        $prefersCall = false;

        if ($user && $couple) {
            $myAnswerMode = $this->getAnswerModeForUser($user);
            $canAnswerViaApp = $this->canUserAnswerViaApp($user);
            $prefersCall = $this->doesUserPreferCall($user);

            // Partner's answer mode (without revealing which user is which)
            if ($couple->isUserOne($user)) {
                $partnerAnswerMode = $this->answer_mode_two ?? 'app';
            } else {
                $partnerAnswerMode = $this->answer_mode_one ?? 'app';
            }
        }

        return [
            'id' => $this->id,
            'question' => $this->question,
            'category' => $this->category,
            'category_label' => $this->category ? DailyQuestion::getCategories()[$this->category] ?? null : null,
            'question_date' => $this->question_date?->format('Y-m-d'),
            'formatted_date' => $this->formatted_date,
            'is_today' => $this->isToday(),

            // Answers
            'answer_one' => $this->answer_one,
            'answered_by_one' => $this->answered_by_one,
            'answered_one_at' => $this->answered_one_at?->format('Y-m-d H:i:s'),
            'answer_mode_one' => $this->answer_mode_one,

            'answer_two' => $this->answer_two,
            'answered_by_two' => $this->answered_by_two,
            'answered_two_at' => $this->answered_two_at?->format('Y-m-d H:i:s'),
            'answer_mode_two' => $this->answer_mode_two,

            'both_answered' => $this->bothAnswered(),
            'both_prefer_call' => $this->bothPreferCall(),

            // Current user info
            'my_answer' => $user ? $this->getAnswerForUser($user) : null,
            'my_answered_at' => $user ? $this->getAnsweredAtForUser($user)?->format('Y-m-d H:i:s') : null,
            'has_answered' => $user ? $this->hasUserAnswered($user) : false,
            'my_answer_mode' => $myAnswerMode,
            'partner_answer_mode' => $partnerAnswerMode,
            'can_answer_via_app' => $canAnswerViaApp,
            'prefers_call' => $prefersCall,
            'can_answer' => $user ? !$this->hasUserAnswered($user) && $this->isToday() && $canAnswerViaApp : false,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),

            // Relationships
            'answerer_one' => UserResource::make($this->whenLoaded('answererOne')),
            'answerer_two' => UserResource::make($this->whenLoaded('answererTwo')),
        ];
    }
}
