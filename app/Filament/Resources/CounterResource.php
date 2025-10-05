<?php

namespace App\Filament\Resources;

use App\Models\Counter;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\CounterResource\Pages;

class CounterResource extends Resource
{
    protected static ?string $model = Counter::class;

    protected static ?string $navigationLabel = 'Manajemen Loket';

    protected static ?string $Label = 'Loket';
    
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function canAccess(): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Loket')
                    ->required(),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),

                // Hubungkan ke Instansi
                Select::make('instansi_id')
                    ->label('Instansi')
                    ->relationship('instansi', 'nama_instansi') // harus sesuai relasi di model Counter
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Reset service when instansi changes
                        $set('service_id', null);
                    }),

                // Pilih layanan yang akan ditangani oleh counter ini (1:1 relationship)
                Select::make('service_id')
                    ->label('Layanan')
                    ->options(function ($get) {
                        $instansiId = $get('instansi_id');
                        if (!$instansiId) {
                            return [];
                        }
                        
                        return Service::where('instansi_id', $instansiId)
                            ->where(function($query) use ($get) {
                                $query->whereNull('counter_id')
                                      ->orWhere('id', $get('service_id'));
                            })
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->reactive()
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Loket')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Status Aktif'),
                Tables\Columns\TextColumn::make('instansi.nama_instansi')
                    ->label('Instansi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('5s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCounters::route('/'),
        ];
    }
}