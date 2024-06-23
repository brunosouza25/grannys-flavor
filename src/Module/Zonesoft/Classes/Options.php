<?php

namespace App\Module\Zonesoft\Classes;

use App\Module\Zonesoft\Authentication;

/**
 * Class to CRUD ProductionCenter
 */

class Options extends Authentication{

    /** @const entity api url */
    const ENTITY = 'option';

    /** @const request type */
    const METHOD = 'POST';


    /**
     * List Taxes, max per request is 250 results
     * @return json
     **/
    public function getOptions()
    {
        $GLOBALS['ACCESS'] = 'options/getInstances';

        return parent::processData([]);
    }

}