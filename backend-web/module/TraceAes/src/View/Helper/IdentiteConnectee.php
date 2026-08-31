<?php

namespace TraceAes\View\Helper;

use TraceAes\Service\AuthService;
use Zend\View\Helper\AbstractHelper;

class IdentiteConnectee extends AbstractHelper
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke()
    {
        return $this->authService->identite();
    }
}
