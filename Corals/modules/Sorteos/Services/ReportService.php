<?php

namespace Corals\Modules\Sorteos\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Sales statistics grouped by period (day|week|month).
     */
    public function salesByPeriod(string $period, Carbon $from, Carbon $to, ?int $sorteoId = null): array
    {
        $format = match ($period) {
            'week'  => '%Y-W%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $query = DB::table('sorteos_orders as o')
            ->selectRaw("DATE_FORMAT(o.created_at, ?) as period_label", [$format])
            ->selectRaw('COUNT(o.id) as orders_count')
            ->selectRaw('SUM(o.total_amount) as revenue')
            ->selectRaw('(SELECT COUNT(*) FROM sorteos_order_items oi WHERE oi.order_id = o.id) as tickets_sold')
            ->where('o.status', 'confirmed')
            ->whereNull('o.deleted_at')
            ->whereBetween('o.created_at', [$from->startOfDay(), $to->endOfDay()]);

        if ($sorteoId) {
            $query->where('o.sorteo_id', $sorteoId);
        }

        $rows = $query->groupBy('period_label')->orderBy('period_label')->get();

        return [
            'labels'  => $rows->pluck('period_label')->toArray(),
            'orders'  => $rows->pluck('orders_count')->map(fn($v) => (int) $v)->toArray(),
            'revenue' => $rows->pluck('revenue')->map(fn($v) => (float) $v)->toArray(),
            'tickets' => $rows->pluck('tickets_sold')->map(fn($v) => (int) $v)->toArray(),
            'totals'  => [
                'orders'  => $rows->sum('orders_count'),
                'revenue' => $rows->sum('revenue'),
                'tickets' => $rows->sum('tickets_sold'),
                'avg_order' => $rows->count() ? $rows->sum('revenue') / max($rows->sum('orders_count'), 1) : 0,
            ],
        ];
    }

    /**
     * Buyer purchase history, optionally filtered by email or sorteo.
     */
    public function buyerHistory(?string $search = null, ?int $sorteoId = null, int $perPage = 25): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = DB::table('sorteos_orders as o')
            ->select([
                'o.buyer_email',
                'o.buyer_name',
                'o.buyer_phone',
                DB::raw('COUNT(o.id) as order_count'),
                DB::raw('SUM(o.total_amount) as total_spent'),
                DB::raw('MAX(o.created_at) as last_purchase'),
                DB::raw('COUNT(DISTINCT o.sorteo_id) as sorteos_count'),
            ])
            ->where('o.status', 'confirmed')
            ->whereNull('o.deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('o.buyer_email', 'like', "%{$search}%")
                  ->orWhere('o.buyer_name', 'like', "%{$search}%");
            });
        }

        if ($sorteoId) {
            $query->where('o.sorteo_id', $sorteoId);
        }

        return $query->groupBy('o.buyer_email', 'o.buyer_name', 'o.buyer_phone')
            ->orderByDesc('total_spent')
            ->paginate($perPage);
    }

    /**
     * Orders for a specific buyer email.
     */
    public function buyerOrders(string $email): \Illuminate\Support\Collection
    {
        return DB::table('sorteos_orders as o')
            ->join('sorteos_sorteos as s', 's.id', '=', 'o.sorteo_id')
            ->select([
                'o.id', 'o.buyer_name', 'o.buyer_email', 'o.payment_method',
                'o.status', 'o.total_amount', 'o.created_at',
                's.name as sorteo_name',
                DB::raw('(SELECT COUNT(*) FROM sorteos_order_items oi WHERE oi.order_id = o.id) as items_count'),
            ])
            ->where('o.buyer_email', $email)
            ->whereNull('o.deleted_at')
            ->orderByDesc('o.created_at')
            ->get();
    }

    /**
     * Breakdown of confirmed orders by payment method.
     */
    public function paymentMethodBreakdown(?int $sorteoId = null): \Illuminate\Support\Collection
    {
        $query = DB::table('sorteos_orders')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
            ->where('status', 'confirmed')
            ->whereNull('deleted_at');

        if ($sorteoId) {
            $query->where('sorteo_id', $sorteoId);
        }

        return $query->groupBy('payment_method')->orderByDesc('revenue')->get();
    }

    /**
     * Orders grouped by state then city.
     */
    public function geographicBreakdown(?int $sorteoId = null): \Illuminate\Support\Collection
    {
        $query = DB::table('sorteos_orders')
            ->selectRaw('COALESCE(NULLIF(buyer_state,""), "Sin especificar") as state')
            ->selectRaw('COALESCE(NULLIF(buyer_city,""), "Sin especificar") as city')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total_amount) as revenue')
            ->where('status', 'confirmed')
            ->whereNull('deleted_at');

        if ($sorteoId) {
            $query->where('sorteo_id', $sorteoId);
        }

        return $query->groupBy('state', 'city')
            ->orderBy('state')
            ->orderByDesc('orders_count')
            ->get();
    }

    /**
     * Overall KPI summary for a given sorteo or all sorteos.
     */
    public function kpiSummary(?int $sorteoId = null): array
    {
        $query = DB::table('sorteos_orders as o')->whereNull('o.deleted_at');

        if ($sorteoId) {
            $query->where('o.sorteo_id', $sorteoId);
        }

        $confirmed = (clone $query)->where('o.status', 'confirmed');
        $pending   = (clone $query)->where('o.status', 'pending');
        $cancelled = (clone $query)->where('o.status', 'cancelled');

        $ticketsSold = DB::table('sorteos_order_items as oi')
            ->join('sorteos_orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.status', 'confirmed')
            ->when($sorteoId, fn($q) => $q->where('o.sorteo_id', $sorteoId))
            ->whereNull('o.deleted_at')
            ->count();

        $confirmedCount   = (clone $confirmed)->count();
        $confirmedRevenue = (float) (clone $confirmed)->sum('o.total_amount');
        $uniqueBuyers     = (clone $confirmed)->distinct()->count('o.buyer_email');

        return [
            'confirmed_orders' => $confirmedCount,
            'pending_orders'   => $pending->count(),
            'cancelled_orders' => $cancelled->count(),
            'total_revenue'    => $confirmedRevenue,
            'tickets_sold'     => $ticketsSold,
            'unique_buyers'    => $uniqueBuyers,
            'avg_order_value'  => $confirmedCount > 0
                ? $confirmedRevenue / $confirmedCount
                : 0.0,
        ];
    }
}
