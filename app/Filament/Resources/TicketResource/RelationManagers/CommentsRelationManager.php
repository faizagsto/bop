<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User'),
                
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->colors([
                        'success' => 'Approve',
                        'danger'  => 'Close',
                        'warning' => 'Revision',
                    ]),
                
                Tables\Columns\TextColumn::make('body')
                    ->label('Comment')
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('attachment_label')
                    ->label('Attachment')
                    ->getStateUsing(fn ($record) => $record->attachment_path 
                        ? '📎 Download Attachment' 
                        : 'No attachment')
                    ->url(fn ($record) => $record->attachment_path 
                        ? Storage::url($record->attachment_path) 
                        : null)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: false),
                
                Tables\Columns\TextColumn::make('transfer_date')
                    ->label('Transfer Date')
                    ->date()
                    ->placeholder('Not set'), // Use placeholder instead of default
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Action Date')
                    ->date(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make()
                    ->modalHeading('Comment Details')
                    ->modalContent(function ($record) {
                        $transferDate = $record->transfer_date 
                            ? $record->transfer_date->format('d M Y') 
                            : 'Not set';
                        
                        return new HtmlString(
                            '<div class="space-y-4">'
                                . '<div><strong>User:</strong> ' . e($record->user->name) . '</div>'
                                . '<div><strong>Action:</strong> ' . e($record->action) . '</div>'
                                . '<div><strong>Transfer Date:</strong> ' . e($transferDate) . '</div>'
                                . '<div class="border-t pt-2"><strong>Comment:</strong><br>' 
                                    . nl2br(e($record->body)) . '</div>'
                                . '<div><strong>Attachment:</strong> ' 
                                    . ($record->attachment_path
                                        ? '<a href="' . e(Storage::url($record->attachment_path)) 
                                            . '" target="_blank" class="text-primary-500 hover:underline">📎 Download</a>'
                                        : 'None'
                                    ) . '</div>'
                                . '<div class="text-sm text-gray-500">'
                                    . 'Posted: ' . $record->created_at->format('d M Y H:i')
                                . '</div>'
                            . '</div>'
                        );
                    })
                    ->modalWidth('xl'),
            ]);
    }
}