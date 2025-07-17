<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;


class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-s-window';
    protected static ?string $navigationGroup = 'Project Management';

    public static function getLabel(): string
    {
        return 'ERS Type';
    }

    public static function getPluralLabel(): string
    {
        return 'ERS Type';
    }

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('name')
                ->label('ERS Name')
                ->required(),

            Textarea::make('description')
                ->label('Description')
                ->rows(4)
                ->required(),
            Select::make('budgetTypes')
                ->label('Budget Types')
                ->multiple()
                ->relationship('budgetTypes', 'name')
                ->preload()
                ->required()

        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('description')->limit(50),
            Tables\Columns\TextColumn::make('budgetTypes.name')
                ->label('Budget Types')
                ->sortable()
                ->searchable(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}


    public static function getRelations(): array
{
    return [
        \App\Filament\Resources\ProjectResource\RelationManagers\ApprovalStepsRelationManager::class,
    ];
}



    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
