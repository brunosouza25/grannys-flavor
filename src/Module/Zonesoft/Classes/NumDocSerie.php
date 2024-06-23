<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to List NumDocSerie
 */

class NumDocSerie extends Authentication{

	/** @const entity api url */
	const ENTITY = 'numdocserie';

    /** @const request type */
    const METHOD = 'POST';

    /** NumDocSerie array data structure
    [
        'doc' => 'FS', // string FS
        'store' => 1, // int the Store Id
        'serie' => 'FT2345234', // string required
        'numero' => 5 , // integer required
    ];
    */

    private $number;

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber(int $number = 0)
    {
        $this->number = $number;
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

    private $serie;

    public function getSerie()
    {
        return $this->serie;
    }

    public function setSerie(string $serie = '')
    {
        $this->serie = $serie;
    }

    private $doc;

    public function getDoc()
    {
        return $this->doc;
    }

    public function setDoc(string $doc = '')
    {
        $this->doc = $doc;
    }


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

   	/**
	* List NumDocSeries, max per request is 250 results
	* @return json
	**/
    public function getNumDocSeries()
	{
        $GLOBALS['ACCESS'] = 'numdocseries/getInstances';

        $filter = [];

        if ($this->getTableName() && $this->getOrderBy()) {
            $this->setOrderBy($this->getTableName().';'.$this->getOrderBy());
        }

        if ($this->getLimit()) {
            $filter['limit'] = $this->getLimit();
        }
        if ($this->getOrderBy()) {
             $filter['order'] = $this->getOrderBy();
        }
        if ($this->getOffset()) {
             $filter['offset'] = $this->getOffset();
        }

        return parent::processData($filter);
	}

    /**
    * Get a NumDocSerie by is Code
    * @return json
    **/
    public function getNumDocSerie()
    {
        $GLOBALS['ACCESS'] = 'numdocseries/getInstance';

        return parent::processData(
            ['codigo' => $this->getCode()]);
    }

    /**
    * New NumDocSerie
    * @return json
    **/
    public function setNumDocSerie()
    {
        $GLOBALS['ACCESS'] = 'numdocseries/saveInstances';

        $response = parent::processData([
            'doc' => $this->getDoc(),
            'loja' => $this->getStore(),
            'serie' => $this->getSerie(),
            'numero' => $this->getNumber()
        ]);

        return $response->Response;
    }



}