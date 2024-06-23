<?php

namespace App\Service;

use App\Repository\SystemConfigRepository;

class SystemConfigService
{

    private $systemConfigRepository;

    public function __construct(SystemConfigRepository $systemConfigRepository)
    {
        $this->systemConfigRepository = $systemConfigRepository;
    }

    public function getSystemConfig()
    {
        return $this->systemConfigRepository->find(1);
    }
}