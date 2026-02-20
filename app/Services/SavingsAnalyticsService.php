<?php

namespace App\Services;

use App\Models\Saving;
use App\Models\SavingTransaction;
use App\Models\SavingCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SavingsAnalyticsService
{
    /**
     * Get comprehensive analytics for a user's savings
     */
    public function getUserAnalytics(User $user, $period = 'all')
    {
        $savings = Saving::where('user_id', $user->id)
            ->with('category')
            ->get();

        $transactions = SavingTransaction::whereIn('saving_id', $savings->pluck('id'))
            ->when($period !== 'all', function ($q) use ($period) {
                return $this->applyPeriodFilter($q, $period);
            })
            ->get();

        return [
            'overview' => $this->getOverview($savings, $transactions),
            'by_category' => $this->getByCategory($savings),
            'trends' => $this->getTrends($transactions),
            'goals_progress' => $this->getGoalsProgress($savings),
            'recent_activity' => $this->getRecentActivity($transactions),
            'monthly_summary' => $this->getMonthlySummary($transactions),
        ];
    }

    /**
     * Get overview statistics
     */
    protected function getOverview($savings, $transactions)
    {
        $totalSavings = $savings->sum('current_amount');
        $totalTarget = $savings->where('is_shared', false)->sum('target_amount');
        $completedCount = $savings->where('is_completed', true)->count();
        $totalCount = $savings->count();

        $totalDeposits = $transactions->where('type', 'deposit')->sum('amount');
        $totalWithdrawals = $transactions->where('type', 'withdrawal')->sum('amount');

        $averageProgress = $totalCount > 0
            ? $savings->where('is_shared', false)->avg('progress')
            : 0;

        return [
            'total_savings' => $totalSavings,
            'total_target' => $totalTarget,
            'overall_progress' => $totalTarget > 0 ? round(($totalSavings / $totalTarget) * 100, 1) : 0,
            'average_progress' => round($averageProgress, 1),
            'completed_savings' => $completedCount,
            'total_savings_count' => $totalCount,
            'completion_rate' => $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 1) : 0,
            'total_deposits' => $totalDeposits,
            'total_withdrawals' => $totalWithdrawals,
            'net_savings' => $totalDeposits - $totalWithdrawals,
        ];
    }

    /**
     * Get breakdown by category
     */
    protected function getByCategory($savings)
    {
        return $savings->filter(fn($s) => $s->category_id)
            ->groupBy('category_id')
            ->map(function ($group) {
                $category = $group->first()->category;
                return [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'icon' => $category->icon,
                        'color' => $category->color,
                    ],
                    'total_amount' => $group->sum('current_amount'),
                    'savings_count' => $group->count(),
                    'completed_count' => $group->where('is_completed', true)->count(),
                    'total_target' => $group->where('is_shared', false)->sum('target_amount'),
                ];
            })
            ->values()
            ->sortByDesc('total_amount')
            ->values();
    }

    /**
     * Get savings trends over time
     */
    protected function getTrends($transactions)
    {
        $groupedByMonth = $transactions->groupBy(function ($t) {
            return $t->created_at->format('Y-m');
        })->map(function ($group) {
            return [
                'month' => $group->first()->created_at->format('M Y'),
                'deposits' => $group->where('type', 'deposit')->sum('amount'),
                'withdrawals' => $group->where('type', 'withdrawal')->sum('amount'),
                'transactions' => $group->count(),
            ];
        })->sortBy('month')->slice(-12)->values();

        return [
            'monthly_trend' => $groupedByMonth,
            'total_months' => $groupedByMonth->count(),
            'average_monthly_savings' => $groupedByMonth->avg('deposits'),
        ];
    }

    /**
     * Get goals progress
     */
    protected function getGoalsProgress($savings)
    {
        return $savings->where('is_shared', false)
            ->map(function ($saving) {
                return [
                    'id' => $saving->id,
                    'name' => $saving->name,
                    'current_amount' => $saving->current_amount,
                    'target_amount' => $saving->target_amount,
                    'progress' => round($saving->progress, 1),
                    'status' => $saving->status,
                    'is_completed' => $saving->is_completed,
                    'target_date' => $saving->target_date?->format('Y-m-d'),
                    'days_remaining' => $saving->days_remaining,
                    'category' => $saving->category ? [
                        'name' => $saving->category->name,
                        'icon' => $saving->category->icon,
                        'color' => $saving->category->color,
                    ] : null,
                ];
            })
            ->sortByDesc('progress')
            ->values();
    }

    /**
     * Get recent activity
     */
    protected function getRecentActivity($transactions)
    {
        return $transactions->sortByDesc('created_at')
            ->take(20)
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'type' => $t->type,
                    'amount' => $t->amount,
                    'note' => $t->note,
                    'date' => $t->created_at->format('d M Y H:i'),
                    'saving_name' => $t->saving->name,
                ];
            })
            ->values();
    }

    /**
     * Get monthly summary
     */
    protected function getMonthlySummary($transactions)
    {
        return $transactions->groupBy(function ($t) {
            return $t->created_at->format('Y-m');
        })->map(function ($group, $month) {
            $deposits = $group->where('type', 'deposit');
            $withdrawals = $group->where('type', 'withdrawal');

            return [
                'month' => $month,
                'month_formatted' => Carbon::parse($month . '-01')->format('M Y'),
                'deposit_count' => $deposits->count(),
                'deposit_total' => $deposits->sum('amount'),
                'withdrawal_count' => $withdrawals->count(),
                'withdrawal_total' => $withdrawals->sum('amount'),
                'net_amount' => $deposits->sum('amount') - $withdrawals->sum('amount'),
                'transaction_count' => $group->count(),
            ];
        })
        ->sortByDesc('month')
        ->slice(-12)
        ->sortBy('month')
        ->values();
    }

    /**
     * Get savings growth data for chart
     */
    public function getSavingsGrowth(User $user, $period = '6months')
    {
        $savings = Saving::where('user_id', $user->id)->get();
        $savingIds = $savings->pluck('id');

        $startDate = match($period) {
            '1month' => Carbon::now()->subMonth(),
            '3months' => Carbon::now()->subMonths(3),
            '6months' => Carbon::now()->subMonths(6),
            '1year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonths(6),
        };

        $transactions = SavingTransaction::whereIn('saving_id', $savingIds)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get();

        $runningTotal = 0;
        $growthData = $transactions->groupBy(function ($t) {
            return $t->created_at->format('Y-m-d');
        })->map(function ($group) use (&$runningTotal) {
            $dailyNet = $group->where('type', 'deposit')->sum('amount')
                      - $group->where('type', 'withdrawal')->sum('amount');
            $runningTotal += $dailyNet;

            return [
                'date' => $group->first()->created_at->format('Y-m-d'),
                'amount' => $runningTotal,
            ];
        })->values();

        return [
            'period' => $period,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'data' => $growthData,
        ];
    }

    /**
     * Get category distribution
     */
    public function getCategoryDistribution(User $user)
    {
        $savings = Saving::where('user_id', $user->id)
            ->with('category')
            ->get();

        $totalAmount = $savings->sum('current_amount');

        return $savings->filter(fn($s) => $s->category_id)
            ->groupBy('category_id')
            ->map(function ($group) use ($totalAmount) {
                $category = $group->first()->category;
                $amount = $group->sum('current_amount');

                return [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'icon' => $category->icon,
                        'color' => $category->color,
                    ],
                    'amount' => $amount,
                    'percentage' => $totalAmount > 0 ? round(($amount / $totalAmount) * 100, 1) : 0,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('amount')
            ->values();
    }

    /**
     * Get upcoming targets
     */
    public function getUpcomingTargets(User $user, $limit = 5)
    {
        return Saving::where('user_id', $user->id)
            ->where('is_shared', false)
            ->whereNull('completed_at')
            ->whereNotNull('target_date')
            ->with('category')
            ->orderBy('target_date')
            ->take($limit)
            ->get()
            ->map(function ($saving) {
                return [
                    'id' => $saving->id,
                    'name' => $saving->name,
                    'target_amount' => $saving->target_amount,
                    'current_amount' => $saving->current_amount,
                    'remaining' => max(0, $saving->target_amount - $saving->current_amount),
                    'progress' => round($saving->progress, 1),
                    'target_date' => $saving->target_date->format('d M Y'),
                    'days_remaining' => $saving->days_remaining,
                    'status' => $saving->status,
                    'is_overdue' => $saving->is_overdue,
                    'category' => $saving->category ? [
                        'name' => $saving->category->name,
                        'icon' => $saving->category->icon,
                        'color' => $saving->category->color,
                    ] : null,
                ];
            });
    }

    /**
     * Apply period filter to query
     */
    protected function applyPeriodFilter($query, $period)
    {
        return match($period) {
            'today' => $query->whereDate('created_at', '>=', Carbon::today()),
            'week' => $query->where('created_at', '>=', Carbon::now()->startOfWeek()),
            'month' => $query->where('created_at', '>=', Carbon::now()->startOfMonth()),
            'quarter' => $query->where('created_at', '>=', Carbon::now()->startOfQuarter()),
            'year' => $query->where('created_at', '>=', Carbon::now()->startOfYear()),
            default => $query,
        };
    }

    /**
     * Compare periods (current vs previous)
     */
    public function comparePeriods(User $user, $currentPeriod = 'month')
    {
        $savings = Saving::where('user_id', $user->id)->pluck('id');

        $currentTransactions = SavingTransaction::whereIn('saving_id', $savings)
            ->where('created_at', '>=', $this->getPeriodStart($currentPeriod))
            ->get();

        $previousTransactions = SavingTransaction::whereIn('saving_id', $savings)
            ->whereBetween('created_at', [
                $this->getPeriodStart($currentPeriod, true),
                $this->getPeriodStart($currentPeriod),
            ])
            ->get();

        $currentDeposits = $currentTransactions->where('type', 'deposit')->sum('amount');
        $previousDeposits = $previousTransactions->where('type', 'deposit')->sum('amount');

        return [
            'current' => [
                'deposits' => $currentDeposits,
                'withdrawals' => $currentTransactions->where('type', 'withdrawal')->sum('amount'),
                'transactions' => $currentTransactions->count(),
            ],
            'previous' => [
                'deposits' => $previousDeposits,
                'withdrawals' => $previousTransactions->where('type', 'withdrawal')->sum('amount'),
                'transactions' => $previousTransactions->count(),
            ],
            'change' => [
                'deposits_percent' => $previousDeposits > 0
                    ? round((($currentDeposits - $previousDeposits) / $previousDeposits) * 100, 1)
                    : 0,
            ],
        ];
    }

    /**
     * Get period start date
     */
    protected function getPeriodStart($period, $previous = false)
    {
        $date = match($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        if ($previous) {
            $date = match($period) {
                'today' => $date->subDay(),
                'week' => $date->subWeek(),
                'month' => $date->subMonth(),
                'quarter' => $date->subQuarter(),
                'year' => $date->subYear(),
                default => $date->subMonth(),
            };
        }

        return $date;
    }
}
