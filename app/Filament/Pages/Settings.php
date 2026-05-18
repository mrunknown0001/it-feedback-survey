<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Branding;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'Appearance & Branding';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'primary_color' => Branding::primaryHex(),
            'brand_name' => Branding::brandName(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Branding')
                    ->description('Customize the public feedback form and admin panel appearance.')
                    ->schema([
                        Forms\Components\TextInput::make('brand_name')
                            ->label('Brand / Service Name')
                            ->maxLength(120)
                            ->required()
                            ->helperText('Shown in the public form header and footer.'),

                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('Primary Color')
                            ->required()
                            ->helperText('Hex color used across the public form and admin panel. Default: orange (#f97316).'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::set('brand_name', $state['brand_name']);
        Setting::set('primary_color', $state['primary_color']);

        Cache::forget('setting:brand_name');
        Cache::forget('setting:primary_color');

        Notification::make()
            ->title('Settings saved')
            ->body('Refresh the page to see panel color changes.')
            ->success()
            ->send();
    }
}
