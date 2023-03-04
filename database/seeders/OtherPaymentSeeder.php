<?php

namespace Database\Seeders;

use App\Models\OtherPayments;
use Illuminate\Database\Seeder;

class OtherPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payment = OtherPayments::create([
            'gateway' => 'Getnet',
            'label' => 'Pix',
            'detail' => 'Aprovação Imediata',
            'img_url' => 'https://admin-h.muquiranasbar.com.br/img/pix.png',
            'api_sufix' => '/getnet/pix',
            'order' => 1,
            'inserted_for' => 'Flavio Ariano',
        ]);
    }
}
