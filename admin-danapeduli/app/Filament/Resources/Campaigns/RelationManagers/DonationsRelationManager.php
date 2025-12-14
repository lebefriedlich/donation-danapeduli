<?php

namespace App\Filament\Resources\Campaigns\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\CampaignDonationsExport;
use Filament\Actions\Action as ActionsAction;
use Maatwebsite\Excel\Facades\Excel;

class DonationsRelationManager extends RelationManager
{
    protected static string $relationship = 'donations';
    protected static ?string $title = 'Donatur';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->latest())
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('donor_name')
                    ->label('Donatur')
                    ->formatStateUsing(fn($state, $record) => $record->is_anonymous ? 'Orang Baik' : ($state ?? '-'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'PENDING',
                        'success' => 'PAID',
                        'danger'  => 'FAILED',
                        'gray'    => 'EXPIRED',
                        'secondary' => 'REFUNDED',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                ActionsAction::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $campaign = $this->getOwnerRecord();

                        return Excel::download(
                            new CampaignDonationsExport($campaign->id),
                            'donations-campaign-' . $campaign->slug . '.xlsx'
                        );
                    }),
            ]);
    }
}
