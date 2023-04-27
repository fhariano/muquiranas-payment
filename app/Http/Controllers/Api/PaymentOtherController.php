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
        $onlyPdv = ($request->exists('pdv')) ? true : false;
        $others = DB::table('payment_others')->where('only_pdv', $onlyPdv)->orderBy('order')->get();

        if ($others->isEmpty()) {
            return response()->json([
                "error" => true,
                "message" => "Nenhum registro foi encontrado!",
                "data" => [],
            ], 404);
        }

        return response()->json([
            "error" => false,
            "message" => "Lista de outros pagamentos!",
            "data" => $others,
        ], 200);
    }
}
