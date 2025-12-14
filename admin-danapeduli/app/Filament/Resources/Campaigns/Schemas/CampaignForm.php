<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use App\Models\Campaign;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Info Campaign')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->live(),

                    Placeholder::make('slug_preview')
                        ->label('Slug (otomatis)')
                        ->content(
                            fn(?Campaign $record, Get $get) =>
                            $record?->slug ?? Str::slug($get('title') ?? '')
                        ),

                    RichEditor::make('description')
                        ->required()
                        ->columnSpanFull(),

                    FileUpload::make('cover_image')
                        ->disk('public')
                        ->directory('campaigns')
                        ->image()
                        ->imageEditor()
                        ->nullable(),
                ])
                ->columns(1),

            Section::make('Tipe & Target')
                ->schema([
                    Select::make('type')
                        ->options([
                            'DONATION' => 'Donasi',
                            'CROWDFUND' => 'Galang Dana',
                        ])
                        ->required()
                        ->live(),

                    Select::make('goal_type')
                        ->options([
                            'AMOUNT' => 'Pakai Target Nominal',
                            'NONE'   => 'Tanpa Target',
                        ])
                        ->required()
                        ->live()
                        ->hidden(fn(Get $get) => $get('type') === 'CROWDFUND'),

                    TextInput::make('target_amount')
                        ->label('Target (Rp)')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required()
                        ->rules([
                            fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $value = (int) $value;

                                if ($get('type') === 'CROWDFUND' && $value <= 0) {
                                    $fail('Target wajib lebih dari 0 untuk Galang Dana.');
                                }

                                if ($get('type') === 'DONATION' && $get('goal_type') === 'NONE' && $value !== 0) {
                                    $fail('Donasi tanpa target harus bernilai 0.');
                                }
                            },
                        ])
                        ->helperText('Galang Dana wajib pakai target > 0. Donasi tanpa target: isi 0.'),

                    Select::make('status')
                        ->options([
                            'DRAFT'    => 'Draft',
                            'ACTIVE'   => 'Aktif',
                            'CLOSED'   => 'Ditutup',
                            'ARCHIVED' => 'Arsip',
                        ])
                        ->required(),

                    TextInput::make('total_paid')
                        ->label('Total Terkumpul (Rp)')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn($state) => $state ?? 0)
                        ->helperText('Angka ini otomatis dari pembayaran sukses (webhook Midtrans).'),

                    Select::make('auto_close_on_target')
                        ->label('Auto tutup saat target tercapai')
                        ->options([
                            1 => 'Ya',
                            0 => 'Tidak',
                        ])
                        ->default(1)
                        ->hidden(fn(Get $get) => $get('goal_type') === 'NONE'),
                ])
                ->columns(2),

            Section::make('Jadwal')
                ->schema([
                    DateTimePicker::make('open_at')->nullable(),
                    DateTimePicker::make('close_at')->nullable(),
                    DateTimePicker::make('closed_at')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Terisi otomatis ketika campaign ditutup sistem/admin.')
                        ->nullable(),

                    Placeholder::make('info_hint')
                        ->content('Sistem dapat menutup otomatis jika close_at terlewati (via scheduler).'),
                ])
                ->columns(2),
        ]);
    }
}
