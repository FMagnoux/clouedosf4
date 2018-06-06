<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FileController extends Controller
{
    /**
     * @Route("/file/upload", name="app_upload")
     */
    public function upload()
    {

    }

    /**
     * @Route("/file/show", name="app_show")
     */
    public function show()
    {
        $files = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(array('id_user' => $_SESSION['idUser']));


        return $this->render('file/show.twig', array(
            'files' => $files
        ));
    }
}
