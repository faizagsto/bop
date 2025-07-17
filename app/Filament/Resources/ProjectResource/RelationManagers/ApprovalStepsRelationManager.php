<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\{Select, TextInput};
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApprovalStepsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvalSteps';

    protected static ?string $title = 'Approval Steps';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('step_order')
                ->label('Order')
                ->numeric()
                ->required(),

            Select::make('role')
                ->label('Role')
                ->options([
                    'bm' => 'Branch Manager',
                    'reviewer' => 'Reviewer',
                    'manager' => 'Manager',
                    'cashier' => 'Cashier',
                    'requester' => 'Requester',
                ])
                ->required(),

            Select::make('area')
                ->label('Area')
                ->options([
                    'branch' => 'Branch',
                    'main' => 'Main',
                ])
                ->required()
                ->live(),

            Select::make('unit')
                ->label('Unit')
                ->options([
                    'operational' => 'Operational',
                    'finance' => 'Finance',
                    'sales' => 'Sales',
                ])
                ->visible(fn (Get $get) => $get('area') === 'main')
                ->requiredIf('area', 'main'),
            Forms\Components\Toggle::make('is_final')
                ->label('Final Step')
                ->default(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('role')
            ->columns([
                TextColumn::make('step_order')->label('Order')->sortable(),
                TextColumn::make('role')->label('Role')->sortable(),
                TextColumn::make('area')->label('Area'),
                TextColumn::make('unit')->label('Unit'),
                // Tables\Columns\IconColumn::make('is_final')->boolean()->label('Final'),

            ])
            ->defaultSort('step_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
