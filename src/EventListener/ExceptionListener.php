<?php

namespace App\EventListener;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{
    public function __construct(
        private Environment $twig
    ) {
    }
    public function onKernelException(ExceptionEvent $exceptionEvent): void
    {
        $exception = $exceptionEvent->getThrowable();
        $message = '';

        if ($exception instanceof NotFoundHttpException) $message = 'Page non trouvée.';

        else if ($exception instanceof AccessDeniedHttpException) $message = 'Vous n\'avez pas les droits d\'accès à cette ressource.';

        else $message = 'Une erreur est survenue, veuillez actualiser la page.';

        $content = $this->twig->render(
            name: 'exceptions/not_found.html.twig',
            context:[ 'message'=> $message, 'detail'=> $exception->getMessage()],
        );

        $exceptionEvent->setResponse((new Response())->setContent($content));
    }
}
