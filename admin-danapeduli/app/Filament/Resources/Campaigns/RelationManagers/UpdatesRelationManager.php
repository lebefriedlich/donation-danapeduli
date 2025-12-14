<?php

namespace App\Filament\Resources\Campaigns\RelationManagers;

use App\Models\CampaignUpdate;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get as UtilitiesGet;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class UpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'updates';
    protected static ?string $title = 'Kabar Terbaru';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Update')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\Toggle::make('is_financial_update')
                        ->label('Ini penyaluran dana')
                        ->default(false)
                        ->live(),

                    Forms\Components\TextInput::make('disbursed_amount')
                        ->label('Jumlah Disalurkan (Rp)')
                        ->numeric()
                        ->minValue(1)
                        ->nullable()
                        ->hidden(fn(\Filament\Schemas\Components\Utilities\Get $get) => ! $get('is_financial_update'))
                        ->helperText('Isi jika update ini merekam penyaluran dana.')
                        ->rules([
                            fn(\Filament\Schemas\Components\Utilities\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($get('is_financial_update') && (! $value || (int) $value <= 0)) {
                                    $fail('Jumlah penyaluran wajib diisi dan > 0.');
                                }
                                if (! $get('is_financial_update') && ! is_null($value)) {
                                    $fail('Jika bukan penyaluran, jumlah harus dikosongkan.');
                                }
                            },
                        ]),

                    Forms\Components\RichEditor::make('content')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('attachment')
                        ->disk('public')
                        ->directory('campaign-updates')
                        ->preserveFilenames()
                        ->downloadable()
                        ->openable()
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->nullable()
                        ->rules([
                            fn(\Filament\Schemas\Components\Utilities\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($get('is_financial_update') && empty($value)) {
                                    $fail('Penyaluran dana wajib memiliki waktu publikasi.');
                                }
                            },
                        ]),
                ])
                ->columnSpanFull()
                ->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(60),

                Tables\Columns\IconColumn::make('is_financial_update')
                    ->label('Finansial')
                    ->boolean(),

                Tables\Columns\TextColumn::make('disbursed_amount')
                    ->label('Disalurkan')
                    ->money('IDR', true)
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Paksa konsisten: bukan financial -> amount harus null
                        if (empty($data['is_financial_update'])) {
                            $data['disbursed_amount'] = null;
                        }
                        return $data;
                    })
                    ->before(function (array $data) {
                        $this->validateDisbursement($data);
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['is_financial_update'])) {
                            $data['disbursed_amount'] = null;
                        }
                        return $data;
                    })
                    ->before(function (array $data, CampaignUpdate $record) {
                        $this->validateDisbursement($data, $record);
                    }),

                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private function validateDisbursement(array $data, ?CampaignUpdate $record = null): void
    {
        // Validasi over-disburse hanya jika ini financial update
        if (empty($data['is_financial_update'])) {
            return;
        }

        $campaign = $this->getOwnerRecord(); // Campaign model
        $currentAmount = (int) ($data['disbursed_amount'] ?? 0);

        if ($currentAmount <= 0) {
            throw ValidationException::withMessages([
                'disbursed_amount' => 'Jumlah penyaluran wajib diisi dan > 0.',
            ]);
        }

        $sumQuery = CampaignUpdate::query()
            ->where('campaign_id', $campaign->id)
            ->where('is_financial_update', true);

        // Kalau edit record lama, exclude dirinya sendiri biar tidak double hitung
        if ($record) {
            $sumQuery->where('id', '!=', $record->id);
        }

        $totalDisbursedOther = (int) $sumQuery->sum('disbursed_amount');
        $totalAfter = $totalDisbursedOther + $currentAmount;

        if ($totalAfter > (int) $campaign->total_paid) {
            throw ValidationException::withMessages([
                'disbursed_amount' => 'Total penyaluran melebihi dana terkumpul pada campaign ini.',
            ]);
        }
    }
}
