<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ShareController extends Controller
{
    /**
     * @Route("/share", name="share")
     */
    public function index()
    {
        return $this->render('share/index.html.twig', [
            'controller_name' => 'ShareController',
        ]);
    }
}
