<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOthers extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table ="payment_others";

    protected $fillable = ['gateway','label', 'detail', 'img_url', 'api_sufix', 'order', 'inserted_for'];

}
