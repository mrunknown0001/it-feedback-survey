<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class AdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Administrators';

    protected static ?string $modelLabel = 'Administrator';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('role', 'admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Account Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(User::class, 'email', ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->minLength(8)
                        ->confirmed()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave blank to keep current password.' : null),

                    Forms\Components\TextInput::make('password_confirmation')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->dehydrated(false),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_first_admin')
                    ->label('Root Admin')
                    ->getStateUsing(fn (User $record) => static::isFirstAdmin($record))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn (User $record) => static::isFirstAdmin($record) ? 'Root admin — only editable by themselves.' : null),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (User $record) => static::isFirstAdmin($record) && auth()->id() !== static::firstAdminId()),

                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (User $record) => static::isFirstAdmin($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, \Illuminate\Database\Eloquent\Collection $records) {
                            $firstAdminId = static::firstAdminId();
                            if ($records->contains('id', $firstAdminId)) {
                                $action->cancel();
                                \Filament\Notifications\Notification::make()
                                    ->title('Action not allowed')
                                    ->body('The root administrator cannot be deleted.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }

    public static function canEdit(Model $record): bool
    {
        if (static::isFirstAdmin($record)) {
            return auth()->id() === static::firstAdminId();
        }

        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return ! static::isFirstAdmin($record);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit'   => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    protected static ?int $cachedFirstAdminId = null;

    public static function firstAdminId(): ?int
    {
        if (static::$cachedFirstAdminId === null) {
            static::$cachedFirstAdminId = User::where('role', 'admin')
                ->orderBy('id')
                ->value('id');
        }

        return static::$cachedFirstAdminId;
    }

    public static function isFirstAdmin(User $record): bool
    {
        return $record->id === static::firstAdminId();
    }
}
