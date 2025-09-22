<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'magento_code' => 'checkmo',
                'biso_payment_method' => 'Bank Transfer',
                'biso_forms_of_payment' => 'Check/Money Order',
                'max_installments' => 1,
                'is_active' => true,
                'description' => 'Pagamento por transferência bancária ou boleto',
            ],
            [
                'magento_code' => 'banktransfer',
                'biso_payment_method' => 'Bank Transfer',
                'biso_forms_of_payment' => 'Bank Transfer',
                'max_installments' => 1,
                'is_active' => true,
                'description' => 'Transferência bancária direta',
            ],
            [
                'magento_code' => 'creditcard',
                'biso_payment_method' => 'Credit Card',
                'biso_forms_of_payment' => 'Credit Card',
                'max_installments' => 12,
                'is_active' => true,
                'description' => 'Cartão de crédito (Visa, Mastercard, etc.)',
            ],
            [
                'magento_code' => 'pix',
                'biso_payment_method' => 'Pix',
                'biso_forms_of_payment' => 'Pix',
                'max_installments' => 1,
                'is_active' => true,
                'description' => 'Pagamento instantâneo via Pix',
            ],
            [
                'magento_code' => 'paypal',
                'biso_payment_method' => 'Digital Wallet',
                'biso_forms_of_payment' => 'PayPal',
                'max_installments' => 1,
                'is_active' => true,
                'description' => 'Pagamento via PayPal',
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['magento_code' => $method['magento_code']],
                $method
            );
        }
    }
}