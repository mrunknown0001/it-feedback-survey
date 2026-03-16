<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Models\Feedback;
use App\Models\Location;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Feedback Responses';

    protected static ?string $navigationGroup = 'Survey';

    protected static ?int $navigationSort = 1;

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
                    Infolists\Components\TextEntry::make('locations.name')
                        ->label('Location / Area / Department')
                        ->badge()
                        ->separator(','),
                    Infolists\Components\TextEntry::make('agents.name')
                        ->label('IT Support Agent(s)')
                        ->badge()
                        ->separator(','),
                    Infolists\Components\TextEntry::make('overall_rating')
                        ->label('Overall Rating')
                        ->badge()
                        ->color(fn ($state) => match(true) {
                            $state >= 4.5 => 'success',
                            $state >= 3   => 'warning',
                            default       => 'danger',
                        })
                        ->formatStateUsing(fn ($state) => number_format($state, 2) . ' / 5.00'),
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
                                ->color(fn ($state) => match(true) {
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

                Tables\Columns\TextColumn::make('locations.name')
                    ->label('Location / Area / Dept.')
                    ->badge()
                    ->separator(',')
                    ->searchable(),

                Tables\Columns\TextColumn::make('agents.name')
                    ->label('Agent(s)')
                    ->badge()
                    ->separator(',')
                    ->searchable(),

                Tables\Columns\TextColumn::make('overall_rating')
                    ->label('Rating')
                    ->badge()
                    ->color(fn ($state) => match(true) {
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
            ->filters([
                Tables\Filters\SelectFilter::make('locations')
                    ->label('Location / Area / Department')
                    ->relationship('locations', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('agents')
                    ->relationship('agents', 'name')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListFeedback::route('/'),
            'view'  => Pages\ViewFeedback::route('/{record}'),
        ];
    }
}
