<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\FeedbackResource\Pages;
use App\Models\Feedback;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'My Feedback';

    protected static ?string $navigationGroup = 'Survey';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('agents', fn (Builder $q) => $q->where('agents.id', auth()->user()->agent_id));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Respondent')
                ->schema([
                    Infolists\Components\TextEntry::make('respondent_name')->label('Name'),
                    Infolists\Components\TextEntry::make('position')->label('Position'),
                    Infolists\Components\TextEntry::make('overall_rating')
                        ->label('Overall Rating')
                        ->badge()
                        ->color(fn ($state) => match (true) {
                            $state >= 4.5 => 'success',
                            $state >= 3   => 'warning',
                            default       => 'danger',
                        })
                        ->formatStateUsing(fn ($state) => number_format($state, 2) . ' / 5.00'),
                    Infolists\Components\TextEntry::make('issueType.name')
                        ->label('Issue Type')
                        ->default('—'),
                    Infolists\Components\TextEntry::make('issue_description')
                        ->label('Issue Description')
                        ->default('—')
                        ->columnSpan(2),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Submitted At')
                        ->dateTime(),
                ])->columns(3),

            Infolists\Components\Section::make('Survey Answers')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('responses')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('question.question_text')
                                ->label('Question')
                                ->columnSpan(2),
                            Infolists\Components\TextEntry::make('rating_value')
                                ->label('Rating')
                                ->badge()
                                ->color(fn ($state) => match (true) {
                                    $state >= 4 => 'success',
                                    $state >= 3 => 'warning',
                                    $state !== null => 'danger',
                                    default     => 'gray',
                                })
                                ->formatStateUsing(fn ($state) => $state ? "⭐ {$state} / 5" : '—'),
                            Infolists\Components\TextEntry::make('text_value')
                                ->label('Comment')
                                ->default('—')
                                ->columnSpan(3),
                        ])->columns(5),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('respondent_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->searchable(),

                Tables\Columns\TextColumn::make('issueType.name')
                    ->label('Issue Type')
                    ->searchable(),

                Tables\Columns\TextColumn::make('overall_rating')
                    ->label('Rating')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 3   => 'warning',
                        default       => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ★')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF Report')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function () {
                        $agent = auth()->user()->agent;
                        $feedbacks = Feedback::whereHas(
                            'agents',
                            fn (Builder $q) => $q->where('agents.id', auth()->user()->agent_id)
                        )->with(['responses.question', 'issueType'])->get();

                        $pdf = Pdf::loadView('pdf.agent-feedback-report', [
                            'agent'     => $agent,
                            'feedbacks' => $feedbacks,
                            'generated' => now()->format('F j, Y g:i A'),
                        ])->setPaper('a4');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'feedback-report-' . now()->format('Y-m-d') . '.pdf'
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedback::route('/'),
            'view'  => Pages\ViewFeedback::route('/{record}'),
        ];
    }
}
