<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ErrorController extends Controller
{
    /**
     * @Route("/error/403", name="error_denied_url")
     */
    public function denied()
    {
        return $this->render('error/forgot_password.html.twig', []);
    }
}
