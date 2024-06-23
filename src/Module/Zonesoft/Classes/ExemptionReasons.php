<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to List ExemptionReasons
 */

class ExemptionReasons extends Authentication{

	/** @const entity api url */
	const ENTITY = 'exemptionreason';

    /** @const request type */
    const METHOD = 'POST';

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
	* List ExemptionReasons, max per request is 250 results
	* @return json
	**/
    public function getExemptionReasons()
	{
        $GLOBALS['ACCESS'] = 'exemptionreasons/getInstances';

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
    * Get a ExemptionReason by is Code
    * @return json
    **/
    public function getExemptionReason()
    {
        $GLOBALS['ACCESS'] = 'exemptionreasons/getInstance';

        return parent::processData(
            ['codigo' => $this->getCode()]);
    }

}