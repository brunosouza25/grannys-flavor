<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to get ProductStock
 */

class ProductStock extends Authentication{

	/** @const entity api url */
	const ENTITY = 'productstock';

    /** @const request type */
    const METHOD = 'POST';

	/** Product Stock array data structure
	[
        'loja =>  1, //integer
        'armazem' => 0, //integer
        'producto' => 100, // integer
        'stock' => 0.00, // float
        'stkmin' => 0.00, // float
        'stkmax' => 0.00, // float
        'data' => '2020-01-01', //Date
        'Store' => ''
    ];
    */

    private $warehouse;

    public function getWarehouse()
    {
        return $this->warehouse;
    }

    public function setWarehouse(int $warehouse = 0)
    {
        $this->warehouse = $warehouse;
    }

    private $store;

    public function getStore()
    {
        return $this->store;
    }

    public function setStore(int $store = 0)
    {
        $this->store = $store;
    }

    private $product;

    public function getProduct()
    {
        return $this->product;
    }

    public function setProduct(?int $product = 0)
    {
        $this->product = $product;
    }

    private $stock;

    public function getStock()
    {
        return $this->stock;
    }

    public function setStock(float $stock = 0.00)
    {
        $this->stock = $stock;
    }

    private $stockMin;

    public function getStockMin()
    {
        return $this->stockMin;
    }

    public function setStockMin(float $stockMin = 0.00)
    {
        $this->stockMin = $stockMin;
    }

    private $stockMax;

    public function getStockMax()
    {
        return $this->stockMax;
    }

    public function setStockMax(float $stockMax = 0.00)
    {
        $this->stockMax = $stockMax;
    }

    private $date;

    public function getDate()
    {
        return $this->date;
    }

    public function setDate()
    {
        $now = new \DateTime('now', new \DateTimeZone($_ENV['TIME_ZONE']));
        $this->date = $now->format('Y-m-d');
    }

    /**
    * Get a Product by is Code
    * @return json
    **/
    public function getProductStock()
    {
        $GLOBALS['ACCESS'] = 'product/getCurrentStockInstances';

        return parent::processData(
            ['produto' => $this->getProduct(),
            'armazem' => $this->getWarehouse(),
            'loja' => $this->getStore()
        ]);
    }

}