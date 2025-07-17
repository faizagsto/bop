<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;



use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-users';
    protected static ?string $navigationGroup = 'Filament Shield';


    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Grid::make(2)->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->required()
                    ->maxLength(15),

                Select::make('role')
                    ->label('Jabatan')
                    ->required()
                    ->live()
                    ->options([
                        'requester' => 'Requester',
                        'bm' => 'Branch Manager',
                        'reviewer' => 'Reviewer',
                        'manager' => 'Manager',
                        'cashier' => 'Cashier',
                    ])
                    ->afterStateHydrated(function ($component, $state, $record) {
                            if ($record && $record->roles->count() > 0) {
                                $component->state($record->roles->first()->name);
                            }
                        }),
                

                Select::make('area')
                    ->label('Area')
                    ->required()
                    ->options([
                        'branch' => 'Branch',
                        'main' => 'Main',
                    ])
                    ->reactive(), // important for showing unit
                Select::make('unit')
                    ->label('Unit')
                    ->options([
                        'finance' => 'Finance',
                        'operational' => 'Operational',
                        'sales' => 'Sales',
                    ])
                    ->visible(fn ($get) => $get('area') === 'main'),

                // Select::make('region')
                //     ->label('Region')
                //     ->required()
                //     ->options([
                //         'jakarta' => 'Jakarta',
                //         'jawa-barat' => 'Jawa Barat',
                //         'jawa-tengah' => 'Jawa Tengah',
                //         'jawa-timur' => 'Jawa Timur',
                //         'bali' => 'Bali',
                //     ])
                //     ->visible(fn ($get) => $get('area') === 'branch'),

                Select::make('region_id')
                        ->label('Region')
                        ->relationship('region', 'name') 
                        ->required()
                        ->visible(fn ($get) => $get('area') === 'branch'),

                Select::make('spatie_role')
                    ->label('Permission')
                    ->options(
                        \Spatie\Permission\Models\Role::all()
                            ->pluck('name')
                            ->mapWithKeys(fn ($name) => [
                                $name => \Illuminate\Support\Str::of($name)
                                    ->replace('_', ' ')
                                    ->title()
                                    ->toString()
                            ])
                            ->toArray()
                    )
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record && $record->roles->count() > 0) {
                            $component->state($record->roles->first()->name);
                        }
                    })
                    ->required(),
                
                TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->dehydrated(fn ($state) => filled($state)) // only send if filled
                        ->required(fn (string $context) => $context === 'create') // only required on create
                        ->maxLength(255)
                        ->dehydrateStateUsing(fn ($state) => bcrypt($state)) // hash if filled
                        ->autocomplete('new-password')
                    
            ]),
        ]);
    
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email'),
                TextColumn::make('phone')
                    ->label('Nomor Telepon'),
                TextColumn::make('role')->label('Jabatan'),
               TextColumn::make('spatie_role_label')
                ->label('Permission')
                ->formatStateUsing(fn ($state, $record) =>
                    $record->roles->first()
                        ? \Illuminate\Support\Str::of($record->roles->first()->name)->replace('_', ' ')->title()->toString()
                        : '-'
                ),
                TextColumn::make('area'),
                TextColumn::make('unit'),
                TextColumn::make('region.name'),
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            // 'view' => Pages\ViewUser::route('/{record}'), // uncomment if needed
        ];
    }
}
