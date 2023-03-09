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
    }
}
