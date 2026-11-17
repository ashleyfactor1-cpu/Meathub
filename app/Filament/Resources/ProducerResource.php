<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProducerResource\Pages;
use App\Models\Producer;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProducerResource extends Resource
{
    protected static ?string $model = Producer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Marketplace';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Producer Profile')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255)
                            ->placeholder('City or province'),
                        Forms\Components\TextInput::make('address')
                            ->label('Shop / Pick-up Address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('map_embed_url')
                            ->label('Map embed URL')
                            ->helperText('Paste Google Maps embed link or leave blank to auto-generate from address.')
                            ->maxLength(2048)
                            ->rules([
                                'nullable',
                                'url',
                                'regex:#^https://(?:(?:www\.)?google\.com|maps\.google\.com)/maps(?:/|\?|$)#i',
                            ])
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('contact_number')
                            ->label('Contact number')
                            ->tel()
                            ->maxLength(32),
                        FileUpload::make('avatar')
                            ->image()
                            ->disk('public')
                            ->directory('producers')
                            ->visibility('public')
                            ->maxSize(5120),
                        Forms\Components\Textarea::make('bio')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Show on storefront')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->placeholder('Not provided'),
                Tables\Columns\TextColumn::make('address')
                    ->label('Shop / Pick-up Address')
                    ->searchable()
                    ->placeholder('Not provided'),
                Tables\Columns\TextColumn::make('contact_number')
                    ->label('Contact number')
                    ->placeholder('Not provided'),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Storefront')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducers::route('/'),
            'create' => Pages\CreateProducer::route('/create'),
            'edit' => Pages\EditProducer::route('/{record}/edit'),
        ];
    }
}
