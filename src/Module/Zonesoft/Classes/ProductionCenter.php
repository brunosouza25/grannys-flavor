<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to CRUD ProductionCenter
 */

class ProductionCenter extends Authentication{

    /** @const entity api url */
    const ENTITY = 'productioncentre';

    /** @const request type */
    const METHOD = 'POST';


    /**
     * List Taxes, max per request is 250 results
     * @return json
     **/
    public function getProductionCenter()
    {
        $GLOBALS['ACCESS'] = 'productioncentres/getInstances';

        return parent::processData([]);
    }

}