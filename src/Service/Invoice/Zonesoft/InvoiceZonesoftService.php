<?php

namespace App\Service\Invoice\Zonesoft;

use App\Module\Zonesoft\Classes\Options;
use App\Service\SettingsService;
use
Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface,
Symfony\Component\HttpFoundation\Session\SessionInterface,
App\Module\Invoice\Zonesoft,
App\Entity\Invoice\InvoiceCompanies,
App\Entity\Invoice\InvoiceCredentials,
App\Entity\Invoice\InvoiceSeries,
App\Module\Zonesoft\Classes\User,
App\Module\Zonesoft\Classes\Client,
App\Module\Zonesoft\Classes\Tax,
App\Module\Zonesoft\Classes\Product,
App\Module\Zonesoft\Classes\ProductStock,
App\Module\Zonesoft\Classes\Category,
App\Module\Zonesoft\Classes\Supplier,
App\Module\Zonesoft\Classes\Group,
App\Module\Zonesoft\Classes\ProductionCenter,
App\Module\Zonesoft\Classes\Family,
App\Module\Zonesoft\Classes\SubFamily,
App\Module\Zonesoft\Classes\Unit,
App\Module\Zonesoft\Classes\InvoiceDocuments,
App\Module\Zonesoft\Classes\NumDocSerie,
App\Module\Zonesoft\Classes\ExemptionReasons,
Doctrine\ORM\EntityManagerInterface,
Symfony\Component\Security\Core\Security;

class InvoiceZonesoftService
{
	public $credentials;
	public $invoiceCompanies;
	private $session;
	private $em;

    public function __construct(ParameterBagInterface $environment, SessionInterface $session, EntityManagerInterface $em, Security $security)
    {

            $zonedata = $em->getRepository(\App\Entity\Zonesoftdata::class)->find(1);


            $this->session = $session;
            $this->credentials['tin'] = $zonedata->getNif(); // Tin a.k.a. fiscal number, that allows access to Zonesoft (login page)
            $this->credentials['name'] = $zonedata->getUsername(); // Name, that allows access to Zonesoft (login page)
            $this->credentials['password'] = $zonedata->getPassword(); // Password, that allows access to Zonesoft (login page)
            $this->credentials['store'] = $zonedata->getStore(); // Store, the store id, provided by Zonesoft


//
//        $this->credentials['tin'] = '5017﻿05287'; // Tin a.k.a. fiscal number, that allows access to Zonesoft (login page)
//        $this->credentials['name'] = 'Admin'; // Name, that allows access to Zonesoft (login page)
//        $this->credentials['password'] = 'ITBsa7777'; // Password, that allows access to Zonesoft (login page)
//        $this->credentials['store'] = '1'; // Store, the store id, provided by Zonesoft


        //Get the request API path according the enviroment
            $this->credentials['url'] = $environment->get("kernel.environment") == 'prod'
                ?
                'https://api.zonesoft.org/v2.9/'
                :
                'https://api.zonesoft.org/v2.9/';

            $this->credentials['token']['auth_token'] = $this->session->get('zs_auth_token');
            $this->credentials['token']['expires_in'] = $this->session->get('zs_expires_in');
            //$this->credentials['domains'] = $em->getRepository(InvoiceSeries::class)->findBy(['invoiceCompanies' => $this->invoiceCompanies]) ?? null;
        }


	/**
	* Create a new access token or use the existing one
	* @return boolean
	**/
	public function start() {
		if (empty($this->credentials))
			return null;

		$now = new \DateTime();

		//Access token expired or not
		if ($this->credentials['token']['auth_token'] && (int)$this->credentials['token']['expires_in'] > (int)$now->format('U'))
			return true;

		$token = $this->authHash();

		if (!$token = $this->authHash())
			return null;

		$this->session->set('zs_expires_in', ($now->format('U') + 450));
   		$this->session->set('zs_auth_token', $token);
   		$this->credentials['token']['auth_token'] = $this->session->get('zs_auth_token');
    	$this->credentials['token']['expires_in'] = $this->session->get('zs_expires_in');

		return true;
	}

	private function authHash() {
		$auth = new User();
		$auth->setTin($this->credentials['tin']);
		$auth->setPassword($this->credentials['password']);
		$auth->setName($this->credentials['name']);
		$auth->setStore($this->credentials['store']);
		$auth->setUrl($this->credentials['url']);
		$token = $auth->getAuthHash();

   		return $token;
	}

	#####
	## CLIENT METHODS
	#####

	/**
	 *
	 * @param      int  $limit    The limit
	 * @param      int  $offset
	 * @param      string  $orderBy  The order by
	 * @param      string  $tableName the table to get the orderBy
	 *
	 * @return     mixed    Clients[] || bool
	 */
	public function getClients(int $limit = 0, int $offset = 0, string $orderBy = null, string $tableName = null, string $condition = null)
	{
		if ($this->start()) {
			$c = new Client();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			if ($limit > 0) {
				$c->setLimit($limit);
			}
			if (strtoupper($orderBy) === 'ASC' || strtoupper($orderBy) === 'DESC') {
				$c->setOrderBy($orderBy);
			}
			if ($offset > 0) {
				$c->setOffset($offset);
			}
			if ($tableName) {
				$c->setTableName($tableName);
			}
			if ($condition) {
				$c->setcondition($condition);
			}

			return $c->getClients();
		}
		return false;
	}


	/**
	 * Get Client by Code
 	 * @param      int  $code    Code Id
	 *
	 * @return     mixed    Client || bool
	 */
	public function getClient(int $code = 0)
	{
		if ($this->start()) {
			$c = new Client();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->getClient();
		}
		return false;
	}

	/**
	 * Get Client by Tax Number
 	 * @param      int  $taxNumber    tax number
	 *
	 * @return     mixed    Client || bool
	 */
	public function getClientByTaxNumber(string $taxNumber = '999999990')
	{
		if ($this->start()) {
			$c = new Client();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCondition($taxNumber);

			return $c->getClientByTaxNumber();
		}
		return false;
	}


	/**
	 * Get 2 clients with the same tax number Client by Tax Number
 	 * @param      int  $taxNumber    tax number
	 *
	 * @return     mixed    Client || bool
	 */
	public function getTwoClientsByTaxNumber(string $taxNumber = '999999990')
	{
		if ($this->start()) {
			$c = new Client();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCondition($taxNumber);

			return $c->getTwoClientsByTaxNumber();
		}
		return false;
	}


	/**
	 * Sets the Client
	 * @param      array  $arClientData  The client data
	 * @return     mixed    Client || bool
	 */
	public function setClient(array $arClientData = [])
	{
		if ($this->start()) {
			$c = new Client();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
            $c->setCreatedAt();
			$c->setIsBlocked($arClientData['isBlocked'] ?? 0);
            $c->setTaxNumber($arClientData['taxNumber'] ?? '999999990');
            $c->setName($arClientData['name'] ?? 'Consumidor Final');
            $c->setMobile($arClientData['mobile'] ?? '');
            $c->setEmail($arClientData['email']) ?? '';
            $c->setAddress($arClientData['address'] ?? '');
            $c->setAlias($arClientData['alias'] ?? '');
            $c->setPostalCode($arClientData['postalCode'] ?? '0000-000');
            $c->setCity($arClientData['city'] ?? '');
            $c->setIsExempt($arClientData['isExempt'] ?? 0);
            $c->setExemptionReason($arClientData['exemptionReason'] ?? '');
            $c->setCountry($arClientData['country'] ?? 'PT');
			return $c->setClient();
		}
		return false;
	}

	/**
	 * Sets the Client
	 * @param      array  $arClientData  The client data
	 * @return     mixed    Client || bool
	 */
	public function updateClient(array $arClientData = [])
	{
		if ($this->start()) {
			$c = new Client();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($arClientData['code']);
			$c->setIsBlocked($arClientData['isBlocked'] ?? 0);
            $c->setTaxNumber($arClientData['taxNumber'] ?? '999999990');
            $c->setName($arClientData['name'] ?? 'Consumidor Final');
            $c->setMobile($arClientData['mobile'] ?? '');
            $c->setAlias($arClientData['alias'] ?? 'dfdg');
            $c->setEmail($arClientData['email']) ?? '';
            $c->setAddress($arClientData['address'] ?? 'Desconhecida');
            $c->setPostalCode($arClientData['postalCode'] ?? '0000-000');
            $c->setIsExempt($arClientData['isExempt'] ?? 0);
            $c->setCity($arClientData['city'] ?? 'Desconhecida');
            $c->setExemptionReason($arClientData['exemptionReason'] ?? '');
            $c->setCountry($arClientData['country'] ?? 'PT');
			return $c->updateClient();
		}
		return false;
	}

	/**
	 * Delete Client
	 * @param      int  $code    Code Id
	 * @return     mixed    Client || bool
	 */
	public function deleteClient(int $code = 0)
	{
		if ($this->start()) {
			$c = new Client();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->deleteClient();
		}
		return false;
	}

	#####
	## TAX METHODS
	#####

	/**
	* List Taxes, max per request is 250 results
	 * @return     mixed    Clients[] || bool
	 */
	public function getTaxes()
	{
		if ($this->start()) {
			$c = new Tax();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			return $c->getTaxes();
		}
		return false;
	}



    /**
     * List Taxes, max per request is 250 results
     * @return     mixed    Clients[] || bool
     */
    public function getProductionCenter()
    {
        if ($this->start()) {
            $c = new ProductionCenter();
            $c->setToken($this->credentials['token']['auth_token']);
            $c->setUrl($this->credentials['url']);

            return $c->getProductionCenter();
        }
        return false;
    }

    /**
     * List Taxes, max per request is 250 results
     * @return     mixed    Clients[] || bool
     */
    public function getOptions()
    {
        if ($this->start()) {
            $c = new Options();
            $c->setToken($this->credentials['token']['auth_token']);
            $c->setUrl($this->credentials['url']);

            return $c->getOptions();
        }
        return false;
    }



    /**
	 * Get Tax by Code
 	 * @param      int  $code    Code Id
	 *
	 * @return     mixed    Client || bool
	 */
	public function getTax(int $code = 0)
	{
		if ($this->start()) {
			$c = new Tax();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->getTax();
		}
		return false;
	}

	/**
	 * Sets the Tax
	 * @param      array  $arTaxData  The Tax data
	 * @return     mixed    Tax || bool
	 */
	public function setTax(array $arTaxData = [])
	{
		if ($this->start()) {
			$c = new Tax();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setIsActive($arTaxData['isActive']);
			$c->setDescription($arTaxData['description']);
			$c->setFactor($arTaxData['factor']);
			$c->setCreatedAt();

			return $c->setTax();
		}
		return false;
	}

	/**
	 * Update the Tax
	 * @param      array  $arTaxData  The Tax data
	 * @return     mixed    Client || bool
	 */
	public function updateTax(array $arTaxData = [])
	{
		if ($this->start()) {
			$c = new Tax();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($arClientData['code']);
			$c->setIsActive($arTaxData['isActive']);
			$c->setDescription($arTaxData['description']);
			$c->setFactor($arTaxData['factor']);

			return $c->updateTax();
		}
		return false;
	}

	/**
	 * Delete Tax
	 * @param      int  $code    Code Id
	 * @return     mixed    Client || bool
	 */
	public function deleteTax(int $code = 0)
	{
		if ($this->start()) {
			$c = new Tax();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->deleteTax();
		}
		return false;
	}


	#####
	## PRODUCTS METHODS
	#####

	/**
	 *
	 * @param      int  $limit    The limit
	 * @param      int  $offset
	 * @param      string  $orderBy  The order by
	 * @param      string  $tableName the table to get the orderBy
	 *
	 * @return     mixed    Products[] || bool
	 */
	public function getProducts(int $limit = 0, int $offset = 0, string $orderBy = null, string $tableName = null, string $condition = null)
	{
		if ($this->start()) {
			$c = new Product();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			if ($limit > 0) {
				$c->setLimit($limit);
			}
			if (strtoupper($orderBy) === 'ASC' || strtoupper($orderBy) === 'DESC') {
				$c->setOrderBy($orderBy);
			}
			if ($offset > 0) {
				$c->setOffset($offset);
			}
			if ($tableName) {
				$c->setTableName($tableName);
			}
			if ($condition) {
				$c->setcondition($condition);
			}

			return $c->getProducts();
		}
		return false;
	}




	/**
	 * Get Product by Code
 	 * @param      int  $code    Code Id
	 *
	 * @return     mixed    Product || bool
	 */
	public function getProduct(int $code = 0)
	{
		if ($this->start()) {
			$c = new Product();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->getProduct();
		}
		return false;
	}

	/**
	 * Sets/Updates the Product
	 * @param      array  $arProductData  The Product data
	 * @return     mixed    Product || bool
	 */
	public function setProduct(array $arProductData = [])
	{
		if ($this->start()) {
			$c = new Product();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setStore($this->credentials['store']);
            $c->setCreatedAt();

            $c->setCode($arProductData['code']);

            $c->setVat($arProductData['vat'] ?? 0);
            $c->setSaftType($arProductData['saftType'] ?? 0);
     		$c->setCodeAlf($arProductData['codeAlf'] ?? 0);
            $c->setUnit($arProductData['unit'] ?? 0);
            $c->setshortDescription($arProductData['shortDescription'] ?? '');
            $c->setFamily($arProductData['family'] ?? 0);
            $c->setCategory($arProductData['category'] ?? 0);
            $c->setGroup($arProductData['group'] ?? 0);
            $c->setExemption($arProductData['exemption'] ?? 'P');
            $c->setSupplier($arProductData['supplier'] ?? 0);
            $c->setDescription($arProductData['description'] ?? 'Descrição');//Required
            $c->setBackground($arProductData['background'] ?? '');
            $c->setLetter($arProductData['letter'] ?? '');
 			$c->setRetail($arProductData['retail'] ?? 0);
 			$c->setSubFamily($arProductData['subFamily'] ?? 0);
 			$c->setIsSellWithoutStock($arProductData['isSellWithoutStock'] ?? 0);
			$c->setSalePrice($arProductData['price'] ?? 0);

			return $c->setProduct();

		}
		return false;
	}

	/**
	 * Delete Product
	 * @param      int  $code    Code Id
	 * @return     mixed    Product || bool
	 */
	public function deleteProduct(int $code = 0)
	{
		if ($this->start()) {
			$c = new Product();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->deleteProduct();
		}
		return false;
	}



	#####
	## PRODUCT_STOCK METHOD
	#####

	/**
	* Get Stock of a Product by Id
	 * @return     mixed    Categories[] || bool
	 */
	public function getProductStock(int $productId)
	{
		if ($this->start()) {
			$c = new ProductStock();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setProduct($productId);
			$c->setStore(1);
			$c->setWareHouse(0);

			return $c->getProductStock();
		}
		return false;
	}



	#####
	## CATEGORY METHODS
	#####

	/**
	* List Categories max per request is 250 results
	 * @return     mixed    Categories[] || bool
	 */
	public function getCategories()
	{
		if ($this->start()) {
			$c = new Category();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			return $c->getCategories();
		}
		return false;
	}

	/**
	 * Get Category by Code
 	 * @param      int  $code    Code Id
	 *
	 * @return     mixed    Category || bool
	 */
	public function getCategory(int $code = 0)
	{
		if ($this->start()) {
			$c = new Category();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->getCategory();
		}
		return false;
	}

	/**
	 * Sets the Category
	 * @param      array  $arCategoryData  The Category data
	 * @return     mixed    Tax || bool
	 */
	public function setCategory(array $arCategoryData = [])
	{
		$code = \DateTime::createFromFormat('Y-m-d H:i:s', '2021-01-01 00:00:00', new \DateTimeZone($_ENV['TIME_ZONE']));
		$now = new \DateTime('now', new \DateTimeZone($_ENV['TIME_ZONE']));

		if ($this->start()) {
			$c = new Category();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($now->format('U')-$code->format('U'));
			$c->setStore($this->credentials['store']);
			$c->setSubCategories($arCategoryData['subCategories']);
			$c->setDescription($arCategoryData['description']);
			$c->setCreatedAt();

			return $c->setCategory();
		}
		return false;
	}

	/**
	 * Update the Category
	 * @param      array  $arCategoryData  The Category data
	 * @return     mixed    Category || bool
	 */
	public function updateCategory(array $arCategoryData = [])
	{
		if ($this->start()) {
			$c = new Category();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($arCategoryData['code']);
			$c->setStore($this->credentials['store']);
			$c->setSubCategories($arCategoryData['subCategories']);
			$c->setDescription($arCategoryData['description']);
			$c->setCreatedAt();

			return $c->updateCategory();
		}
		return false;
	}

	/**
	 * Delete Category
	 * @param      int  $code    Code Id
	 * @return     mixed    Category || bool
	 */
	public function deleteCategory(int $code = 0)
	{
		if ($this->start()) {
			$c = new Category();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->deleteCategory();
		}
		return false;
	}

	#####
	## SUPPLIER METHODS
	#####

	/**
	* List Suppliers max per request is 250 results
	 * @return     mixed    Suppliers[] || bool
	 */
	public function getSuppliers()
	{
		if ($this->start()) {
			$c = new Supplier();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			return $c->getSuppliers();
		}
		return false;
	}

	/**
	 * Get Supplier by Code
 	 * @param      int  $code    Code Id
	 *
	 * @return     mixed    Supplier || bool
	 */
	public function getSupplier(int $code = 0)
	{
		if ($this->start()) {
			$c = new Supplier();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->getSupplier();
		}
		return false;
	}

	#####
	## GROUP METHODS
	#####

	/**
	* List Groups max per request is 250 results
	 * @return     mixed    Group[] || bool
	 */
	public function getGroups()
	{
		if ($this->start()) {
			$c = new Group();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			return $c->getGroups();
		}
		return false;
	}

	/**
	 * Get Group by Code
 	 * @param      int  $code    Code Id
	 *
	 * @return     mixed    Group || bool
	 */
	public function getGroup(int $code = 0)
	{
		if ($this->start()) {
			$c = new Group();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->getGroup();
		}
		return false;
	}

	#####
	## FAMILY METHODS
	#####

	/**
	* List Families max per request is 250 results
	 * @return     mixed    Family[] || bool
	 */
	public function getFamilies(int $limit = 0, int $offset = 0, string $orderBy = null, string $tableName = null, string $condition = null)
	{
		if ($this->start()) {
			$c = new Family();
            $c->setStore($this->credentials['store']);
            $c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			if ($limit > 0) {
				$c->setLimit($limit);
			}
			if (strtoupper($orderBy) === 'ASC' || strtoupper($orderBy) === 'DESC') {
				$c->setOrderBy($orderBy);
			}
			if ($offset > 0) {
				$c->setOffset($offset);
			}
			if ($tableName) {
				$c->setTableName($tableName);
			}
			if ($condition) {
				$c->setcondition($condition);
			}

			return $c->getFamilies();
		}
		return false;
	}

	/**
	 * Get Family by Code
 	 * @param      int  $code    Code Id
	 *
	 * @return     mixed    Family || bool
	 */
	public function getFamily(int $code = 0)
	{
		if ($this->start()) {
			$c = new Family();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->getFamily();
		}
		return false;
	}

	/**
	 * Set the Family create or update
	 * @param      array  $arFamilyData  The Family data
	 * @return     mixed    Family || bool
	 */
	public function setFamily(array $arFamilyData = [])
	{
		if ($this->start()) {
			$c = new Family();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setStore($this->credentials['store']);
			$c->setCode($arFamilyData['code']);
			$c->setDescription($arFamilyData['description']);
			($arFamilyData['descriptionShop'] ?? null) ? $c->setDescriptionShop($arFamilyData['descriptionShop']) : false;

			return $c->setFamily();
		}
		return false;
	}

	/**
	 * Delete Family
	 * @param      int  $code    Code Id
	 * @return     mixed    Family || bool
	 */
	public function deleteFamily(int $code = 0)
	{
		if ($this->start()) {
			$c = new Family();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->deleteFamily();
		}
		return false;
	}

	#####
	## SUBFAMILY METHODS
	#####

	/**
	* List SubFamilies max per request is 250 results
	 * @return     mixed    Family[] || bool
	 */
	public function getSubFamilies(int $limit = 0, int $offset = 0, string $orderBy = null, string $tableName = null, string $condition = null)
	{
		if ($this->start()) {
			$c = new SubFamily();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			if ($limit > 0) {
				$c->setLimit($limit);
			}
			if (strtoupper($orderBy) === 'ASC' || strtoupper($orderBy) === 'DESC') {
				$c->setOrderBy($orderBy);
			}
			if ($offset > 0) {
				$c->setOffset($offset);
			}
			if ($tableName) {
				$c->setTableName($tableName);
			}
			if ($condition) {
				$c->setcondition($condition);
			}
			return $c->getSubFamilies();
		}

		return false;
	}

	/**
	 * Get SubFamily by Code
 	 * @param      int  $code    Code Id
	 *
	 * @return     mixed    Family || bool
	 */
	public function getSubFamily(int $code = 0)
	{
		if ($this->start()) {
			$c = new SubFamily();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->getSubFamily();
		}
		return false;
	}

	/**
	 * Set the SubFamily create or update
	 * @param      array  $arSubFamilyData  The SubFamily data
	 * @return     mixed    SubFamily || bool
	 */
	public function setSubFamily(array $arSubFamilyData = [])
	{
		if ($this->start()) {
			$c = new SubFamily();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setStore($this->credentials['store']);
			$c->setCode($arSubFamilyData['code']);
			$c->setDescription($arSubFamilyData['description']);
			($arSubFamilyData['descriptionShop'] ?? null) ? $c->setDescriptionShop($arSubFamilyData['descriptionShop']) : false;
			$c->setFamily($arSubFamilyData['familyId']);

			return $c->setSubFamily();
		}
		return false;
	}

	/**
	 * Delete SubFamily
	 * @param      int  $code    Code Id
	 * @return     mixed    SubFamily || bool
	 */
	public function deleteSubFamily(int $code = 0)
	{
		if ($this->start()) {
			$c = new SubFamily();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->deleteSubFamily();
		}
		return false;
	}

	#####
	## UNIT METHODS
	#####

	/**
	* List Units max per request is 250 results
	 * @return     mixed    Unit[] || bool
	 */
	public function getUnits()
	{
		if ($this->start()) {
			$c = new Unit();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			return $c->getUnits();
		}
		return false;
	}

	/**
	 * Get Unit by Code
 	 * @param      int  $code    Code Id
	 *
	 * @return     mixed    Unit || bool
	 */
	public function getUnit(int $code = 0)
	{
		if ($this->start()) {
			$c = new Unit();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->getUnit();
		}
		return false;
	}

	#####
	## EXEMPTIONREASON METHODS
	#####

	/**
	* List ExemptionReasons max per request is 250 results
	 * @return     mixed    ExemptionReason[] || bool
	 */
	public function getExemptionReasons()
	{
		if ($this->start()) {
			$c = new ExemptionReasons();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			return $c->getExemptionReasons();
		}
		return false;
	}

	/**
	 * Get ExemptionReason by Code
 	 * @param      int  $code    Code Id
	 *
	 * @return     mixed    Unit || bool
	 */
	public function getExemptionReason(int $code = 0)
	{
		if ($this->start()) {
			$c = new ExemptionReasons();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setCode($code);

			return $c->getExemptionReason();
		}
		return false;
	}

	#####
	## INVOICE DOCUMENTS METHODS
	#####

	/**
	* List InvoiceDocuments max per request is 250 results
	 * @return     mixed    InvoiceDocuments[] || bool
	 */
	public function getInvoiceDocuments()
	{
		if ($this->start()) {
			$c = new InvoiceDocuments();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			return $c->getInvoiceDocuments();
		}
		return false;
	}

	/**
	 * Get InvoiceDocuments by Number
 	 * @param      int  $number    Invoice Id
	 *
	 * @return     mixed    InvoiceDocuments || bool
	 */
	public function getInvoiceDocument(int $number = 0)
	{
		if ($this->start()) {
			$c = new InvoiceDocuments();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setNumber($number);

			return $c->getInvoiceDocument();
		}
		return false;
	}

	/**
	 * Sets the InvoiceDocument
	 * @param      array  $arInvoiceData  The Invoice Ducument data
	 * @return     mixed    InvoiceDocument|| bool
	 */
	public function setInvoiceDocument(array $arInvoiceData = [])
	{
		if ($this->start()) {
			$c = new InvoiceDocuments();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setStore($this->credentials['store']);
			$c->setDoc($arInvoiceData['doc'] ?? 'FT');
			$c->setClient($arInvoiceData['client'] ?? 0);
			$c->setTaxNumber($arInvoiceData['taxNumber'] ?? '999999990');
			$c->setIsPaid($arInvoiceData['isPaid'] ?? 1);
			$c->setSupplier($arInvoiceData['supplier'] ?? 1);
			$c->setVatIncluded($arInvoiceData['vatIncluded'] ?? 1);
			$c->setDelivery($arInvoiceData['delivery'] ?? null);
			$c->setSales($arInvoiceData['sales'] ?? []);

			return $c->setInvoiceDocument();
		}
		return false;
	}

	/**
	 * Anull a InvoiceDocument
	 * @param      array  $arInvoiceData  The Invoice Document data
	 * @return     mixed    InvoiceDocument|| bool
	 */
	public function setInvoiceDocumentAsNulled(array $arInvoiceData = [])
	{
		if ($this->start()) {
			$c = new InvoiceDocuments();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setDoc($arInvoiceData['doc']);
			$c->setSerie($arInvoiceData['serie']);
			$c->setNumber($arInvoiceData['number']);
			$c->setDescriptionToNullInvoice($arInvoiceData['descriptionToNullInvoice']);

			return $c->setInvoiceDocumentAsNulled();
		}
		return false;
	}

	#####
	## NUMDOC SERIES METHODS
	#####

	/**
	* List NumDocSerie max per request is 250 results
	 * @return     mixed    NumDocSerie[] || bool
	 */
	public function getNumDocSeries()
	{
		if ($this->start()) {
			$c = new NumDocSerie();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);

			return $c->getNumDocSeries();
		}
		return false;
	}

	/**
	 * Get NumDocSerie by Number
 	 * @param      int  $number    NumDocSerie Id
	 *
	 * @return     mixed    NumDocSerie || bool
	 */
	public function getNumDocSerie(int $number = 0)
	{
		if ($this->start()) {
			$c = new NumDocSerie();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setNumber($number);

			return $c->getNumDocSerie();
		}
		return false;
	}

	/**
	 * Sets the NumDocSerie
	 * @param      array  $arNumDocSerie Data  The NumDocSerie data
	 * @return     mixed    NumDocSerie || bool
	 */
	public function setNumDocSerie(array $arNumDocSerieData = [])
	{
		if ($this->start()) {
			$c = new NumDocSerie();
			$c->setToken($this->credentials['token']['auth_token']);
			$c->setUrl($this->credentials['url']);
			$c->setStore($this->credentials['store']);
			$c->setDoc($arNumDocSerieData['doc'] ?? 'FS');
			$c->setSerie($arNumDocSerieData['serie'] ?? 'NW1234');
			$c->setNumber($arNumDocSerieData['number'] ?? 2);

			return $c->setNumDocSerie();
		}
		return false;
	}

    /**
     * @param $products array com os ids dos produtos [1,2,5,9...]
     */
    public function getProductStockIds($products)
    {
        $newArray = [];
        foreach ($products as $product) {
            $newArray[] = ['produto' => $product, 'loja' => $this->getStore()];
        }

        $auth = $this->getToken();

        $data = [
            "auth_hash" => $auth,
            "productstock" => $newArray
        ];

        $url = 'https://api.zonesoft.org/v2.9/productstocks/getCurrentStockInstances';

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $response = curl_exec($curl);

        curl_close($curl);
        $stockProducts = json_decode($response, true);

        return $stockProducts;
    }

    public function getToken()
    {
        $data = [
            "user" => ["nif" => $this->credentials['tin'], "nome" => $this->credentials['name'], "password" => $this->credentials['password'], "loja" => $this->credentials['store']]
        ];

        $url = 'https://api.zonesoft.org/v2.9/auth/authenticate';

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $response = curl_exec($curl);

        curl_close($curl);

        $responseData = json_decode($response, true);

        $token = $responseData['Response']['Content']['auth_hash'];
        return $token;
    }

    public function getStore()
    {
        return $this->credentials['store'];
    }

}
