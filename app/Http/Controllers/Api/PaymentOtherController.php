<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentOthers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentOtherController extends Controller
{

    protected $model;

    public function __construct(PaymentOthers $others)
    {
        $this->model = $others;
    }

    public function index(Request $request)
    {
        $others = DB::table('Payment_Others');
    }
}
