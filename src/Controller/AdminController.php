<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Task;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="app_admin")
     */
    public function home()
    {
        $tasks = $this->getDoctrine()
            ->getRepository(Task::class)
            ->findAll();

        return $this->render('admin/home.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route("/admin/add/task", name="app_admin_add_task")
     */
    public function addTask(Request $request)
    {
        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('path', TextType::class)
            ->add('add', SubmitType::class, ['label' => 'Ajouter la tâche']);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            $validator = $this->get('validator');
            $liste_erreurs = $validator->validate($datas);
            if(count($liste_erreurs) > 0){
                return $this->render('admin/add_task.html.twig', [
                    'form' => $form->createView(),
                    'errors' => $liste_erreurs
                ]);
            }

            $task = new Task();
            $task->setName($datas['name'])
                ->setActivate(false)
                ->setDescription($datas['description'])
                ->setPath($datas['path'])
                ->setUser($this->get('security.token_storage')->getToken()->getUser())
                ->setUpdateDate(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->clear();
            $this->addFlash(
                'notice',
                'La tâche a été ajoutée, merci de configurer celle ci'
            );
        }
        return $this->render('admin/add_task.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/show/task/{id}", name="app_admin_show_task")
     */
    public function showTask($id)
    {
        $task = $this->getDoctrine()
            ->getRepository(Task::class)
            ->find($id);

        return $this->render('admin/show_task.html.twig', [
            'task' => $task,
        ]);
    }

    /**
     * @Route("/admin/delete/task/{id}", name="app_admin_delete_task")
     */
    public function deleteTask($id)
    {
        $task = $this->getDoctrine()
            ->getRepository(Task::class)
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($task);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->clear();
        $this->addFlash(
            'notice',
            'La tâche a été supprimée'
        );
        return $this->redirectToRoute('app_admin');
    }

    /**
     * @Route("/admin/update/task/{id}", name="app_admin_update_task")
     */
    public function updateTask($id, Request $request){

        $task = $this->getDoctrine()
            ->getRepository(Task::class)
            ->find($id);

        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('name', TextType::class, array('data' => $task->getName()))
            ->add('description', TextareaType::class, array('data' => $task->getDescription()))
            ->add('activate', CheckboxType::class, array('data' => $task->getActivate()))
            ->add('path', TextType::class, array('data' => $task->getPath()))
            ->add('update', SubmitType::class, ['label' => 'Modifier la tâche']);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        $message = "La tâche a été modifiée avec succès";

        if ($form->isSubmitted() && $form->isValid()) {

            $datas = $form->getData();
            if($datas['activate']){
                if($this->checkTask($datas['name'])){
                    $task->setActivate(true);
                }
                else {
                    $task->setActivate(false);
                    $message .= " malheuresement elle ne peux pas être activée car elle ne respecte pas les conditions d'activation";
                }
            }
            else {
                $task->setActivate(false);
            }

            $task->setName($datas['name'])
                ->setDescription($datas['description'])
                ->setUser($this->get('security.token_storage')->getToken()->getUser())
                ->setPath($datas['path'])
                ->setUpdateDate(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->clear();
            $this->addFlash(
                'notice',
                $message
            );
            return $this->redirectToRoute('app_admin');
        }
        return $this->render('admin/update_task.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function checkTask($entityName){
        if(class_exists("App\Entity\\".$entityName) && class_exists("App\Controller\\".$entityName."Controller")){
            return true;
        }
        else {
            return false;
        }
    }
}
