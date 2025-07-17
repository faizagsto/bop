<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalStepResource\Pages;
use App\Filament\Resources\ApprovalStepResource\RelationManagers;
use App\Models\ApprovalStep;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\{Select, TextInput};
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Get;




class ApprovalStepResource extends Resource
{
    protected static ?string $model = ApprovalStep::class;

    protected static ?string $navigationIcon = 'heroicon-s-numbered-list';
    protected static ?string $navigationGroup = 'Project Management';


public static function form(Form $form): Form
{
    return $form
        ->schema([
            Select::make('project_id')
                ->relationship('project', 'name')
                ->required(),

            TextInput::make('step_order')
                ->numeric()
                ->required(),

            Select::make('role')
                ->options([
                    'bm' => 'Branch Manager',
                    'reviewer' => 'Reviewer',
                    'manager' => 'Manager',
                    'cashier' => 'Cashier',
                    'requester' => 'Requester',
                ])
                ->required(),

            Select::make('area')
                ->options([
                    'branch' => 'Branch',
                    'main' => 'Main',
                ])
                ->required()
                ->live(),

            Select::make('unit')
                ->options([
                    'operational' => 'Operational',
                    'finance' => 'Finance',
                    'sales' => 'Sales',
                ])
                ->visible(fn (Get $get) => $get('area') === 'main')
                ->requiredIf('area', 'main'),



        ]);
}
    

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')->label('ERS Type')->sortable(),
                TextColumn::make('step_order')->label('Order')->sortable(),
                TextColumn::make('role')->label('Role')->sortable(),
                TextColumn::make('area')->label('Area'),
                TextColumn::make('unit')->label('Unit')->toggleable(),
                // TextColumn::make('region')->label('Region')->toggleable(),
                // Tables\Columns\IconColumn::make('is_final')->boolean()->label('Final'),
            ])
            ->defaultSort('project_id', 'asc')
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
            'index' => Pages\ListApprovalSteps::route('/'),
            'create' => Pages\CreateApprovalStep::route('/create'),
            'edit' => Pages\EditApprovalStep::route('/{record}/edit'),
        ];
    }
}
