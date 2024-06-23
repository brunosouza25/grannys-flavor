<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to CRUD Invoice Documents
 */

class InvoiceDocuments extends Authentication{

	/** @const entity api url */
	const ENTITY = 'invoicedocument';

    /** @const request type */
    const METHOD = 'POST';

    /** InvoiceDocument array data structure
    [
        'doc' => 'FT', // string required, 'FT' for stores that operate in Portugal || 'VD' for stores that operate out Portugal;
        'pagamento' => 1,//integer, required, is paied
        'loja' => 1 //, integer, required, the Store ID
        'contribuinte' => 999999900, //string, required client Tax Number
        'cliente' => 0, // integer required the ID of the Client
        'fornecedor' => 1, //integer, required the Supplier ID
        'ivaincluido' => 1, // integer This field should always be 1.
        'delivery' => null || //, []
        'vendas' => [ // array required, the sales lines
            'codigo' => 46456, // integer, product ID
            'qtd' => 1.5, // float required, Product quantity
            'punit' => 2.36 // float required, Price for Unit of product
            'desconto' => 0.50 // float required, Discount in value
            'obs' => 'Some notes', // string
        ]
    ];
    */

    private $store;

    public function getStore()
    {
        return $this->store;
    }

    public function setStore(int $store = 0)
    {
        $this->store = $store;
    }

    private $taxNumber;

    public function getTaxNumber()
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(string $taxNumber = '999999990')
    {
        $this->taxNumber = $taxNumber ?? '999999990';
    }

    private $isPaid;

    public function getIsPaid()
    {
        return $this->isPaid;
    }

    public function setIsPaid(int $isPaid = 1)
    {
        $this->isPaid = $isPaid;
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

    private $client;

    public function getClient()
    {
        return $this->client;
    }

    public function setClient(int $client = 0)
    {
        $this->client = $client;
    }

    private $sales;

    public function getSales()
    {
        return $this->sales;
    }

    public function setSales(array $sales = [])
    {
        $this->sales = $sales;
    }

    public function hasSales()
    {
        $this->getSales();
    }

    private $vatIncluded;

    public function getVatIncluded()
    {
        return $this->vatIncluded;
    }

    public function setVatIncluded(int $vatIncluded = 1)
    {
        $this->vatIncluded = $vatIncluded;
    }

    private $delivery;

    public function getDelivery()
    {
        return $this->delivery;
    }

    public function setDelivery($delivery = null)
    {
        //Check if keys are set in the delivery
        if (is_array($delivery)) {
            if (array_key_exists('morada', $delivery) && array_key_exists('contacto', $delivery) && array_key_exists('dataentrega', $delivery)) {
                $this->delivery = $delivery;
            }
        }

        else
            $this->delivery = null;
    }

    private $documentType;

    public function getDocumentType()
    {
        return $this->documentType;
    }

    public function setDocumentType(string $documentType = 'FT')
    {
        $this->documentType = $documentType;
    }

    private $number;

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber(int $number = 0)
    {
        $this->number = $number;
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

    private $serie;

    public function getSerie()
    {
        return $this->serie;
    }

    public function setSerie(string $serie = '')
    {
        $this->serie = $serie;
    }

    private $descriptionToNullInvoice;

    public function getDescriptionToNullInvoice()
    {
        return $this->descriptionToNullInvoice;
    }

    public function setDescriptionToNullInvoice(string $descriptionToNullInvoice = '')
    {
        $this->descriptionToNullInvoice = $descriptionToNullInvoice;
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
	* List Invoicedocuments, max per request is 250 results
	* @return json
	**/
    public function getInvoiceDocuments()
	{
        $GLOBALS['ACCESS'] = 'invoicedocuments/getInstances';

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
    * Get a Invoice Documents by is Number
    * @return json
    **/
    public function getInvoiceDocument()
    {
        $GLOBALS['ACCESS'] = 'invoicedocuments/getInstance';

        return parent::processData(
            ['numero' => $this->getCode()]);
    }

    /**
    * New Invoice Document
    * @return json
    **/
    public function setInvoiceDocument()
    {
        $GLOBALS['ACCESS'] = 'invoicedocuments/saveInstance';

        $response = parent::processData([
            'doc' => $this->getDoc(),
            'cliente' => $this->getClient(),
            'contribuinte' => $this->getTaxNumber(),
            'pagamento' => $this->getIsPaid(),
            'fornecedor' => $this->getSupplier(),
            'loja' => $this->getStore(),
            'ivaincluido' => $this->getVatIncluded(),
            'delivery' => $this->getDelivery(),
            'vendas' => $this->getSales()
        ]);

        return $response->Response;
    }

    /**
    * Anull a InvoiceDocument
    * @return json
    **/
    public function setInvoiceDocumentAsNulled()
    {
        $GLOBALS['ACCESS'] = 'invoicedocuments/setInstanceAsNulled';
        $response = parent::processData([
            'doc' => $this->getDoc(),
            'serie' => $this->getSerie(),
            'numero' => $this->getNumber(),
            'descanulado' => $this->getDescriptionToNullInvoice()
        ]);

        return $response->Response;
    }


}