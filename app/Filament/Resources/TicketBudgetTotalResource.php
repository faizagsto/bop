<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketBudgetTotalResource\Pages;
use App\Filament\Resources\TicketBudgetTotalResource\RelationManagers;
use App\Models\TicketBudgetTotal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketBudgetTotalResource extends Resource
{
    protected static ?string $model = TicketBudgetTotal::class;

    protected static ?string $navigationIcon = 'heroicon-s-banknotes';
    protected static ?string $navigationGroup = 'Budget Setup';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket.nomor_pengajuan')
                    ->label('Nomor Pengajuan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ticket.title')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('budgetType.name')
                    ->label('Budget Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('budget_total')
                    ->label('Total Budget Amount')
                    ->money('idr', true)
                    ->sortable(),
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
            'index' => Pages\ListTicketBudgetTotals::route('/'),
            'create' => Pages\CreateTicketBudgetTotal::route('/create'),
            'edit' => Pages\EditTicketBudgetTotal::route('/{record}/edit'),
        ];
    }
}
