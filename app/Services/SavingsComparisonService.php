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

        $combinedDeposits = $userSavings['deposits'] + $partnerSavings['deposits'];

        return [
            'user' => [
                'total_deposits' => $userSavings['deposits'],
                'total_withdrawals' => $userSavings['withdrawals'],
            ],
            'partner' => [
                'total_deposits' => $partnerSavings['deposits'],
                'total_withdrawals' => $partnerSavings['withdrawals'],
            ],
            'combined' => [
                'total_deposits' => $combinedDeposits,
                'user_contribution' => $this->calculateContribution($userSavings['deposits'], $partnerSavings['deposits']),
                'partner_contribution' => $this->calculateContribution($partnerSavings['deposits'], $userSavings['deposits']),
            ],
            'leader' => $userSavings['deposits'] >= $partnerSavings['deposits'] ? 'user' : 'partner',
        ];
    }

    /**
     * Get savings list comparison
     */
    protected function getSavingsListComparison(User $user, User $partner)
    {
        // Get all savings (including shared) for categories
        $allSavings = Saving::with('category')->get();

        // Get all unique categories
        $allCategories = $allSavings->pluck('category')
            ->filter()
            ->unique('id')
            ->sortBy('name');

        $comparison = [];

        foreach ($allCategories as $category) {
            // Get saving IDs in this category
            $savingIdsInCategory = $allSavings->where('category_id', $category->id)->pluck('id');

            // Get deposit amounts for each user in this category
            $userDeposits = SavingTransaction::whereIn('saving_id', $savingIdsInCategory)
                ->where('actor_user_id', $user->id)
                ->where('type', 'deposit')
                ->sum('amount');

            $partnerDeposits = SavingTransaction::whereIn('saving_id', $savingIdsInCategory)
                ->where('actor_user_id', $partner->id)
                ->where('type', 'deposit')
                ->sum('amount');

            $comparison[] = [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'color' => $category->color,
                ],
                'user' => [
                    'amount' => $userDeposits,
                    'count' => 0, // Not applicable for deposits
                ],
                'partner' => [
                    'amount' => $partnerDeposits,
                    'count' => 0, // Not applicable for deposits
                ],
                'leader' => $userDeposits >= $partnerDeposits ? 'user' : 'partner',
            ];
        }

        // Add savings without category
        $savingIdsNoCategory = $allSavings->whereNull('category_id')->pluck('id');

        if ($savingIdsNoCategory->count() > 0) {
            $userNoCategoryDeposits = SavingTransaction::whereIn('saving_id', $savingIdsNoCategory)
                ->where('actor_user_id', $user->id)
                ->where('type', 'deposit')
                ->sum('amount');

            $partnerNoCategoryDeposits = SavingTransaction::whereIn('saving_id', $savingIdsNoCategory)
                ->where('actor_user_id', $partner->id)
                ->where('type', 'deposit')
                ->sum('amount');

            $comparison[] = [
                'category' => [
                    'id' => null,
                    'name' => 'Uncategorized',
                    'icon' => '📁',
                    'color' => '#6c757d',
                ],
                'user' => [
                    'amount' => $userNoCategoryDeposits,
                    'count' => 0,
                ],
                'partner' => [
                    'amount' => $partnerNoCategoryDeposits,
                    'count' => 0,
                ],
                'leader' => $userNoCategoryDeposits >= $partnerNoCategoryDeposits ? 'user' : 'partner',
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

        // Get saving IDs for both users (including shared savings)
        $userSavingIds = Saving::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->orWhereNull('user_id');
        })->pluck('id');

        $partnerSavingIds = Saving::where(function ($query) use ($partner) {
            $query->where('user_id', $partner->id)->orWhereNull('user_id');
        })->pluck('id');

        $contributions = [];

        foreach ($months as $month) {
            $userDeposits = SavingTransaction::whereIn('saving_id', $userSavingIds)
                ->where('type', 'deposit')
                ->whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->sum('amount');

            $partnerDeposits = SavingTransaction::whereIn('saving_id', $partnerSavingIds)
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
        // Get personal savings for each user
        $userPersonalSavings = Saving::where('user_id', $user->id)->with('category')->get();
        $partnerPersonalSavings = Saving::where('user_id', $partner->id)->with('category')->get();

        // Get shared savings (user_id is NULL)
        $sharedSavings = Saving::whereNull('user_id')->with('category')->get();

        // Split shared savings equally between both users
        $userSavings = $userPersonalSavings->concat($sharedSavings);
        $partnerSavings = $partnerPersonalSavings->concat($sharedSavings);

        $categories = $userSavings->pluck('category')
            ->merge($partnerSavings->pluck('category'))
            ->filter()
            ->unique('id');

        return $categories->map(function ($category) use ($userSavings, $partnerSavings, $user, $partner) {
            $userInCategory = $userSavings->where('category_id', $category->id);
            $partnerInCategory = $partnerSavings->where('category_id', $category->id);

            // For shared savings, split the amount equally
            $userSharedAmount = $userInCategory->whereNull('user_id')->sum('current_amount') / 2;
            $partnerSharedAmount = $partnerInCategory->whereNull('user_id')->sum('current_amount') / 2;

            $userAmount = $userInCategory->where('user_id', $user->id)->sum('current_amount') + $userSharedAmount;
            $partnerAmount = $partnerInCategory->where('user_id', $partner->id)->sum('current_amount') + $partnerSharedAmount;

            // Calculate totals for percentage
            $userTotal = $userSavings->sum('current_amount') / 2; // Split shared
            $partnerTotal = $partnerSavings->sum('current_amount') / 2; // Split shared

            return [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'color' => $category->color,
                ],
                'user' => [
                    'amount' => $userAmount,
                    'percentage' => $this->calculatePercentage($userAmount, $userTotal),
                ],
                'partner' => [
                    'amount' => $partnerAmount,
                    'percentage' => $this->calculatePercentage($partnerAmount, $partnerTotal),
                ],
            ];
        })->values();
    }

    /**
     * Get goals progress comparison
     */
    protected function getGoalsProgressComparison(User $user, User $partner)
    {
        // Get personal goals for each user (user_id matches)
        $userPersonalGoals = Saving::where('user_id', $user->id)
            ->where('is_shared', false)
            ->whereNotNull('target_amount')
            ->whereNull('completed_at')
            ->with('category')
            ->get();

        $partnerPersonalGoals = Saving::where('user_id', $partner->id)
            ->where('is_shared', false)
            ->whereNotNull('target_amount')
            ->whereNull('completed_at')
            ->with('category')
            ->get();

        // Get shared goals (user_id is NULL) - these appear for both users
        $sharedGoals = Saving::whereNull('user_id')
            ->where('is_shared', false)
            ->whereNotNull('target_amount')
            ->whereNull('completed_at')
            ->with('category')
            ->get();

        // Merge personal goals with shared goals for each user
        $userGoals = $userPersonalGoals->concat($sharedGoals);
        $partnerGoals = $partnerPersonalGoals->concat($sharedGoals);

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
        // Get saving IDs including shared savings
        $userSavingIds = Saving::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->orWhereNull('user_id');
        })->pluck('id');

        $partnerSavingIds = Saving::where(function ($query) use ($partner) {
            $query->where('user_id', $partner->id)->orWhereNull('user_id');
        })->pluck('id');

        $userTransactionCount = SavingTransaction::whereIn('saving_id', $userSavingIds)->count();
        $partnerTransactionCount = SavingTransaction::whereIn('saving_id', $partnerSavingIds)->count();

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
        // Get personal savings and shared savings
        $savings = Saving::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->orWhereNull('user_id');
        })->get();
        $savingIds = $savings->pluck('id');
        $transactions = SavingTransaction::whereIn('saving_id', $savingIds)->get();

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
        // Get savings owned by this user (user_id matches) OR shared savings (user_id is NULL)
        $savings = Saving::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereNull('user_id');
        })->get();

        // Get transactions performed by this user (based on actor_user_id)
        $userDeposits = SavingTransaction::where('actor_user_id', $user->id)
            ->where('type', 'deposit')
            ->sum('amount');

        $userWithdrawals = SavingTransaction::where('actor_user_id', $user->id)
            ->where('type', 'withdrawal')
            ->sum('amount');

        // For shared savings (user_id is NULL), split them equally for comparison
        $sharedSavings = $savings->whereNull('user_id');
        $personalSavings = $savings->where('user_id', $user->id);

        $totalSavings = $personalSavings->sum('current_amount') + ($sharedSavings->sum('current_amount') / 2);
        $totalTarget = $personalSavings->where('is_shared', false)->sum('target_amount') + ($sharedSavings->where('is_shared', false)->sum('target_amount') / 2);

        $personalCount = $personalSavings->where('is_shared', false)->count();
        $sharedCount = $sharedSavings->where('is_shared', false)->count();
        $totalGoalsCount = $personalCount + $sharedCount;

        $completedCount = $personalSavings->whereNotNull('completed_at')->count() + $sharedSavings->whereNotNull('completed_at')->count();

        return [
            'total' => $totalSavings,
            'target' => $totalTarget,
            'completed' => $completedCount,
            'active' => $totalGoalsCount - $completedCount,
            'completion_rate' => $totalGoalsCount > 0
                ? round(($completedCount / $totalGoalsCount) * 100, 1)
                : 0,
            'deposits' => $userDeposits,
            'withdrawals' => $userWithdrawals,
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
        // Get saving IDs including shared savings
        $savingIds = Saving::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->orWhereNull('user_id');
        })->pluck('id');
        return SavingTransaction::whereIn('saving_id', $savingIds)->avg('amount') ?? 0;
    }
}
