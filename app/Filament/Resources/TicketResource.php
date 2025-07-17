<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use Filament\Support\RawJs;
use App\Models\Project;
use App\Models\COATag;
use App\Models\ProjectName;
use Filament\Forms\Get;
use Filament\Forms\Set;     
use Filament\Forms\Components\{Card, Group, Placeholder, Repeater, Select, TextInput, Textarea, FileUpload, FieldSet, DatePicker, Section};
use App\Filament\Resources\TicketResource\RelationManagers\CommentsRelationManager;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;
    protected static ?string $navigationIcon = 'heroicon-s-document-text';
    protected static ?string $navigationGroup = 'Pengajuan';


    public function api()
    {
        $tickets = Ticket::all();
        return response()->json($tickets);
    }


    public static function getLabel(): string
    {
        return 'Pengajuan';
    }

    public static function getPluralLabel(): string
    {
        return 'Pengajuan';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

    if ($user->role === 'admin') {
        return Ticket::query()
            ->with(['viewableUsers', 'ticketBudgetTotals', 'ticketBudgetEntries', 'project.budgetTypes']);
    }

    return Ticket::query()
        ->with(['ticketBudgetTotals', 'ticketBudgetEntries', 'project.budgetTypes',])
        ->where(function ($query) use ($user) {
                $query->where(function ($subQuery) use ($user) {
                    $subQuery->where('responsible_role', $user->role)
                        ->where(function ($q) use ($user) {
                            $q->where(function ($q) use ($user) {
                                $q->where('responsible_area', 'branch')
                                    ->whereHas('owner', fn ($q) => $q->where('region_id', $user->region_id));
                            })->orWhere(function ($q) use ($user) {
                                $q->where('responsible_area', 'main')
                                    ->where('responsible_unit', $user->unit);
                            });
                        });
                });
            });
    }

    public static function applyEloquentFiltersToCollection(): bool
    {
        return true;
    }

// In the form() method, update the schema to include these changes:

public static function form(Form $form): Form
{
    return $form->schema([
        Section::make('Rincian Pengajuan')
            ->columnSpanFull()
            ->schema([
                Card::make()
                    ->columns(12)
                    ->schema([
                        Select::make('project_id')
                            ->label('ERS Type')
                            ->options(fn () => Project::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->columnSpan(5)
                             ->disabled(function (string $context, $record) {
                                if ($context !== 'edit') return false;
                                $user = auth()->user();
                                if ($record && $record->owner_id === $user->id) return false;
                                if (
                                    $record &&
                                    $user->role === 'reviewer' &&
                                    $user->unit === 'sales' &&
                                    $record->status === 'Revision : reviewer sales'
                                ) return false;
                                return true;
                            })
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('budgetEntriesData', []);
                            }),

                        // Add project name selection from backup
                        Select::make('project_name_id')
                            ->label('Nama Pengajuan/Project')
                            ->options(function () {
                                return ProjectName::query()
                                    ->select(['id', 'name', 'customer', 'period', 'pks_number'])
                                    ->get()
                                    ->mapWithKeys(function (ProjectName $project) {
                                        return [
                                            $project->id => "{$project->name} - {$project->customer} ({$project->period}) - PKS: {$project->pks_number}"
                                        ];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $project = ProjectName::find($state);
                                    if ($project) {
                                        $set('title', "{$project->name} - {$project->customer} ({$project->period}) - PKS: {$project->pks_number}");
                                    }
                                } else {
                                    $set('title', '');
                                }
                            })
                            ->columnSpan(7)
                            ->disabled(function (string $context, $record) {
                                if ($context !== 'edit') return false;
                                $user = auth()->user();
                                if ($record && $record->owner_id === $user->id) return false;
                                if (
                                    $record &&
                                    $user->role === 'reviewer' &&
                                    $user->unit === 'sales' &&
                                    $record->status === 'Revision : reviewer sales'
                                ) return false;
                                return true;
                            }),

                        TextInput::make('title')
                            ->label('Nama Pengajuan (Teks)')
                            ->dehydrated()
                            ->columnSpan(5)
                            ->hidden()
                            ->disabled(function (string $context, $record) {
                                if ($context !== 'edit') return false;
                                $user = auth()->user();
                                if ($record && $record->owner_id === $user->id) return false;
                                if (
                                    $record &&
                                    $user->role === 'reviewer' &&
                                    $user->unit === 'sales' &&
                                    $record->status === 'Revision : reviewer sales'
                                ) return false;
                                return true;
                            }),

                        Textarea::make('content')
                        ->label('Deskripsi')
                        ->placeholder('Jelaskan pengajuan')
                        ->required()
                        ->default('Pengajuan baru')
                        ->disabled(function (string $context, $record) {
                            if ($context !== 'edit') return false;
                            $user = auth()->user();
                            if ($record && $record->owner_id === $user->id) return false;
                            if (
                                $record &&
                                $user->role === 'reviewer' &&
                                $user->unit === 'sales' &&
                                $record->status === 'Revision : reviewer sales'
                            ) return false;
                            return true;
                        })                      
                        ->columnSpan(12),
                        DatePicker::make('expected_transfer_date')
                        ->label('Ekspektasi Tanggal Transfer')
                        ->required()
                        ->default(now()->addDays(7)->format('Y-m-d'))
                        ->disabled(function (string $context, $record) {
                            if ($context !== 'edit') return false;
                            $user = auth()->user();
                            if ($record && $record->owner_id === $user->id) return false;
                            if (
                                $record &&
                                $user->role === 'reviewer' &&
                                $user->unit === 'sales' &&
                                $record->status === 'Revision : reviewer sales'
                            ) return false;
                            return true;
                        })
                        ->columnSpan(6),

                        // Add file upload from backup
                        FileUpload::make('attachment_path')
                            ->preserveFilenames()
                            ->label('Lampiran')
                            ->directory('attachments')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'image/jpeg',
                                'image/png',
                                'image/jpg',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            ])
                            ->columnSpan(5)
                            ->openable()
                            ->disk('public')
                            ->multiple()
                            ->downloadable()
                            ->disabled(function (string $context, $record) {
                                if ($context !== 'edit') return false;
                                $user = auth()->user();
                                if ($record && $record->owner_id === $user->id) return false;
                                if (
                                    $record &&
                                    $user->role === 'reviewer' &&
                                    $user->unit === 'sales' &&
                                    $record->status === 'Revision : reviewer sales'
                                ) return false;
                                return true;
                            })
                            ->deletable(false)
                            ->visibility('public'),

                        // ... keep other existing fields ...

                        // Update total_project with validation from backup
                        TextInput::make('total_project')
                            ->label('Total Project')
                            ->visible(fn (string $context) => $context !== 'view')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 0)
                            JS))
                            ->stripCharacters(['.', ','])
                            ->formatStateUsing(fn ($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => str_replace(['.', ','], '', $state))
                            ->required()
                            ->placeholder('Masukkan Total Project')
                            ->numeric()
                            ->live()
                            ->prefix('Rp')
                            ->disabled(function (string $context, $record) {
                                if ($context !== 'edit') return false;
                                $user = auth()->user();
                                if ($record && $record->owner_id === $user->id) return false;
                                if (
                                    $record &&
                                    $user->role === 'reviewer' &&
                                    $user->unit === 'sales' &&
                                    $record->status === 'Revision : reviewer sales'
                                ) return false;
                                return true;
                            })
                            ->columnSpan(5)
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $totalProject = (float) str_replace(['.', ','], '', $value);
                                        $budgetTotals = $get('budgetTotalsData') ?? [];
                                        
                                        $sumAllBudgets = 0;
                                        foreach ($budgetTotals as $budgetTypeId => $data) {
                                            if (isset($data['budget_total'])) {
                                                $sumAllBudgets += (float) str_replace(['.', ','], '', $data['budget_total']);
                                            }
                                        }
                                        
                                        if ($totalProject <= $sumAllBudgets) {
                                            $formattedProject = 'Rp ' . number_format($totalProject, 0, ',', '.');
                                            $formattedSum = 'Rp ' . number_format($sumAllBudgets, 0, ',', '.');
                                            $fail("Total Project ($formattedProject) harus lebih besar dari Total Semua Budget ($formattedSum)");
                                        }
                                    };
                                }
                            ]),
                    ])
            ]),
            
        Section::make('Budget')
            ->columnSpanFull()
            ->schema(function (Get $get, $livewire, $set, $record = null) {
                $project = $record?->project ?? null;
                if (!$project) {
                    $projectId = $get('project_id');
                    if ($projectId) {
                        $project = Project::with('budgetTypes')->find($projectId);
                    }
                }
                if (!$project) {
                    return [];
                }

                return $project->budgetTypes->map(function ($budgetType) use ($get, $record) {
                    $budgetTypeId = $budgetType->id;

                    // Try to find existing budget entry for this budget type if editing
                    $entry = $record?->ticketBudgetEntries->firstWhere('budget_type_id', $budgetTypeId);

                    // The form keys for budget and details
                    $totalField = "budgetTotalsData.{$budgetTypeId}.budget_total";
                    $entriesField = "budgetEntriesData.{$budgetTypeId}";

                    return Card::make([
                        TextInput::make($totalField)
                            ->label("Total Budget: {$budgetType->name}")
                            ->default($entry ? number_format($entry->budget ?? 0, 0, ',', '.') : null)
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 0)
                            JS))
                            ->stripCharacters(['.', ','])
                            ->formatStateUsing(fn ($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => str_replace(['.', ','], '', $state))
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->live()
                             ->disabled(function (string $context, $record) {
                                if ($context !== 'edit') return false;
                                $user = auth()->user();
                                if ($record && $record->owner_id === $user->id) return false;
                                if (
                                    $record &&
                                    $user->role === 'reviewer' &&
                                    $user->unit === 'sales' &&
                                    $record->status === 'Revision : reviewer sales'
                                ) return false;
                                return true;
                            })
                            ->rules([
                                function (Get $get) use ($entriesField) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $entriesField) {
                                        $budget = (float) str_replace(['.', ','], '', $value ?? '0');
                                        $entries = $get($entriesField) ?? [];
                                        $total = collect($entries)->sum(fn ($e) => (float) str_replace(['.', ','], '', $e['amount'] ?? '0'));

                                        if (abs($total - $budget) > 0.01) {
                                            $fail("Total details (Rp" . number_format($total, 0, ',', '.') . ") must match budget (Rp" . number_format($budget, 0, ',', '.') . ")");
                                        }
                                    };
                                },
                            ]),

                        // Add summary placeholder from backup
                        Placeholder::make("{$budgetTypeId}_total_summary")
                            ->label("Total for {$budgetType->name}")
                            ->content(function (Get $get) use ($totalField, $entriesField) {
                                $budget = (float) str_replace(['.', ','], '', $get($totalField) ?? '0');
                                $entries = $get($entriesField) ?? [];
                                $total = collect($entries)->sum(fn ($e) => (float) str_replace(['.', ','], '', $e['amount'] ?? '0'));

                                $formattedBudget = 'Rp' . number_format($budget, 0, ',', '.');
                                $formattedTotal = 'Rp' . number_format($total, 0, ',', '.');

                                if (abs($total - $budget) > 0.01) {
                                    return "⚠️ Mismatch! Budget: {$formattedBudget} | Total: {$formattedTotal}";
                                }

                                return "✅ Valid: {$formattedTotal}";
                            })
                            ->extraAttributes(['class' => 'text-sm text-gray-500'])
                            ->reactive(),

                        Repeater::make($entriesField)
                            ->label("{$budgetType->name} Details")
                            ->schema([
                                Select::make('coa_tag_id')
                                    ->label('COA Tag')
                                    ->options(COATag::where('budget_type_id', $budgetTypeId)
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->disabled(function (string $context, $record) {
                                        if ($context !== 'edit') return false;
                                        $user = auth()->user();
                                        if ($record && $record->owner_id === $user->id) return false;
                                        if (
                                            $record &&
                                            $user->role === 'reviewer' &&
                                            $user->unit === 'sales' &&
                                            $record->status === 'Revision : reviewer sales'
                                        ) return false;
                                        return true;
                                    })
                                    ->required(),   

                                TextInput::make('amount')
                                    ->label('Amount')
                                    ->mask(RawJs::make(<<<'JS'
                                        $money($input, ',', '.', 0)
                                    JS))
                                    ->stripCharacters(['.', ','])
                                    ->formatStateUsing(fn ($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
                                    ->dehydrateStateUsing(fn ($state) => str_replace(['.', ','], '', $state))
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('Masukkan nilai')
                                    ->disabled(function (string $context, $record) {
                                        if ($context !== 'edit') return false;
                                        $user = auth()->user();
                                        if ($record && $record->owner_id === $user->id) return false;
                                        if (
                                            $record &&
                                            $user->role === 'reviewer' &&
                                            $user->unit === 'sales' &&
                                            $record->status === 'Revision : reviewer sales'
                                        ) return false;
                                        return true;
                                    })
                                    ->live(),
                            ])
                            ->columns(2)
                            ->default(
                                $record?->ticketBudgetEntries
                                    ?->where('budget_type_id', $budgetTypeId)
                                    ->map(fn ($e) => [
                                        'coa_tag_id' => $e->coa_tag_id,
                                        'amount' => number_format($e->budget, 0, ',', '.'),
                                    ])
                                    ->toArray() ?? []
                            )
                            ->live()
                            ->reactive()
                            ->addActionLabel('Tambah Detail'),
                    ]);
                })->toArray();
            })
    ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pengajuan')->label('Nomor Pengajuan')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Title')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('owner.name')->label('Owner')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('project.name')->label('Project')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Phone')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state){
                        'Done' => 'success',
                        'Closed' => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('total_project')
                    ->label('Total Project')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('total_budget')
                    ->label('Total Budget')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('expected_transfer_date')
                    ->label('Ekspektasi Tanggal Transfer')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record): bool => auth()->user()->can('update', $record)),
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
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
            'view' => Pages\ViewTicket::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }
}