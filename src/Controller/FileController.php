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
     * @Route("/file/upload", name="app_upload_file")
     */

    public function upload(Request $request)
    {
        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('name', TextType::class)
            ->add('fileupload', FileType::class)
            ->add('upload', SubmitType::class, ['label' => 'Ajouter le fichier']);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = new File();
            $data = $form->getData();
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $space = $user->getSpace();
            $fileUploaded = $data['fileupload'];

            $file->setName(str_replace(array(" ", "."), "_",$data['name']));
            $file->setExtension($fileUploaded->guessExtension());

            if($space->getSize() - $fileUploaded->getSize() <= 0){
                $this->get('session')->getFlashBag()->clear();
                $this->addFlash(
                    'error',
                    "Il n'y a pas assez d'espace pour intégrer ce fichier"
                );
            }
            else {
                if($this->checkUniqueName($file->getName())){

                    $file->setPath($data['name'].".".$fileUploaded->guessExtension());
                    $file->setSize($fileUploaded->getSize());

                    $fileUploaded->move("../public/".$space->getName(), $data['name'].".".$fileUploaded->guessExtension());

                    $file->setDateAdd(new \DateTime());
                    $file->setDateUpdate(new \DateTime());
                    $file->setNbDownload(0);
                    $space->addFile($file);
                    $space->setSize($user->getSpace()->getSize() - $file->getSize());

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($space);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_space_show', array('id' => $space->getId()));
                }
                else {
                    $this->get('session')->getFlashBag()->clear();
                    $this->addFlash(
                        'error',
                        "Le nom du fichier est déjà existant dans votre espace"
                    );
                }
            }
        }
        return $this->render('file/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/file/delete/{id}", name="app_delete_file")
     */
    public function delete($id){

        $file = $this->getDoctrine()
            ->getRepository(File::class)
            ->find(array('id' => $id));

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $user->setSpace($user->getSpace() + $file->getSize());

        if(unlink($file->getPath())){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($file);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_show');
        }

        $this->get('session')->getFlashBag()->clear();
        $this->addFlash(
            'error',
            "Impossible de supprimer le fichier"
        );
        return $this->render('space/show.html.twig');
    }

    /**
     * @Route("/file/update/{id}", name="app_update_file")
     */
    public function update($id , Request $request){
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $file = $this->getDoctrine()
            ->getRepository(File::class)
            ->find(array('id' => $id));

        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('name', TextType::class, array('data' => $file->getName()))
            ->add('update', SubmitType::class, ['label' => 'Modifier le fichier']);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            if($this->checkUniqueName($data['name'])){

                if(rename(
                    $user->getSpace()->getPath().$file->getName().".".$file->getExtension(),
                    $user->getSpace()->getPath().$data['name'].".".$file->getExtension())
                ){
                    $file->setName($data['name']);
                    $file->setDateUpdate(new \DateTime());
                    $file->setPath($data['name'].".".$file->getExtension());

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($file);
                    $entityManager->flush();
                }

                return $this->redirectToRoute('app_space_show', array('id' => $user->getSpace()->getId()));

            }
            else {
                $this->get('session')->getFlashBag()->clear();
                $this->addFlash(
                    'error',
                    "Le nom du fichier est déjà existant dans votre espace"
                );
            }
        }

        return $this->render('file/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/file/download/{id}", name="app_download_file")
     */
    public function download($id){
        $file = $this->getDoctrine()
            ->getRepository(File::class)
            ->find($id);

        $file->setNbDownload($file->getNbDownload() + 1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($file);
        $entityManager->flush();

        $space = $this->get('security.token_storage')->getToken()->getUser()->getSpace();

        return $this->file($space->getPath().$file->getPath());
    }

    /**
     * @param $nameFile
     * @return bool
     */
    private function checkUniqueName($nameFile){
        $files = $this->get('security.token_storage')->getToken()->getUser()->getSpace()->getFiles();
        foreach ($files as $key => $value){
            if($files[$key]->getName() == $nameFile){
                return false;
            }
        }
        return true;
    }
}
