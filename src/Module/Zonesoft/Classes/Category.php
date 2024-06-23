<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to CRUD Category
 */

class Category extends Authentication{

	/** @const entity api url */
	const ENTITY = 'category';

    /** @const request type */
    const METHOD = 'POST';

	/** Category array data structure
	[
        'loja' => 1, // integer
        'codigo' => 100, // integer
        'descricao' => iva taxa x, // string
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

    private $store;

    public function getStore()
    {
        return $this->store;
    }

    public function setStore(int $store = 0)
    {
        $this->store = $store;
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

    private $subCategories;

    public function getSubCategories()
    {
        return $this->subCategories;
    }

    public function setSubCategories(string $subCategories = '')
    {
        $this->subCategories = $subCategories;
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
	* List Categories, max per request is 250 results
	* @return json
	**/
    public function getCategories()
	{
        $GLOBALS['ACCESS'] = 'categories/getInstances';

        return parent::processData([]);
	}

    /**
    * Get a Category by is Code
    * @return json
    **/
    public function getCategory()
    {
        $GLOBALS['ACCESS'] = 'categories/getInstance';

        return parent::processData(
            ['codigo' => $this->getCode()]);
    }

    /**
    * Update Category
    * @return json
    **/
    public function updateCategory()
    {
        $GLOBALS['ACCESS'] = 'categories/saveInstances';

        $response = parent::processData([
            'codigo' => $this->getCode(),
            'descricao' => $this->getDescription(),
            'lastupdate' => $this->getCreatedAt(),
            'loja' => $this->getStore(),
            'subcategories' => $this->getSubCategories(),
        ]);

        return $response->Response;
    }

    /**
    * New Category
    * @return json
    **/
    public function setCategory()
    {
        $GLOBALS['ACCESS'] = 'categories/saveInstances';

        $response = parent::processData([
            'datacriacao' => $this->getCreatedAt(),
            'lastupdate' => $this->getCreatedAt(),
            'codigo' => $this->getCode(),
            'descricao' => $this->getDescription(),
            'loja' => $this->getStore(),
            'subcategories' => $this->getSubCategories(),
        ]);

        return $response->Response;
    }

    /**
    * Delete Category
    * @return json
    **/
    public function deleteCategory()
    {
        $GLOBALS['ACCESS'] = 'categories/deleteInstances';
        $response = parent::processData([
            'codigo' => $this->getCode()
        ]);

        return $response->Response;
    }

}