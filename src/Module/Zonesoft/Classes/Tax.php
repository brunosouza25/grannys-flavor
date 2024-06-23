<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to CRUD Tax
 */

class Tax extends Authentication{

	/** @const entity api url */
	const ENTITY = 'tax';

    /** @const request type */
    const METHOD = 'POST';

	/** Tax array data structure
	[
        'activa' => 1, // boolean
        'codigo' => 100, // integer
        'descricao' => iva taxa x, // string
        'factor' => 21, // integer required
        'datacriacao' => datetime, // ("Y-m-d H:i:s")
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

    private $description;

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(string $description = null)
    {
        $this->description = $description;
    }

    private $isActive;

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive(int $isActive = 0)
    {
        $this->isActive = $isActive;
    }

    private $factor;

    public function getFactor()
    {
        return $this->factor;
    }

    public function setFactor(int $factor = 0)
    {
        $this->factor = $factor;
    }

    private $createdAt;

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt()
    {
        $now = new \DateTime('now', new \DateTimeZone($_ENV['TIME_ZONE']));
        $this->createdAt = $now->format('Y-m-d H:i:s');
    }

   	/**
	* List Taxes, max per request is 250 results
	* @return json
	**/
    public function getTaxes()
	{
        $GLOBALS['ACCESS'] = 'taxes/getInstances';

        return parent::processData([]);
	}

    /**
    * Get a Tax by is Code
    * @return json
    **/
    public function getTax()
    {
        $GLOBALS['ACCESS'] = 'taxes/getInstance';

        return parent::processData(
            ['codigo' => $this->getCode()]);
    }

    /**
    * Update Tax
    * @return json
    **/
    public function updateTax()
    {
        $GLOBALS['ACCESS'] = 'taxes/saveInstances';

        $response = parent::processData([
            'codigo' => $this->getCode(),
            'activa' => $this->getIsActive(),
            'descricao' => $this->getDescription(),
            'factor' => $this->getFactor(),
        ]);

        return $response->Response;
    }

    /**
    * New Tax
    * @return json
    **/
    public function setTax()
    {
        $GLOBALS['ACCESS'] = 'taxes/saveInstances';

        $response = parent::processData([
            'activa' => $this->getIsActive(),
            'datacriacao' => $this->getCreatedAt(),
            'descricao' => $this->getDescription(),
            'factor' => $this->getFactor(),
        ]);

        return $response->Response;
    }

    /**
    * Delete Tax
    * @return json
    **/
    public function deleteTax()
    {
        $GLOBALS['ACCESS'] = 'taxes/deleteInstances';
        $response = parent::processData([
            'codigo' => $this->getCode()
        ]);

        return $response->Response;
    }

}