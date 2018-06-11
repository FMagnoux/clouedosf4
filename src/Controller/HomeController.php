<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    /**
     * @Route("/home", name="app_home")
     */
    public function home()
    {
        return $this->render('home.html.twig', [
        ]);
    }
}
