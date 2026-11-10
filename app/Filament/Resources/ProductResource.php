<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->description('Enter the details of the meat product.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Pork Liempo, Beef Ribs'),

                        FileUpload::make('image')
                            ->label('Product image')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('producer_name')
                            ->label('Producer')
                            ->maxLength(255)
                            ->placeholder('e.g. San Isidro Family Farm'),

                        Forms\Components\Select::make('category')
                            ->options([
                                'Pork' => 'Pork',
                                'Beef' => 'Beef',
                                'Chicken' => 'Chicken',
                                'Organ Meat' => 'Organ Meat',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('₱')
                            ->required()
                            ->placeholder('0.00'),

                        Forms\Components\TextInput::make('stock_kg')
                            ->numeric()
                            ->label('Stock (KG)')
                            ->required()
                            ->placeholder('0'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_available')
                            ->label('Available for Sale')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pork' => 'warning',
                        'Beef' => 'primary',
                        'Chicken' => 'info',
                        'Organ Meat' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('PHP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_kg')
                    ->label('Stock (KG)')
                    ->formatStateUsing(fn ($state): string => number_format((int) $state).' kg')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        (int) $state <= 0 => 'danger',
                        (int) $state <= 5 => 'warning',
                        default => 'success',
                    })
                    ->description(fn (Product $record): string => match (true) {
                        $record->stock_kg <= 0 => 'Out of stock',
                        $record->stock_kg <= 5 => 'Low stock',
                        default => 'In stock',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_available')
                    ->label('Status')
                    ->formatStateUsing(fn ($state): string => $state ? 'Available' : 'Unavailable')
                    ->badge()
                    ->icon(fn ($state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                    ->color(fn ($state): string => $state ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Pork' => 'Pork',
                        'Beef' => 'Beef',
                        'Chicken' => 'Chicken',
                        'Organ Meat' => 'Organ Meat',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
