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


        if ($exception instanceof NotFoundHttpException) {
            $content = $this->twig->render(
                'exceptions/not_found.html.twig',
                [
                    'message'   => 'Page non trouvée.',
                    'detail'    => $exception->getMessage(),
                ]
            );
        } else if ($exception instanceof AccessDeniedHttpException) {
            $content = $this->twig->render(
                'exceptions/not_found.html.twig',
                [
                    'message'   => 'Vous n\'avez pas les droits d\'accès à cette ressource.',
                    'detail'    => $exception->getMessage(),
                ]
            );
        } else {
            $content = $this->twig->render(
                'exceptions/not_found.html.twig',
                [
                    'message'   => 'Une erreur est survenue, veuillez actualiser la page.',
                    'detail'    => $exception->getMessage(),
                ]
            );
        }

        $exceptionEvent->setResponse((new Response())->setContent($content));
    }
}
