<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectNameResource\Pages;
use App\Filament\Resources\ProjectNameResource\RelationManagers;
use App\Models\ProjectName;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectNameResource extends Resource
{
    protected static ?string $model = ProjectName::class;

    protected static ?string $navigationIcon = 'heroicon-s-queue-list';
    protected static ?string $navigationGroup = 'Project Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('customer')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('period')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('pks_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('from_date')
                    ->label('From Date')
                    ->required(),
                Forms\Components\DatePicker::make('to_date')
                    ->label('To Date')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('period')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pks_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('from_date')
                    ->date()
                    ->label('From Date'),
                Tables\Columns\TextColumn::make('to_date')
                    ->date()
                    ->label('To Date'),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectNames::route('/'),
            'create' => Pages\CreateProjectName::route('/create'),
            'edit' => Pages\EditProjectName::route('/{record}/edit'),
        ];
    }
}
