<?php

namespace Database\Seeders;

use App\Models\PaymentOthers;
use Illuminate\Database\Seeder;

class PaymentOthersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payment = PaymentOthers::create([
            'gateway' => 'Getnet',
            'label' => 'Via Pix',
            'detail' => 'Aprovação Imediata',
            'img_url' => 'https://admin-h.muquiranasbar.com.br/img/pix.png',
            'api_sufix' => '/getnet/pix',
            'order' => 1,
            'inserted_for' => 'Flavio Ariano',
        ]);

        $payment->create([
            'gateway' => 'Getnet',
            'label' => 'Débito',
            'detail' => 'Visa, Mastercard e Elo',
            'img_url' => 'https://admin-h.muquiranasbar.com.br/img/debit.png',
            'api_sufix' => '/getnet/debit',
            'order' => 2,
            'inserted_for' => 'Flavio Ariano',
        ]);

        $payment->create([
            'gateway' => 'Getnet',
            'label' => 'Crédito',
            'detail' => 'Visa, Mastercard, Elo, AmEx e Hipercard',
            'img_url' => 'https://admin-h.muquiranasbar.com.br/img/credit.png',
            'api_sufix' => '/getnet/credit',
            'order' => 3,
            'inserted_for' => 'Flavio Ariano',
        ]);
    }
}
