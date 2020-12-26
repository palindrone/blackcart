<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 2020-12-21
 * Time: 2:15 PM
 */

namespace App\Interfaces;


interface ChannelAPIInterface {


    public function products($asJson = false);

    public function product($id);

    public function convertToProduct($productData);

    public function loadProductData();
}