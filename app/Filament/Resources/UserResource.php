<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Manajemen Pengguna';

    protected static ?string $Label = 'Pengguna';

    protected static ?string $navigationIcon = 'heroicon-o-users';


    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'operator']);
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }
    
    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        
        // Admin bisa edit semua user
        if ($user->role === 'admin') {
            return true;
        }
        
        // Operator hanya bisa edit profil mereka sendiri
        if ($user->role === 'operator') {
            return $user->id === $record->id;
        }
        
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        // Hanya admin yang bisa delete user
        return Auth::user()->role === 'admin';
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->label('Username')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->disabled(fn($record) => $record && $record instanceof User && $record->role === 'admin'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->default(fn(?User $record) => $record?->plain_password ?? '')
                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->disabled(fn($record) => $record && $record instanceof User && $record->role === 'admin')
                    ->afterStateUpdated(function ($state, Set $set, ?User $record) {
                        if ($record && filled($state)) {
                            $record->update(['plain_password' => $state]);
                        }
                    })
                    ->live()
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, ?User $record) {
                        if ($record && $record->plain_password) {
                            $component->state($record->plain_password);
                        }
                    }),
                Forms\Components\Select::make('role')
                    ->options(function (Get $get, ?User $record) {
                        // Pada create: hanya izinkan operator. Pada edit admin: tampilkan Admin tapi dikunci lewat disabled.
                        return [
                            'operator' => 'Operator',
                            ...($record && $record->role === 'admin' ? ['admin' => 'Admin'] : []),
                        ];
                    })
                    ->default('operator')
                    ->live()
                    ->required()
                    ->disabled(fn($record) => 
                        ($record && $record instanceof User && $record->role === 'admin') ||
                        (Auth::user()->role === 'operator')
                    ),
                Forms\Components\Select::make('service_id')
                    ->label('Layanan')
                    ->options(\App\Models\Service::where('is_active', true)->pluck('name', 'id'))
                    ->visible(fn(Get $get) => $get('role') === 'operator')
                    ->required(fn(Get $get) => $get('role') === 'operator')
                    ->disabled(fn() => Auth::user()->role === 'operator'),
                Forms\Components\Select::make('counter_id')
                    ->label('Loket')
                    ->options(\App\Models\Counter::withoutGlobalScopes()->orderBy('name')->pluck('name', 'id'))
                    ->visible(fn(Get $get) => $get('role') === 'operator')
                    ->required(fn(Get $get) => $get('role') === 'operator')
                    ->disabled(fn() => Auth::user()->role === 'operator')
                    ->searchable()
                    ->preload()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pengguna'),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Peran'),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->formatStateUsing(fn (?string $state, User $record): string => $record->role === 'admin' ? 'Semua' : ($state ?? '-')),
                Tables\Columns\TextColumn::make('counter.name')
                    ->label('Loket')
                    ->formatStateUsing(fn (?string $state, User $record): string => $record->role === 'admin' ? 'Semua' : ($state ?? '-'))
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(User $record) => $record->role !== 'admin'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
