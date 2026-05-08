<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MatriculasRelationManager extends RelationManager
{
    protected static string $relationship = 'matriculas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('escola_id')
                    ->relationship('escola', 'nome')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('serie_escolar')
                    ->options(\App\Enums\SerieEscolar::class)
                    ->required(),
                Forms\Components\TextInput::make('ano_letivo')
                    ->numeric()
                    ->required()
                    ->minValue(2020)
                    ->maxValue(2025),
                Forms\Components\DatePicker::make('data_de_criacao')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(\App\Enums\StatusMatricula::class)
                    ->required(),
                Forms\Components\Select::make('resultado_final')
                    ->options(\App\Enums\ResultadoFinal::class)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('escola.nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serie_escolar')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ano_letivo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_de_criacao')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resultado_final')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
