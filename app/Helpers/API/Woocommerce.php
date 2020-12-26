<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 2020-12-21
 * Time: 2:14 PM
 */

namespace App\Helpers\API;


use App\Interfaces\ChannelAPIInterface;

class Woocommerce implements ChannelAPIInterface {

    public function products($asJson = false) {
        $productArray = $this->loadProductData();

        if ($asJson) {
            return $productArray;
        }

        return array_map([$this, "convertToProduct"], $productArray);
    }

    public function product($id) {
        // TODO: Implement product() method.
    }

    /**
     * Convert individual products from the WooCommerce format into the BlackCart format
     *
     * @param $productData object the product data for a single product (including variants, ideally). See WC documentation.
     * @return array the product data, formatted to BlackCart standards
     */
    public function convertToProduct($productData) {
        $optionNames = [];
        $optionIndex = 0;
        $inventoryTotal = 0;
        $prices = [$productData->price];
        $weights = [];

        // Process Product Options
        $options = array_map(function($option) use (&$optionNames, &$optionIndex) {
            $optionIndex ++;
            $optionNames[$optionIndex] = $option->name;

            return [
                "name" => $option->name,
                "values" => $option->options
            ];
        }, $productData->type == "simple" ? [] : $productData->attributes);

        // Process Product Variants
        $variants = array_map(function($variant) use ($productData, $optionNames, &$inventoryTotal, &$prices, &$weights) {
            $variantData = [
                "price" => isset($variant->price) ? $variant->price : $productData->price,
                "weight" => $variant->weight,
            ];

            foreach ($variant->attributes as $attribute) {
                $variantData[$attribute->name] = $attribute->option;
            }

            $weights[] = $variantData['weight'];
            if ($variant->manage_stock) {
                $inventoryTotal += $variantData['inventory'];
            } else {
                $variantData['inventory_status'] = $variant->stock_status;
            }

            $prices[] = $variantData['price'];


            return $variantData;
        }, $productData->type == "simple" ? [] : $productData->variations);

        $prices = array_values(array_filter($prices));

        // Process Product Data
        return [
            "id" => $productData->id,
            "name" => $productData->name,
            "options" => $options,
            "variants" => $variants,
            "price" => "\${$prices[0]} CAD",
            "prices" => array_unique($prices, SORT_REGULAR),
            "price_range" => implode(" - ", $this->getPricesRange($prices)),
            "inventory_total" => $inventoryTotal,
            "inventory_status" => $inventoryTotal > 0,
            "weight" => implode(" ", $weights),
            "weights" => array_unique($weights, SORT_REGULAR)
        ];
    }

    /**
     * Load product and variant data from Woocommerce
     *
     * TODO Replace this with calls to Woocommerce API
     * @param array $options product options to send to the woocommerce API
     * @return array the product objects
     */
    public function loadProductData($options = []) {
        $jsonFile = resource_path("data/woocommerce.json");
        $productJsonData = json_decode(file_get_contents($jsonFile));

        return $productJsonData;
    }

    /**
     * Get the low-high price range for this product and all of its variants
     *
     * If all variants have the same price, returns an array with one element, otherwise it returns an array
     *  where the 0th value is the minimum price and the 1st value is the maximum price
     *
     * @param $priceArray
     * @return array
     */
    private function getPricesRange($priceArray) {
        $min = $max = null;

        foreach ($priceArray as $price) {
            $priceAmount = doubleval($price);
            if (is_null($min) || $priceAmount < $min) {
                $min = $priceAmount;
            }

            if (is_null($max) || $priceAmount > $max) {
                $max = $priceAmount;
            }
        }

        return array_unique([
            number_format($min, 2), number_format($max, 2)
        ]);
    }
}