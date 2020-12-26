<?php

namespace App\Models;

use App\Exceptions\InvalidStoreException;
use App\Helpers\API\Shopify;
use App\Helpers\API\Woocommerce;
use App\Interfaces\ChannelAPIInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    const kShopify = 1;
    const kWoocommerce = 2;

    /**
     * @param $storeId
     * @return array
     * @throws InvalidStoreException
     */
    public function products() {

        /**
         * @var $api ChannelAPIInterface The Channel Interface (in App/Helpers/API) from which we are loading our data
         */
        $api = new $this->api_class();
        return $api->products();
    }
}
