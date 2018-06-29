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
            ->findBy(array('activate' => true));

        return $this->render('admin/home.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route("/admin/contact", name="app_admin_contacts")
     */
    public function contact()
    {
        return $this->render('admin/contact.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/contact/{id}", name="app_admin_contact_show")
     */
    public function contactShow($id)
    {
        return $this->render('admin/contact_response.html.twig', [
            'controller_name' => 'AdminController',
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

            return $this->render('admin/add_task.html.twig', [
                'form' => $form->createView(),
                'success' => "La tâche a été ajoutée, merci de configurer celle ci"
            ]);

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
     * @Route("/admin/show/tasks", name="app_admin_show_tasks")
     */
    public function showTasks(){
        $tasks = $this->getDoctrine()
            ->getRepository(Task::class)
            ->findAll();

        return $this->render('admin/show_tasks.html.twig', [
            'tasks' => $tasks,
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

        return $this->redirectToRoute('app_admin_show_tasks');

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
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('activate', CheckboxType::class)
            ->add('update', SubmitType::class, ['label' => 'Modifier la tâche']);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $datas = $form->getData();
            $task->setName($datas['name'])
                ->setActivate($datas['activate'])
                ->setDescription($datas['description'])
                ->setUser($this->get('security.token_storage')->getToken()->getUser())
                ->setUpdateDate(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->render('admin/update_task.html.twig', [
                'form' => $form->createView(),
                'success' => "La tâche a été modifiée"
            ]);
        }
        return $this->render('admin/update_task.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
