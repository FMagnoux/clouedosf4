<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\File;

class FileController extends Controller
{
    /**
     * @Route("/file/upload", name="app_upload")
     */
    public function upload(Request $request)
    {
        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('name',TextType::class)
            ->add('fileupload',FileType::class)
            ->add('upload', SubmitType::class, array('label' => 'Ajouter le fichier'));

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = new File();
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $file->setName($form['name']);

            $file = $form['attachment']->getData();
            $file->move("../public/".$user->getFolder(), $form['name']);

            $file->setDateAdd(new \DateTime());
            $file->setPath("../public/".$user->getFolder()."/".$form['name']);
            $file->setUser($user);
            $file->setUserId($user->getId());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($file);
            $entityManager->flush();
        }

        return $this->render('file/upload.html.twig', array(
            'form' => $form->createView()
        ));

    }

    /**
     * @Route("/file/show", name="app_show")
     */
    public function show()
    {
        $files = $this->getDoctrine()
            ->getRepository(File::class)
            ->findBy(array('userId' => $this->get('security.token_storage')->getToken()->getUser()->getId()));

        return $this->render('file/show.html.twig', array(
            'files' => $files
        ));
    }
}
