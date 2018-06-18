<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Space;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class SpaceController extends Controller
{
    /**
     *
     * @Route("/space/access/", name="app_space_access")
     * @Method({"POST"})
     */
    public function access(Request $request){
        $url = $this->generateUrl('app_space_show', array("id" => $request->get("idSpace")));
        return $this->redirect($url);
    }

    /**
     * @Route("/space/{id}", name="app_space_show")
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
