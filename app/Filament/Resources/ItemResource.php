<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Barang';

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->role === 'Sales';
    }

    public static function canCreate(): bool
    {
        return Auth::user()->role === 'Sales';
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->role === 'Sales';
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->role === 'Sales';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')
                ->label('Nama Barang')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('uom')
                ->label('Satuan')
                ->required()
                ->maxLength(50),

            Forms\Components\TextInput::make('harga_beli')
                ->label('Harga Beli')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('harga_jual')
                ->label('Harga Jual')
                ->numeric()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('uom')
                    ->label('Satuan'),

                Tables\Columns\TextColumn::make('harga_beli')
                    ->label('Harga Beli')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->money('IDR'),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => in_array(Auth::user()->role, ['Sales'])),
                    Tables\Actions\DeleteAction::make()
                    ->visible(fn () => in_array(Auth::user()->role, ['Sales'])),
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
