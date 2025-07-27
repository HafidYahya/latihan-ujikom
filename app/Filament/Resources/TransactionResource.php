<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Hidden;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Transaksi';

    // Hanya tampilkan transaksi milik customer
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();
        if ($user && $user->role === 'Customer') {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    // Tampilkan menu hanya untuk role tertentu
    public static function canAccess(): bool
    {
        return in_array(Auth::user()->role, ['Sales', 'Customer', 'Petugas']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    // Hanya role tertentu bisa create
    public static function canCreate(): bool
    {
        return in_array(Auth::user()->role, ['Customer', 'Sales']);
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();

        $fields = [
            Hidden::make('user_id')
                ->default($user->id)
                ->dehydrated(),

            Forms\Components\Select::make('item_id')
                ->label('Item')
                ->relationship('item', 'nama')
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $item = Item::find($state);
                    if ($item) {
                        $set('price', $item->harga_jual);
                        $set('amount', $item->harga_jual);
                    } else {
                        $set('price', 0);
                        $set('amount', 0);
                    }
                }),

            Forms\Components\TextInput::make('qty')
                ->label('Jumlah')
                ->numeric()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    $set('amount', $state * $get('price'));
                }),

            // Tetap disimpan walau disabled
            Forms\Components\TextInput::make('price')
                ->label('Harga Satuan')
                ->numeric()
                ->required()
                ->disabled()
                ->dehydrated(),

            Forms\Components\TextInput::make('amount')
                ->label('Total Harga')
                ->numeric()
                ->required()
                ->disabled()
                ->dehydrated(),
        ];

        // Jika Sales, pilih status
        if ($user->role === 'Sales') {
            $fields[] = Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                ])
                ->required();
        } else {
            // Jika Customer, default status = pending
            $fields[] = Hidden::make('status')
                ->default('pending')
                ->dehydrated();
        }

        return $form->schema($fields);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.nama')->label('Item'),
                Tables\Columns\TextColumn::make('qty')->label('Jumlah'),
                Tables\Columns\TextColumn::make('price')->label('Harga')->money('IDR'),
                Tables\Columns\TextColumn::make('amount')->label('Total')->money('IDR'),
                Tables\Columns\TextColumn::make('status')->badge()->label('Status'),
                Tables\Columns\TextColumn::make('user.name')->label('Customer'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => in_array(Auth::user()->role, ['Sales', 'Customer'])),
                    Tables\Actions\DeleteAction::make()
                    ->visible(fn () => in_array(Auth::user()->role, ['Sales', 'Customer'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->role === 'Sales'),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
