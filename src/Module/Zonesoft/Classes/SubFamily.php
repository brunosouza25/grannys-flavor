<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to List SubFamilies
 */

class SubFamily extends Authentication{

	/** @const entity api url */
	const ENTITY = 'subfamily';

    /** @const request type */
    const METHOD = 'POST';

    /** SubFamily array data structure
    [
        'codigo' => 9,
        'descricao' => 'Adulto',
        'descricao_loja' => 'Adulto',
        'familia' => 46, // integer Required
        'fundo' => '#808080',
        'letra' => '#ffffff',
        'loja' => 1,
        'posicao' => 1,
        'subfamposicaoprint' => 0,
        'subfamzonas' => [
            'loja' => 1,
            'subfamilia' => 9,
            'zona' => 0
        ]
    ];
    */

    //Code id integer
    private $code;

    public function getCode()
    {
        return $this->code;
    }

    public function setCode(int $code = 0)
    {
        $this->code = $code;
    }


    //description string
    private $descriptionShop;

    public function getDescriptionShop()
    {
        return $this->descriptionShop;
    }

    public function setDescriptionShop(string $descriptionShop = '')
    {
        $this->descriptionShop = $descriptionShop;
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

    //description string
    private $description;

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(string $description = '')
    {
        $this->description = $description;
    }

    //family string
    private $family;

    public function getFamily()
    {
        return $this->family;
    }

    public function setFamily(int $family = 0)
    {
        $this->family = $family;
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
	* List SubFamilies, max per request is 250 results
	* @return json
	**/
    public function getSubFamilies()
	{
        $GLOBALS['ACCESS'] = 'subfamilies/getInstances';

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

            $filter['condition'] = $this->getTableName() == 'codigo'
            ?
                $this->getTableName().' = ' .$this->getCondition()
            :
                $this->getTableName().' LIKE "%' .$this->getCondition() .'%"';
        }
        return parent::processData($filter);
	}

    /**
    * Get a SubFamily by is Code
    * @return json
    **/
    public function getSubFamily()
    {
        $GLOBALS['ACCESS'] = 'subfamilies/getInstance';

        return parent::processData(
            ['codigo' => $this->getCode()]);
    }

    /**
    * New SubFamily Or Update
    * @return json
    **/
    public function setSubFamily()
    {
        $GLOBALS['ACCESS'] = 'subfamilies/saveInstances';

        $response = parent::processData([
            'codigo' => $this->getCode(),
            'descricao' => $this->getDescription(),
            'descricao_loja' => $this->getDescriptionShop(),
            'familia' => $this->getFamily()
        ]);

        return $response;
    }

    /**
    * Delete SubFamily
    * @return json
    **/
    public function deleteSubFamily()
    {
        $GLOBALS['ACCESS'] = 'subfamilies/deleteInstances';

        $response = parent::processData([
            'codigo' => $this->getCode()
        ]);

        return $response;
    }


}