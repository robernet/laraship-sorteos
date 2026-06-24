<?php

namespace Corals\Modules\Sorteos\Http\Controllers;

use Carbon\Carbon;
use Corals\Foundation\Http\Controllers\BaseController;
use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Cartera;
use Corals\Modules\Sorteos\Models\Order;
use Corals\Modules\Sorteos\Models\Sorteo;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->resource_url   = 'sorteos/dashboard';
        $this->title          = 'Dashboard';
        $this->title_singular = 'Dashboard del Sorteo';

        parent::__construct();
    }

    public function index(Request $request)
    {
        $sorteoId = $request->integer('sorteo_id') ?: null;
        $sorteos  = Sorteo::orderByDesc('id')->pluck('name', 'id');
        $sorteo   = $sorteoId ? Sorteo::find($sorteoId) : Sorteo::orderByDesc('id')->first();

        $data = $sorteo ? $this->compute($sorteo) : [];

        $this->setViewSharedData(['title_singular' => 'Dashboard del Sorteo']);

        return view('Sorteos::sorteos.dashboard', compact('sorteo', 'sorteos', 'sorteoId', 'data'));
    }

    private function compute(Sorteo $sorteo): array
    {
        $sid = $sorteo->id;

        // Boletos
        $totalBoletos      = Boleto::where('sorteo_id', $sid)->count();
        $boletosVendidos   = Boleto::where('sorteo_id', $sid)->where('status', 'sold')->count();
        $boletosReservados = Boleto::where('sorteo_id', $sid)->where('status', 'reserved')->count();
        $boletosDisponibles = $totalBoletos - $boletosVendidos - $boletosReservados;
        $tiraje            = $sorteo->tiraje ?: $totalBoletos;
        $pctVendido        = $tiraje > 0 ? round($boletosVendidos / $tiraje * 100, 1) : 0;

        // Órdenes / ingresos
        $confirmedOrders = Order::where('sorteo_id', $sid)->where('status', 'confirmed');
        $totalRevenue    = (clone $confirmedOrders)->sum('total_amount');
        $confirmedCount  = (clone $confirmedOrders)->count();
        $pendingCount    = Order::where('sorteo_id', $sid)->where('status', 'pending')->count();
        $uniqueBuyers    = (clone $confirmedOrders)->distinct('buyer_email')->count('buyer_email');
        $avgOrder        = $confirmedCount > 0 ? $totalRevenue / $confirmedCount : 0;

        // Carteras
        $carterasTotal    = Cartera::where('sorteo_id', $sid)->count();
        $carterasByStatus = Cartera::where('sorteo_id', $sid)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Días al sorteo
        $drawDate = $sorteo->draw_date ? Carbon::parse($sorteo->draw_date) : null;
        $daysLeft = $drawDate ? (int) now()->diffInDays($drawDate, false) : null;

        // Ventas diarias (últimos 30 días)
        $from = now()->subDays(29)->startOfDay();
        $dailySales = Order::where('sorteo_id', $sid)
            ->where('status', 'confirmed')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, count(*) as orders, sum(total_amount) as revenue')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $dailyLabels = $dailyOrders = $dailyRevenue = [];
        for ($i = 29; $i >= 0; $i--) {
            $day            = now()->subDays($i)->format('Y-m-d');
            $dailyLabels[]  = now()->subDays($i)->format('d/m');
            $dailyOrders[]  = $dailySales->has($day) ? (int) $dailySales[$day]->orders  : 0;
            $dailyRevenue[] = $dailySales->has($day) ? (float) $dailySales[$day]->revenue : 0;
        }

        // Distribución geográfica
        $ciudades = Order::where('sorteo_id', $sid)
            ->where('status', 'confirmed')
            ->whereNotNull('buyer_city')
            ->where('buyer_city', '!=', '')
            ->selectRaw('buyer_city, count(*) as total')
            ->groupBy('buyer_city')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'buyer_city')
            ->toArray();

        // Métodos de pago
        $paymentMethods = Order::where('sorteo_id', $sid)
            ->where('status', 'confirmed')
            ->selectRaw('payment_method, count(*) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        $paymentLabels = array_map(fn($m) => match ($m) {
            'cash'     => 'Efectivo',
            'transfer' => 'Transferencia',
            'clubpago' => 'ClubPago',
            default    => ucfirst($m),
        }, array_keys($paymentMethods));

        // Top colaboradores
        $topColaboradores = Order::where('sorteos_orders.sorteo_id', $sid)
            ->where('sorteos_orders.status', 'confirmed')
            ->whereNotNull('sorteos_orders.colaborador_id')
            ->join('sorteos_colaboradores', 'sorteos_orders.colaborador_id', '=', 'sorteos_colaboradores.id')
            ->selectRaw('sorteos_colaboradores.name, count(*) as orders, sum(sorteos_orders.total_amount) as revenue')
            ->groupBy('sorteos_colaboradores.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Alertas
        $alertas = $this->buildAlerts($pctVendido, $daysLeft, $pendingCount, $boletosVendidos, $tiraje);

        return compact(
            'totalBoletos', 'boletosVendidos', 'boletosReservados', 'boletosDisponibles',
            'tiraje', 'pctVendido',
            'totalRevenue', 'confirmedCount', 'pendingCount', 'uniqueBuyers', 'avgOrder',
            'carterasTotal', 'carterasByStatus',
            'drawDate', 'daysLeft',
            'dailyLabels', 'dailyOrders', 'dailyRevenue',
            'ciudades', 'paymentMethods', 'paymentLabels',
            'topColaboradores',
            'alertas'
        );
    }

    private function buildAlerts(float $pctVendido, ?int $daysLeft, int $pendingCount, int $vendidos, int $tiraje): array
    {
        $alerts = [];

        if ($daysLeft !== null && $daysLeft <= 7 && $daysLeft >= 0) {
            $alerts[] = ['type' => 'danger', 'icon' => 'fa-exclamation-triangle',
                'msg' => "¡El sorteo es en {$daysLeft} día(s)! Quedan " . ($tiraje - $vendidos) . ' boletos por vender.'];
        } elseif ($daysLeft !== null && $daysLeft <= 30 && $daysLeft >= 0) {
            $alerts[] = ['type' => 'warning', 'icon' => 'fa-clock-o',
                'msg' => "El sorteo es en {$daysLeft} días."];
        } elseif ($daysLeft !== null && $daysLeft < 0) {
            $alerts[] = ['type' => 'info', 'icon' => 'fa-flag-checkered',
                'msg' => 'El sorteo ya fue realizado.'];
        }

        if ($tiraje > 0 && $pctVendido < 50 && $daysLeft !== null && $daysLeft <= 30 && $daysLeft >= 0) {
            $alerts[] = ['type' => 'warning', 'icon' => 'fa-bar-chart',
                'msg' => "Solo el {$pctVendido}% de boletos vendidos con menos de 30 días para el sorteo."];
        }

        if ($pendingCount > 0) {
            $alerts[] = ['type' => 'info', 'icon' => 'fa-hourglass-half',
                'msg' => "{$pendingCount} orden(es) pendiente(s) de confirmación."];
        }

        if ($tiraje > 0 && $pctVendido >= 90) {
            $alerts[] = ['type' => 'success', 'icon' => 'fa-trophy',
                'msg' => "¡Excelente! {$pctVendido}% de boletos vendidos."];
        }

        return $alerts;
    }
}
