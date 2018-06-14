<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Space;

class SpaceController extends Controller
{
    /**
     * @Route("/space/{id}", name="app_show_space")
     */
    public function show($id)
    {
        $space = $this->getDoctrine()
            ->getRepository(Space::class)
            ->find($id);

        return $this->render('space/show.html.twig', array(
            'files' => $space->getFiles()
        ));
    }
}
