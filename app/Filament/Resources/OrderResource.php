<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('delivery_address')
                    ->limit(32)
                    ->tooltip(fn (Order $record): string => $record->delivery_address),
                Tables\Columns\TextColumn::make('items.product_name')
                    ->label('Items')
                    ->listWithLineBreaks(),
                Tables\Columns\TextColumn::make('payment_reference')
                    ->label('GCash reference')
                    ->placeholder('Not submitted')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('gcash_receipt')
                    ->label('Receipt')
                    ->disk('public')
                    ->square()
                    ->height(48),
                Tables\Columns\TextColumn::make('total')
                    ->money('PHP'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid', 'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending verification',
                        'paid' => 'Paid',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View order')
                    ->infolist([
                        InfolistSection::make('Order details')
                            ->schema([
                                TextEntry::make('order_number'),
                                TextEntry::make('payment_status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'paid', 'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'warning',
                                    }),
                                TextEntry::make('delivery_address')
                                    ->columnSpanFull(),
                                TextEntry::make('items.product_name')
                                    ->label('Items')
                                    ->listWithLineBreaks()
                                    ->columnSpanFull(),
                                TextEntry::make('payment_reference')
                                    ->label('GCash reference')
                                    ->placeholder('Not submitted'),
                                TextEntry::make('total')
                                    ->money('PHP'),
                                ImageEntry::make('gcash_receipt')
                                    ->label('GCash receipt')
                                    ->disk('public')
                                    ->height(320)
                                    ->columnSpanFull()
                                    ->visible(fn (Order $record): bool => filled($record->gcash_receipt)),
                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('markPaid')
                        ->label('Mark as paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Order $record): bool => $record->payment_status === 'pending'
                            && (filled($record->payment_reference) || filled($record->gcash_receipt)))
                        ->action(function (Order $record): void {
                            $result = DB::transaction(function () use ($record): string {
                                $order = Order::query()
                                    ->with('items')
                                    ->whereKey($record->getKey())
                                    ->lockForUpdate()
                                    ->firstOrFail();

                                if ($order->payment_status !== 'pending'
                                    || (blank($order->payment_reference) && blank($order->gcash_receipt))) {
                                    return 'invalid';
                                }

                                if (! $order->stock_deducted) {
                                    $products = Product::query()
                                        ->whereIn('id', $order->items->pluck('product_id')->filter())
                                        ->lockForUpdate()
                                        ->get()
                                        ->keyBy('id');

                                    foreach ($order->items as $item) {
                                        $product = $products->get($item->product_id);

                                        if (! $product || ! $product->is_available || $product->stock_kg < $item->quantity_kg) {
                                            return 'insufficient_stock';
                                        }
                                    }

                                    foreach ($order->items as $item) {
                                        $products->get($item->product_id)->decrement('stock_kg', $item->quantity_kg);
                                    }

                                    $order->stock_deducted = true;
                                }

                                $order->payment_status = 'paid';
                                $order->save();

                                return 'paid';
                            });

                            if ($result === 'paid') {
                                Notification::make()->title('Order marked as paid')->success()->send();
                            } elseif ($result === 'insufficient_stock') {
                                Notification::make()
                                    ->title('Unable to mark paid: one or more products no longer have enough stock.')
                                    ->danger()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('This order is no longer pending or has no payment proof.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Tables\Actions\Action::make('markCompleted')
                        ->label('Mark as completed')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Order $record): bool => $record->payment_status === 'paid')
                        ->action(function (Order $record): void {
                            $completed = DB::transaction(function () use ($record): bool {
                                $order = Order::query()
                                    ->whereKey($record->getKey())
                                    ->lockForUpdate()
                                    ->firstOrFail();

                                if ($order->payment_status !== 'paid') {
                                    return false;
                                }

                                $order->payment_status = 'completed';
                                $order->save();

                                return true;
                            });

                            if ($completed) {
                                Notification::make()->title('Order marked as completed')->success()->send();
                            } else {
                                Notification::make()->title('This order is no longer paid')->danger()->send();
                            }
                        }),
                    Tables\Actions\Action::make('cancel')
                        ->label('Cancel order')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Order $record): bool => $record->payment_status === 'pending')
                        ->action(function (Order $record): void {
                            $cancelled = DB::transaction(function () use ($record): bool {
                                $order = Order::query()
                                    ->with('items')
                                    ->whereKey($record->getKey())
                                    ->lockForUpdate()
                                    ->firstOrFail();

                                if ($order->payment_status !== 'pending') {
                                    return false;
                                }

                                if ($order->stock_deducted) {
                                    foreach ($order->items as $item) {
                                        if ($item->product_id !== null) {
                                            Product::query()
                                                ->whereKey($item->product_id)
                                                ->increment('stock_kg', $item->quantity_kg);
                                        }
                                    }

                                    $order->stock_deducted = false;
                                }

                                $order->payment_status = 'cancelled';
                                $order->save();

                                return true;
                            });

                            if ($cancelled) {
                                Notification::make()->title('Order cancelled and stock released')->success()->send();
                            } else {
                                Notification::make()->title('This order is no longer pending')->danger()->send();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}
