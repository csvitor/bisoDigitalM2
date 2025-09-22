<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Formas de Pagamento';

    protected static ?string $modelLabel = 'Forma de Pagamento';

    protected static ?string $pluralModelLabel = 'Formas de Pagamento';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('magento_code')
                    ->label('Código do Magento')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('Ex: checkmo, banktransfer, creditcard')
                    ->helperText('Código do método de pagamento no Magento'),

                TextInput::make('biso_payment_method')
                    ->label('Método de Pagamento (Biso)')
                    ->required()
                    ->placeholder('Ex: Credit Card, Pix, Bank Transfer')
                    ->helperText('Nome do método para enviar à API da Biso'),

                TextInput::make('biso_forms_of_payment')
                    ->label('Formas de Pagamento (Biso)')
                    ->placeholder('Ex: Visa, Mastercard, Pix')
                    ->helperText('Descrição específica da forma de pagamento (opcional)'),

                TextInput::make('max_installments')
                    ->label('Máximo de Parcelas')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(24)
                    ->helperText('Número máximo de parcelas permitidas'),

                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true)
                    ->helperText('Se este método está ativo no sistema'),

                Textarea::make('description')
                    ->label('Descrição')
                    ->placeholder('Descrição adicional do método de pagamento')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('magento_code')
                    ->label('Código Magento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('biso_payment_method')
                    ->label('Método Biso')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('biso_forms_of_payment')
                    ->label('Formas de Pagamento')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('max_installments')
                    ->label('Max. Parcelas')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}