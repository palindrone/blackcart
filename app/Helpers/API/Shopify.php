<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 2020-12-21
 * Time: 2:14 PM
 */

namespace App\Helpers\API;


use App\Interfaces\ChannelAPIInterface;

class Shopify implements ChannelAPIInterface {

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
     * Convert individual products from the Shopify format into the BlackCart format
     *
     * @param $productData object the product data for a single product. See Shopify documentation.
     * @return array the product data, formatted to BlackCart standards
     */
    public function convertToProduct($productData) {
        $optionNames = [];
        $optionIndex = 0;
        $inventoryTotal = 0;
        $prices = [];
        $weights = [];

        // Process Product Options
        $options = array_map(function($option) use (&$optionNames, &$optionIndex) {
            $optionIndex ++;
            $optionNames[$optionIndex] = $option->name;

            return [
                "name" => $option->name,
                "values" => $option->values
            ];
        }, $productData->options);

        // Process Product Variants
        $variants = array_map(function($variant) use ($optionNames, &$inventoryTotal, &$prices, &$weights) {
            $variantData = [
                "name" => $variant->title,
                "price" => $variant->price,
                "prices" => array_map(function($priceArray) use (&$prices) {
                    $variantPrice = [
                        "amount" => $priceArray->price->amount,
                        "currency" => $priceArray->price->currency_code
                    ];

                    $prices[] = $variantPrice;
                    return $variantPrice;
                }, $variant->presentment_prices),
                "weight" => [
                    "value" => $variant->weight,
                    "unit" => $variant->weight_unit
                ],
                "inventory" => $variant->inventory_quantity,
                "inventory_status" => $variant->inventory_quantity > 0 ? "instock" : "outofstock"
            ];

            // Shopify only allows 3 options
            foreach ([1, 2, 3] as $optId) {
                if (is_null($variant->{"option".$optId})) {
                    break;
                }
                $variantData[$optionNames[$optId]] = $variant->{"option".$optId};
            }

            $weights[] = $variantData['weight'];
            $inventoryTotal += $variantData['inventory'];

            return $variantData;
        }, $productData->variants);

        return [
            "id" => $productData->id,
            "name" => $productData->title,
            "options" => $options,
            "variants" => $variants,
            "price" => "{$prices[0]['amount']} {$prices[0]['currency']}",
            "prices" => array_unique($prices, SORT_REGULAR),
            "price_range" => implode(" - ", $this->getPricesRange($prices)),
            "inventory_total" => $inventoryTotal,
            "inventory_status" => $inventoryTotal > 0 ? "instock" : "outofstock",
            "weight" => implode(" ", $weights[0]),
            "weights" => array_unique($weights, SORT_REGULAR)
        ];
    }

    /**
     * Load product and variant data from Shopify
     *
     * TODO Replace this with calls to Shopify API
     * @param array $options product options to send to the Shopify API
     * @return array the product objects
     */
    public function loadProductData($params = []) {
        $jsonFile = resource_path("data/shopify.json");
        $productJsonData = json_decode(file_get_contents($jsonFile));

        // The shopify API returns an object with the property "products"
        $productArray = $productJsonData->products;

        return $productArray;
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
            $price = (object)$price;

            $priceAmount = doubleval($price->amount);
            if (is_null($min) || $priceAmount < $min) {
                $min = $priceAmount;
            }

            if (is_null($max) || $priceAmount > $max) {
                $max = $priceAmount;
            }
        }

        return array_unique([
            $min, $max
        ]);
    }
}