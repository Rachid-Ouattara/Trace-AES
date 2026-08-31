<?php

namespace TraceAes;

use TraceAes\Service\AuthService;
use Zend\Mvc\MvcEvent;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/',
                ],
            ],
        ];
    }

    /**
     * Garde-fou d'authentification : toutes les pages de TraceAes exigent une
     * session active, sauf le controleur d'authentification lui-meme (sinon
     * impossible d'atteindre la page de connexion).
     */
    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $services = $application->getServiceManager();

        $application->getEventManager()->attach(MvcEvent::EVENT_ROUTE, function (MvcEvent $event) use ($services) {
            $routeMatch = $event->getRouteMatch();
            if (! $routeMatch) {
                return null;
            }

            // Le segment d'URL donne l'alias court ("auth"), pas le nom de
            // classe complet (qui n'apparait que dans les defaults de route
            // quand aucun controleur n'est precise dans l'URL).
            $controller = $routeMatch->getParam('controller');
            if (in_array($controller, ['auth', Controller\AuthController::class], true)) {
                return null;
            }

            /** @var AuthService $authService */
            $authService = $services->get(AuthService::class);
            if ($authService->estConnecte()) {
                return null;
            }

            $response = $event->getResponse();
            $response->getHeaders()->addHeaderLine('Location', '/trace-aes/auth/login');
            $response->setStatusCode(302);
            $event->stopPropagation(true);

            return $response;
        }, -100);
    }
}
