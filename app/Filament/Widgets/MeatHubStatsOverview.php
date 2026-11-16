<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MeatHubStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $revenue = Order::query()
            ->whereIn('payment_status', ['paid', 'completed'])
            ->sum('total');

        $totalOrders = Order::query()->count();
        $pendingOrders = Order::query()->where('payment_status', 'pending')->count();
        $completedOrders = Order::query()->whereIn('payment_status', ['paid', 'completed'])->count();
        $lowStockProducts = Product::query()->where('stock_kg', '<=', 5)->count();

        return [
            Stat::make('Total Revenue', '₱'.number_format((float) $revenue, 2))
                ->description('Paid and completed orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Total Orders', number_format($totalOrders))
                ->description("{$pendingOrders} pending · {$completedOrders} paid/completed")
                ->descriptionIcon('heroicon-m-shopping-bag'),
            Stat::make('Low Stock Products', number_format($lowStockProducts))
                ->description('Products with 5 kg or less in stock')
                ->descriptionIcon($lowStockProducts > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
        ];
    }
}
