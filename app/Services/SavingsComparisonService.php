<?php

namespace App\Services;

use App\Models\Saving;
use App\Models\SavingTransaction;
use App\Models\User;
use App\Models\Couple;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SavingsComparisonService
{
    /**
     * Get comparison data between user and partner
     */
    public function getComparisonData(User $user)
    {
        $couple = $user->couple;

        if (!$couple) {
            return null;
        }

        $partner = $couple->getPartner($user);

        if (!$partner) {
            return null;
        }

        return [
            'users' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                ],
                'partner' => [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'avatar_url' => $partner->avatar_url,
                ],
            ],
            'overview' => $this->getOverviewComparison($user, $partner),
            'savings_list' => $this->getSavingsListComparison($user, $partner),
            'monthly_contributions' => $this->getMonthlyContributions($user, $partner),
            'by_category' => $this->getCategoryComparison($user, $partner),
            'goals_progress' => $this->getGoalsProgressComparison($user, $partner),
            'transactions_summary' => $this->getTransactionsSummary($user, $partner),
            'achievements' => $this->getAchievementsComparison($user, $partner),
        ];
    }

    /**
     * Get overview comparison
     */
    protected function getOverviewComparison(User $user, User $partner)
    {
        $userSavings = $this->getUserSavingsData($user);
        $partnerSavings = $this->getUserSavingsData($partner);

        return [
            'user' => [
                'total_savings' => $userSavings['total'],
                'total_target' => $userSavings['target'],
                'completed_count' => $userSavings['completed'],
                'active_count' => $userSavings['active'],
                'completion_rate' => $userSavings['completion_rate'],
                'total_deposits' => $userSavings['deposits'],
                'total_withdrawals' => $userSavings['withdrawals'],
            ],
            'partner' => [
                'total_savings' => $partnerSavings['total'],
                'total_target' => $partnerSavings['target'],
                'completed_count' => $partnerSavings['completed'],
                'active_count' => $partnerSavings['active'],
                'completion_rate' => $partnerSavings['completion_rate'],
                'total_deposits' => $partnerSavings['deposits'],
                'total_withdrawals' => $partnerSavings['withdrawals'],
            ],
            'combined' => [
                'total_savings' => $userSavings['total'] + $partnerSavings['total'],
                'total_target' => $userSavings['target'] + $partnerSavings['target'],
                'user_contribution' => $this->calculateContribution($userSavings['total'], $partnerSavings['total']),
                'partner_contribution' => $this->calculateContribution($partnerSavings['total'], $userSavings['total']),
            ],
            'leader' => $this->determineLeader($userSavings, $partnerSavings),
        ];
    }

    /**
     * Get savings list comparison
     */
    protected function getSavingsListComparison(User $user, User $partner)
    {
        $userSavings = Saving::where('user_id', $user->id)
            ->with('category')
            ->get();

        $partnerSavings = Saving::where('user_id', $partner->id)
            ->with('category')
            ->get();

        // Combine all unique categories
        $allCategories = $userSavings->pluck('category')
            ->merge($partnerSavings->pluck('category'))
            ->filter()
            ->unique('id')
            ->sortBy('name');

        $comparison = [];

        foreach ($allCategories as $category) {
            $userInCategory = $userSavings->where('category_id', $category->id);
            $partnerInCategory = $partnerSavings->where('category_id', $category->id);

            $comparison[] = [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'color' => $category->color,
                ],
                'user' => [
                    'amount' => $userInCategory->sum('current_amount'),
                    'count' => $userInCategory->count(),
                ],
                'partner' => [
                    'amount' => $partnerInCategory->sum('current_amount'),
                    'count' => $partnerInCategory->count(),
                ],
                'leader' => $userInCategory->sum('current_amount') >= $partnerInCategory->sum('current_amount') ? 'user' : 'partner',
            ];
        }

        // Add savings without category
        $userNoCategory = $userSavings->whereNull('category_id');
        $partnerNoCategory = $partnerSavings->whereNull('category_id');

        if ($userNoCategory->count() > 0 || $partnerNoCategory->count() > 0) {
            $comparison[] = [
                'category' => [
                    'id' => null,
                    'name' => 'Uncategorized',
                    'icon' => '📁',
                    'color' => '#6c757d',
                ],
                'user' => [
                    'amount' => $userNoCategory->sum('current_amount'),
                    'count' => $userNoCategory->count(),
                ],
                'partner' => [
                    'amount' => $partnerNoCategory->sum('current_amount'),
                    'count' => $partnerNoCategory->count(),
                ],
                'leader' => $userNoCategory->sum('current_amount') >= $partnerNoCategory->sum('current_amount') ? 'user' : 'partner',
            ];
        }

        return collect($comparison)->sortByDesc(function ($item) {
            return $item['user']['amount'] + $item['partner']['amount'];
        })->values();
    }

    /**
     * Get monthly contributions comparison
     */
    protected function getMonthlyContributions(User $user, User $partner)
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('Y-m');
        }

        $contributions = [];

        foreach ($months as $month) {
            $userDeposits = SavingTransaction::whereHas('savingData', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->where('type', 'deposit')
                ->whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->sum('amount');

            $partnerDeposits = SavingTransaction::whereHas('savingData', function ($q) use ($partner) {
                    $q->where('user_id', $partner->id);
                })
                ->where('type', 'deposit')
                ->whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->sum('amount');

            $contributions[] = [
                'month' => $month,
                'month_formatted' => Carbon::parse($month . '-01')->format('M Y'),
                'user' => $userDeposits,
                'partner' => $partnerDeposits,
                'leader' => $userDeposits >= $partnerDeposits ? 'user' : 'partner',
            ];
        }

        return $contributions;
    }

    /**
     * Get category comparison
     */
    protected function getCategoryComparison(User $user, User $partner)
    {
        $userSavings = Saving::where('user_id', $user->id)->with('category')->get();
        $partnerSavings = Saving::where('user_id', $partner->id)->with('category')->get();

        $categories = $userSavings->pluck('category')
            ->merge($partnerSavings->pluck('category'))
            ->filter()
            ->unique('id');

        return $categories->map(function ($category) use ($userSavings, $partnerSavings) {
            $userInCategory = $userSavings->where('category_id', $category->id);
            $partnerInCategory = $partnerSavings->where('category_id', $category->id);

            return [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'color' => $category->color,
                ],
                'user' => [
                    'amount' => $userInCategory->sum('current_amount'),
                    'percentage' => $this->calculatePercentage($userInCategory->sum('current_amount'), $userSavings->sum('current_amount')),
                ],
                'partner' => [
                    'amount' => $partnerInCategory->sum('current_amount'),
                    'percentage' => $this->calculatePercentage($partnerInCategory->sum('current_amount'), $partnerSavings->sum('current_amount')),
                ],
            ];
        })->values();
    }

    /**
     * Get goals progress comparison
     */
    protected function getGoalsProgressComparison(User $user, User $partner)
    {
        $userGoals = Saving::where('user_id', $user->id)
            ->where('is_shared', false)
            ->whereNotNull('target_amount')
            ->whereNull('completed_at')
            ->with('category')
            ->get();

        $partnerGoals = Saving::where('user_id', $partner->id)
            ->where('is_shared', false)
            ->whereNotNull('target_amount')
            ->whereNull('completed_at')
            ->with('category')
            ->get();

        return [
            'user' => $userGoals->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'progress' => round($goal->progress, 1),
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'target_date' => $goal->target_date?->format('Y-m-d'),
                    'category' => $goal->category ? [
                        'name' => $goal->category->name,
                        'icon' => $goal->category->icon,
                        'color' => $goal->category->color,
                    ] : null,
                ];
            })->sortByDesc('progress')->values(),
            'partner' => $partnerGoals->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'progress' => round($goal->progress, 1),
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'target_date' => $goal->target_date?->format('Y-m-d'),
                    'category' => $goal->category ? [
                        'name' => $goal->category->name,
                        'icon' => $goal->category->icon,
                        'color' => $goal->category->color,
                    ] : null,
                ];
            })->sortByDesc('progress')->values(),
        ];
    }

    /**
     * Get transactions summary comparison
     */
    protected function getTransactionsSummary(User $user, User $partner)
    {
        $userTransactionCount = SavingTransaction::whereHas('savingData', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        $partnerTransactionCount = SavingTransaction::whereHas('savingData', function ($q) use ($partner) {
            $q->where('user_id', $partner->id);
        })->count();

        return [
            'user' => [
                'total_transactions' => $userTransactionCount,
                'avg_transaction_amount' => $this->getAverageTransactionAmount($user),
            ],
            'partner' => [
                'total_transactions' => $partnerTransactionCount,
                'avg_transaction_amount' => $this->getAverageTransactionAmount($partner),
            ],
            'most_active' => $userTransactionCount >= $partnerTransactionCount ? 'user' : 'partner',
        ];
    }

    /**
     * Get achievements comparison
     */
    protected function getAchievementsComparison(User $user, User $partner)
    {
        $userAchievements = $this->getUserAchievements($user);
        $partnerAchievements = $this->getUserAchievements($partner);

        return [
            'user' => $userAchievements,
            'partner' => $partnerAchievements,
            'total_badges' => [
                'user' => count($userAchievements),
                'partner' => count($partnerAchievements),
            ],
        ];
    }

    /**
     * Get user achievements
     */
    protected function getUserAchievements(User $user)
    {
        $achievements = [];
        $savings = Saving::where('user_id', $user->id)->get();
        $transactions = SavingTransaction::whereHas('savingData', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->get();

        // First savings
        if ($savings->count() > 0) {
            $achievements[] = [
                'icon' => '🎯',
                'name' => 'First Goal',
                'description' => 'Created first savings goal',
            ];
        }

        // Multiple savings
        if ($savings->count() >= 3) {
            $achievements[] = [
                'icon' => '📚',
                'name' => 'Goal Setter',
                'description' => 'Created 3 or more savings goals',
            ];
        }

        // First deposit
        if ($transactions->where('type', 'deposit')->count() > 0) {
            $achievements[] = [
                'icon' => '💰',
                'name' => 'First Saver',
                'description' => 'Made first deposit',
            ];
        }

        // Consistent saver
        $depositCount = $transactions->where('type', 'deposit')->count();
        if ($depositCount >= 10) {
            $achievements[] = [
                'icon' => '🏆',
                'name' => 'Consistent Saver',
                'description' => 'Made 10 or more deposits',
            ];
        }

        // Goal achiever
        $completedCount = $savings->whereNotNull('completed_at')->count();
        if ($completedCount > 0) {
            $achievements[] = [
                'icon' => '🌟',
                'name' => 'Goal Achiever',
                'description' => "Completed {$completedCount} goal(s)",
            ];
        }

        // Millionaire
        $totalAmount = $savings->sum('current_amount');
        if ($totalAmount >= 1000000000) {
            $achievements[] = [
                'icon' => '💎',
                'name' => 'Savings Champion',
                'description' => 'Accumulated 1 billion or more',
            ];
        }

        return $achievements;
    }

    /**
     * Get user savings data
     */
    protected function getUserSavingsData(User $user)
    {
        $savings = Saving::where('user_id', $user->id)->get();

        return [
            'total' => $savings->sum('current_amount'),
            'target' => $savings->where('is_shared', false)->sum('target_amount'),
            'completed' => $savings->whereNotNull('completed_at')->count(),
            'active' => $savings->whereNull('completed_at')->count(),
            'completion_rate' => $savings->where('is_shared', false)->count() > 0
                ? round(($savings->whereNotNull('completed_at')->count() / $savings->where('is_shared', false)->count()) * 100, 1)
                : 0,
            'deposits' => SavingTransaction::whereHas('savingData', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('type', 'deposit')->sum('amount'),
            'withdrawals' => SavingTransaction::whereHas('savingData', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('type', 'withdrawal')->sum('amount'),
        ];
    }

    /**
     * Calculate contribution percentage
     */
    protected function calculateContribution($amount, $otherAmount)
    {
        $total = $amount + $otherAmount;
        if ($total == 0) return 50;
        return round(($amount / $total) * 100, 1);
    }

    /**
     * Calculate percentage
     */
    protected function calculatePercentage($amount, $total)
    {
        if ($total == 0) return 0;
        return round(($amount / $total) * 100, 1);
    }

    /**
     * Determine leader in savings
     */
    protected function determineLeader($userData, $partnerData)
    {
        $userScore = $userData['total'] + ($userData['completed'] * 1000000);
        $partnerScore = $partnerData['total'] + ($partnerData['completed'] * 1000000);

        if ($userScore > $partnerScore) return 'user';
        if ($partnerScore > $userScore) return 'partner';
        return 'tie';
    }

    /**
     * Get average transaction amount
     */
    protected function getAverageTransactionAmount(User $user)
    {
        return SavingTransaction::whereHas('savingData', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->avg('amount') ?? 0;
    }
}
