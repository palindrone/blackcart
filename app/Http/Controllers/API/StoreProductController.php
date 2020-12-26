<?php

namespace App\Http\Controllers\API;

use App\Exceptions\InvalidStoreException;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreProductController extends Controller
{
    //


    /**
     * Display a listing of all products for the Store provided
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Store $store)
    {
        //
        try {
            // $this->authorize("list"); TODO Complete these
            // $this->validate($request, []);

            return response([
                "status" => "success",
                "products" => $store->products()
            ], 200);
        } catch (InvalidStoreException $e) {
            // Invalid Store ID is a safe exception to return to the user
            return response([
                "status" => "failure",
                "message" => __($e->getMessage())
            ], 400);
        } catch (\Exception $e) {
            LogHelper::logException($e);
            return response([
                "status" => "failure",
                "message" => __("There was an error. Please check your parameters and try again. If the issue persists, contact technical support")
                //"message" => $e->getMessage() . " " . $e->getFile().":".$e->getLine()
            ], 500);
        }
    }
}
