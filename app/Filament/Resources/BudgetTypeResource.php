<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetTypeResource\Pages;
use App\Filament\Resources\BudgetTypeResource\RelationManagers;
use App\Models\BudgetType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;


class BudgetTypeResource extends Resource
{
    protected static ?string $model = BudgetType::class;

    protected static ?string $navigationIcon = 'heroicon-s-ellipsis-horizontal-circle';
    protected static ?string $navigationGroup = 'Budget Setup';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Budget Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->label('Budget Code')
                    ->helperText('Short, unique code like "bop", "thl"')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('code')->sortable()->searchable(),
            ])
            ->defaultSort('name');
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgetTypes::route('/'),
            'create' => Pages\CreateBudgetType::route('/create'),
            'edit' => Pages\EditBudgetType::route('/{record}/edit'),
        ];
    }
}
