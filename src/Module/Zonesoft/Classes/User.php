<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
* A class to get User Auth
*
* User array data structure
    [
        'nif' => 123123456, // string required
        'nome' => admin, // string required
        'password' => 'password', // string required
        'loja' => 1, // integer required
    ];
*/

class User extends Authentication{

	/** @const entity api url */
	const ENTITY = 'user';

    /** @const folder path to api url */
    const ACCESS = 'auth/authenticate';

    /** @const request type */
    const METHOD = 'POST';

    //string required fiscal number, used in https://zsbmsv2.zonesoft.org/#!/login
    private $tin;

    public function getTin()
    {
        return $this->tin;
    }

    public function setTin(string $tin = null)
    {
        $this->tin = $tin;
    }

    //string required name, used in https://zsbmsv2.zonesoft.org/#!/login
    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name = null)
    {
        $this->name = $name;
    }

    //string required password, used in https://zsbmsv2.zonesoft.org/#!/login
    private $password;

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(string $password = null)
    {
        $this->password = $password;
    }

    //int required store, delivered by Zonesoft
    private $store;

    public function getStore()
    {
        return $this->store;
    }

    public function setStore(int $store = 0)
    {
        $this->store = $store;
    }

	/**
	* Get Zonesoft Authentication Hash
	* @return json
	**/
    public function getAuthHash()
	{
		return parent::authHash([
            'nif' => $this->getTin(),
            'nome' => $this->getName(),
            'password' => $this->getPassword(),
            'loja' => $this->getStore()
        ]);
	}
}