<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class GlobalController extends AbstractController
{
    public function __construct(
        private int $page,
        private int|null $count,
        private string|null $redirect,
    ){}

    public function isPassWordValid(UserPasswordHasherInterface $hasher, Request $request, mixed $entity): bool
    {

        if (!$hasher->isPasswordValid($this->getUser(), $request->request->get('_password'))) {
            $this->addFlash(type: 'warning', message: 'Mot de passe invalide.');
            return false;
        }

        if (!$this->isCsrfTokenValid('delete' . $entity->getId(), $request->request->get('_token'))) {
            $this->addFlash(type: 'warning', message: 'Token invalide.');
            return false;
        }

        return true;
    }

    public function pagination(Request $request): void
    {
        $this->setCount((int)$request->query->get('count') ?: null); //Nombres de lignes sur la page courante
        $this->setPage((int)($request->get('page')) ?: 1); //numéro de la page courante
        $this->setRedirect(htmlspecialchars($request->query->get('redirect')) ?: null); //Page de redirection

    }

    public function showDeletePage():int
    {
        return ($this->getCount() > 0 && $this->getPage() >= 2) || $this->getPage() === 1 ?  $this->getPage()  : $this->getPage() - 1;
    }

    public function showAddEditPage ():int
    {
        return $this->getCount() ? ceil($this->getCount() / 10) : $this->getPage();
    }


    /**
     * Get the value of page
     */
    public function getPage(): int
    {
            return $this->page;
    }

    /**
     * Set the value of page
     */
    public function setPage($page): self
    {
            $this->page = $page;

            return $this;
    }

    /**
     * Get the value of count
     */
    public function getCount(): ?int
    {
            return $this->count;
    }

    /**
     * Set the value of count
     */
    public function setCount($count): self
    {
            $this->count = $count;

            return $this;
    }

    /**
     * Get the value of redirect
     */
    public function getRedirect(): ?string
    {
            return $this->redirect;
    }

    /**
     * Set the value of redirect
     */
    public function setRedirect($redirect): self
    {
            $this->redirect = $redirect;

            return $this;
    }
}
