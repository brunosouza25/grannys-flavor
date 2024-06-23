<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to CRUD Product
 */

class Product extends Authentication{

	/** @const entity api url */
	const ENTITY = 'product';

    /** @const request type */
    const METHOD = 'POST';

	/** Product array data structure
	[
        'codigo' => 100, // integer
        'codigo_alf' => 100, // integer
        'codigosbarras' => '46456456' // string
        'unidade' =>  1, // integer
        'descricao' => 'descrição completa do produto', //string Required
        'descricaocurta' => 'Caneta azul',  // string
        'familia' => 0, // integer
        'categoria' => 0, //integer
        'grupo' => 0 // integer
        'isencao' => 'M11' , // string
        'safttype' => 'P'. //
        'iva' => 23 , // integer the rate
        'fornecedor' => 0, //integer
        'datacriacao' => datetime, // ("Y-m-d H:i:s")
        'fundo' => '#808080', // string
        'letra"=> '#ffffff", //string
        'loja =>  1, //integer
        'retalho => 1, // integer
        'subfam => 0, // integer
        'vendersemstock' => 1 // integer
        'precovenda' => 0 //integer
    ];
    */


    private $salePrice;

    public function getSalePrice()
    {
        return $this->salePrice;
    }

    public function setSalePrice(float $salePrice = 0)
    {
        $this->salePrice = $salePrice;
    }

    private $isSellWithoutStock;

    public function getIsSellWithoutStock()
    {
        return $this->isSellWithoutStock;
    }

    public function setIsSellWithoutStock(int $isSellWithoutStock = 0)
    {
        $this->isSellWithoutStock = $isSellWithoutStock;
    }

    private $subFamily;

    public function getSubFamily()
    {
        return $this->subFamily;
    }

    public function setSubFamily(int $subFamily = 0)
    {
        $this->subFamily = $subFamily;
    }

    private $saftType;

    public function getSaftType()
    {
        return $this->saftType;
    }

    public function setSaftType(string $saftType = 'P')
    {
        $this->saftType = $saftType;
    }

    private $background;

    public function getBackground()
    {
        return $this->background;
    }

    public function setBackground(string $background = null)
    {
        $this->background = $background;
    }

    private $letter;

    public function getLetter()
    {
        return $this->letter;
    }

    public function setLetter(string $letter = null)
    {
        $this->letter = $letter;
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

    private $retail;

    public function getRetail()
    {
        return $this->retail;
    }

    public function setRetail(int $retail = 0)
    {
        $this->retail = $retail;
    }

    //code id integer
    private $code;

    public function getCode()
    {
        return $this->code;
    }

    public function setCode(int $code = 0)
    {
        $this->code = $code;
    }

    //codeAlf integer
    private $codeAlf;

    public function getCodeAlf()
    {
        return $this->codeAlf;
    }

    public function setCodeAlf(string $codeAlf = null)
    {
        $this->codeAlf = $codeAlf;
    }

    private $supplier;

    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setSupplier(int $supplier = 0)
    {
        $this->supplier = $supplier;
    }

    private $exemption;

    public function getExemption()
    {
        return $this->exemption;
    }

    public function setExemption(string $exemption = '')
    {
        $this->exemption = $exemption;
    }

    private $group;

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(int $group = 0)
    {
        $this->group = $group;
    }

    private $category;

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(int $category = 0)
    {
        $this->category = $category;
    }

    private $family;

    public function getFamily()
    {
        return $this->family;
    }

    public function setFamily(int $family = 0)
    {
        $this->family = $family;
    }

    //Unit integer
    private $unit;

    public function getUnit()
    {
        return $this->unit;
    }

    public function setUnit(int $unit = 0)
    {
        $this->unit = $unit;
    }

    private $description;

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(string $description = null)
    {
        $this->description = $description;
    }

    private $shortDescription;

    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription = null)
    {
        $this->shortDescription = $shortDescription;
    }

    private $createdAt;

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt()
    {
        $now = new \DateTime('now', new \DateTimeZone($_ENV['TIME_ZONE']));
        $this->createdAt = $now->format('Y-m-d');
    }

    //vat integer
    private $vat;

    public function getVat()
    {
        return $this->vat;
    }

    public function setVat(int $vat = 0)
    {
        $this->vat = $vat;
    }

    ####
    # gets and sets To filter results
    ####

    //To limit the search on API request
    private $limit;

    public function getLimit()
    {
        return $this->limit;
    }

    public function setLimit(int $limit = 250)
    {
        $this->limit = $limit;
    }

    //To offset the search on API request
    private $offset;

    public function getOffset()
    {
        return $this->offset;
    }

    public function setOffset(int $offset = 0)
    {
        $this->offset = $offset;
    }

    //To OrderBy the search on API request
    private $orderBy;

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function setOrderBy(string $orderBy = null)
    {
        $this->orderBy = $orderBy;
    }

    //To add to orderBy in the search on API request
    private $tableName;

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName = null)
    {
        $this->tableName = $tableName;
    }

    //Condition to make querie "where" in the search on API request
    private $condition;

    public function getCondition()
    {
        return $this->condition;
    }

    public function setCondition(string $condition = null)
    {
        $this->condition = $condition;
    }

   	/**
	* List Products, max per request is 250 results
	* @return json
	**/
    public function getProducts()
	{
        $GLOBALS['ACCESS'] = 'products/getInstances';

        $filter = [];

        if ($this->getOrderBy()) {
            $filter['order'] = 'codigo;'.$this->getOrderBy();
        }

        if ($this->getTableName() && $this->getOrderBy()) {
             $filter['order'] = $this->getTableName().';'.$this->getOrderBy();
        }

        if ($this->getLimit()) {
            $filter['limit'] = $this->getLimit();
        }

        if ($this->getOffset()) {
            $filter['offset'] = $this->getOffset();
        }

        if ($this->getCondition()) {
            $filter['condition'] = 'codigo = ' .$this->getCondition();
        }

        if ($this->getTableName() && $this->getCondition()) {

            $filter['condition'] = $this->getTableName() == 'codigo' || $this->getTableName() == 'codigo_alf'
            ?
                $this->getTableName().' = ' .$this->getCondition()
            :
                $this->getTableName().' LIKE "%' .$this->getCondition() .'%"';
        }

        return parent::processData($filter);
	}

    /**
    * Get a Product by is Code
    * @return json
    **/
    public function getProduct()
    {
        $GLOBALS['ACCESS'] = 'products/getInstance';

        return parent::processData(
            ['codigo' => $this->getCode()]);
    }

    /**
    * New Product Or Update
    * @return json
    **/
    public function setProduct()
    {
        $GLOBALS['ACCESS'] = 'products/saveInstances';

        $response = parent::processData([
            'codigo' => $this->getCode(),
            'codigo_alf' => $this->getCodeAlf(),
            'unidade' => $this->getUnit(),
            'descricaocurta' => $this->getShortDescription(),
            'familia' => $this->getFamily(),
            'categoria' => $this->getCategory(),
            'grupo' => $this->getGroup(),
            'isencao' => $this->getExemption(),
            'safttype' => $this->getSaftType(),
            'fornecedor' => $this->getSupplier(),
            'descricao' => $this->getDescription(),
            'iva' => $this->getVat(),
            'datacriacao' => $this->getCreatedAt(),
            'fundo' => $this->getBackground(),
            'retalho' => $this->getRetail(),
            'loja' => $this->getStore(),
            'letra' => $this->getLetter(),
            'subfam' => $this->getSubFamily(),
            'dataultcompra' => $this->getCreatedAt(),
            'vendersemstock' => $this->getIsSellWithoutStock(),
            'precovenda' => $this->getSalePrice()
        ]);

        return $response;
    }

    /**
    * Delete Product
    * @return json
    **/
    public function deleteProduct()
    {
        $GLOBALS['ACCESS'] = 'products/deleteInstances';

        $response = parent::processData([
            'codigo' => $this->getCode()
        ]);

        return $response;
    }

}