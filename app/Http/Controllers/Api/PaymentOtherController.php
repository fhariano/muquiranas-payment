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
        if (($request->exists('app'))) {
            $others = DB::table('payment_others')->where('only_app', true)->orderBy('order')->get();
        } else if (($request->exists('pdv'))) {
            $others = DB::table('payment_others')->where('only_pdv', true)->orderBy('order')->get();
        } else if (($request->exists('totem'))) {
            $others = DB::table('payment_others')->where('only_totem', true)->orderBy('order')->get();
        }


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
