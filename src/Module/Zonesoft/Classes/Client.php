<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to CRUD Clients
 */

class Client extends Authentication{

	/** @const entity api url */
	const ENTITY = 'client';

    /** @const request type */
    const METHOD = 'POST';

	/** Client array data structure
	[
        'codigo' => 111, // integer used on update ?
        'nome' => 'Manuel Castro', // string required
        'bloqueado' => 0, // boolean required
        'contribuinte' => '822741852', // integer required
        'datacriacao' => datetime, // ("Y-m-d H:i:s")
        'telemovel' => '963852963', //string
        'email' => 'email@email.com', //string
        'morada' => 'Rua Aventura', //string
        'codpostal' => '0000-000', //string
        'isento' => 0, // boolean
        'motivo' => 'M1', // string exemptions reason
        'pais' => 'PT', string,
        'datanascimento' => '2020-01-01',
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

    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name = null)
    {
        $this->name = $name;
    }

    private $taxNumber;

    public function getTaxNumber()
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(string $taxNumber = null)
    {
        $this->taxNumber = $taxNumber;
    }

    private $isBlocked;

    public function getIsBlocked()
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(int $isBlocked = 0)
    {
        $this->isBlocked = $isBlocked;
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

    private $mobile;

    public function getMobile()
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile = null) {
        $this->mobile = $mobile;
    }

    private $email;

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email = null) {
        $this->email = $email;
    }

    private $address;

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress(string $address = null) {
        $this->address = $address;
    }

    private $birthDate;

    public function getBirthDate()
    {
        return $this->birthDate;
    }

    public function setBirthDate(string $birthDate = null) {
        $this->birthDate = $birthDate;
    }


    private $isExempt;

    public function getIsExempt()
    {
        return $this->isExempt;
    }

    public function setIsExempt(int $isExempt = 0) {
        $this->isExempt = $isExempt;
    }

    private $exemptionReason;

    public function getExemptionReason()
    {
        return $this->exemptionReason;
    }

    public function setExemptionReason(string $exemptionReason = null) {
        $this->exemptionReason = $exemptionReason;
    }

    private $city;

    public function getCity()
    {
        return $this->city;
    }

    public function setCity(string $city = '') {
        $this->city = $city;
    }

    private $alias;

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias(string $alias = '') {
        $this->alias = $alias;
    }

    private $postalCode;

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode = '0000-000') {
        $this->postalCode= $postalCode;
    }

    private $country;

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(string $country = 'PT') {
        $this->country = $country;
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
	* List Clients, max per request is 250 results
	* @return json
	**/
    public function getClients()
	{
        $GLOBALS['ACCESS'] = 'clients/getInstances';

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
    * Get a Client by Code
    * @return json
    **/
    public function getClient()
    {
        $GLOBALS['ACCESS'] = 'clients/getInstance';

        return parent::processData(
            ['codigo' => $this->getCode()]);
    }

    /**
    * Get a Client by TaxNumber
    * @return json
    **/
    public function getClientByTaxNumber()
    {
        $GLOBALS['ACCESS'] = 'clients/getInstances';
        $filter['limit'] = 1;
        $filter['condition'] = "contribuinte=".$this->getCondition();
        return parent::processData($filter);
    }


    /**
    * Get two Clients by TaxNumber
    * @return json
    **/
    public function getTwoClientsByTaxNumber()
    {
        $GLOBALS['ACCESS'] = 'clients/getInstances';
        $filter['limit'] = 2;
        $filter['condition'] = "contribuinte=".$this->getCondition();
        return parent::processData($filter);
    }


    /**
    * Update Client
    * @return json
    **/
    public function updateClient()
    {
        $GLOBALS['ACCESS'] = 'clients/saveInstances';

        $response = parent::processData([
            'codigo' => $this->getCode(),
            'bloqueado' => $this->getIsBlocked(),
            'contribuinte' => $this->getTaxNumber(),
            'nome' => $this->getName(),
            'telemovel' => $this->getMobile(),
            'email' => $this->getEmail(),
            'morada' => $this->getAddress(),
            'localidade' => $this->getCity(),
            //'nome_contacto' => $this->getAlias(),
            'codpostal' => $this->getPostalCode(),
            'isento' => $this->getIsExempt(),
            'motivo' => $this->getExemptionReason(),
            'pais' => $this->getCountry(),
            'datanascimento' => '2020-01-01'
        ]);

        return $response;
    }

    /**
    * New Client
    * @return json
    **/
    public function setClient()
    {
        $GLOBALS['ACCESS'] = 'clients/saveInstances';

        $response = parent::processData([
            'bloqueado' => $this->getIsBlocked(),
            'contribuinte' => $this->getTaxNumber(),
            'datacriacao' => $this->getCreatedAt(),
            'nome' => $this->getName(),
            'telemovel' => $this->getMobile(),
            'email' => $this->getEmail(),
            'morada' => $this->getAddress(),
            'codpostal' => $this->getPostalCode(),
            //'nome_contacto' => $this->getAlias(),
            'localidade' => $this->getCity(),
            'isento' => $this->getIsExempt(),
            'motivo' => $this->getExemptionReason(),
            'pais' => $this->getCountry(),
            'datanascimento' => '2020-01-01'
        ]);

        return $response;
    }

    /**
    * Delete Client
    * @return json
    **/
    public function deleteClient()
    {
        $GLOBALS['ACCESS'] = 'clients/deleteInstances';
        $response = parent::processData([
            'codigo' => $this->getCode()
        ]);

        return $response;
    }

}