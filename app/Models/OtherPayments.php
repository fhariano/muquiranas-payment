<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherPayments extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table ="other_payments";

    protected $fillable = ['gateway','label', 'detail', 'img_url', 'api_sufix', 'order', 'inserted_for'];

}
