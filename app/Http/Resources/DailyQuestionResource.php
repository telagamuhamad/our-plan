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

            'answer_two' => $this->answer_two,
            'answered_by_two' => $this->answered_by_two,
            'answered_two_at' => $this->answered_two_at?->format('Y-m-d H:i:s'),

            'both_answered' => $this->bothAnswered(),

            // Current user info
            'my_answer' => Auth::check() ? $this->getAnswerForUser(Auth::user()) : null,
            'has_answered' => Auth::check() ? $this->hasUserAnswered(Auth::user()) : false,
            'can_answer' => Auth::check() ? !$this->hasUserAnswered(Auth::user()) && $this->isToday() : false,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),

            // Relationships
            'answerer_one' => UserResource::make($this->whenLoaded('answererOne')),
            'answerer_two' => UserResource::make($this->whenLoaded('answererTwo')),
        ];
    }
}
