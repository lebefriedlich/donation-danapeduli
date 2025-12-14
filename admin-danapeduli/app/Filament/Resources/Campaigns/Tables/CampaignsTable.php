<?php

namespace App\Filament\Resources\Campaigns\Tables;

use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Number;

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Target')
                    ->formatStateUsing(fn ($state) => 'Rp' . Number::format($state, locale: 'id'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_paid')
                    ->label('Terkumpul')
                    ->formatStateUsing(fn ($state) => 'Rp' . Number::format($state, locale: 'id'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('open_at')
                    ->label('Buka')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('close_at')
                    ->label('Tutup')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'DONATION' => 'Donasi',
                        'CROWDFUND' => 'Galang Dana',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'DRAFT' => 'Draft',
                        'ACTIVE' => 'Aktif',
                        'CLOSED' => 'Ditutup',
                        'ARCHIVED' => 'Arsip',
                    ]),
            ])
            // ->actions([
            //     Tables\Actions\EditAction::make(),
            // ])
            ->defaultSort('created_at', 'desc');
    }
}
