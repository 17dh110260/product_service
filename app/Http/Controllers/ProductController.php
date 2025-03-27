<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    //
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $query = DB::table("products")->select();
        if ($id) {
            $query->where("id", $id);
        }
        $getProduct = $query->get();
        return \response()->json([
            "data" => $getProduct,
            "message" => "Product(s) fetched successfully"
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $control = $data['control'] ?? 0;
        if ($control === 0) {
            $insert = DB::table("products")->insert(
                [
                    "name" => $data['name'],
                    "price" => $data['price'],
                    "description" => $data['description']
                ]
            );
        }

        if ($control === 1) {
            $update = DB::table("products")->where('id', $data['id'])->update(
                [
                    "name" => $data['name'],
                    "price" => $data['price'],
                    "description" => $data['description']
                ]
            );
        }
        return \response()->json(
            [
                "data" => $data,
                "message" => true
            ]
        );
    }
}
