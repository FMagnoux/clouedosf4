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

        //these fields must be rendered in the twig (name, fileupload, upload)
        $formBuilder
            ->add('name', TextType::class)
            ->add('fileupload', FileType::class)
            ->add('upload', SubmitType::class, ['label' => 'Ajouter le fichier']);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $file = new File();
            $user = $this->get('security.token_storage')->getToken()->getUser();

            $fileUploaded = $data['fileupload'];
            $file->setName($data['name'].$fileUploaded->guessExtension());
            if($this->checkUniqueName($file->getName())){
                $fileUploaded->move('../public/'.$user->getFolder(), $data['name'].$fileUploaded->guessExtension());

                $file->setDateAdd(new \DateTime());
                $file->setDateUpdate(new \DateTime());
                $file->setPath('../public/'.$user->getFolder().'/'.$data['name']);
                $file->setUser($user);
                $file->setUserId($user->getId());
                $file->setSize(filesize('../public/'.$user->getFolder().'/'.$data['name']));

                $user->setSpace($user->getSpace() - $file->getSize());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($file, $user);
                $entityManager->flush();

                return $this->redirectToRoute('app_show');
            }
            else {
                return $this->render('file/upload.html.twig', array(
                    'form' => $form->createView(),
                    'errorName' => "Le nom du fichier est déjà existant dans votre espace"
                ));
            }
        }

        return $this->render('file/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/file/delete", name="app_file_delete")
     */
    public function delete(File $file){

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $user->setSpace($user->getSpace() + $file->getSize());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($file);
        $entityManager->persist($user);
        $entityManager->flush();

        unlink("../public/".$user->getFolder()."/".$file->getName());

        return $this->redirectToRoute('app_show');

    }

    public function update(File $file , Request $request){
        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('name', TextType::class, array('data' => $file->getName()))
            ->add('update', SubmitType::class, ['label' => 'Ajouter le fichier']);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('file/upload.html.twig', [
            'form' => $form->createView(),
        ]);
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

    private function checkUniqueName($nameFile){
        $files = $this->getDoctrine()
            ->getRepository(File::class)
            ->findBy(array('userId' => $this->get('security.token_storage')->getToken()->getUser()->getId()));

        foreach ($files as $key => $value){
            if($files[$key]->getName() == $nameFile){
                return false;
            }
        }
        return true;
    }
}
