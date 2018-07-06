<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Contact;


class ContactController extends Controller
{
    public function index(){

    }
    /**
     * @Route("/contact", name="app_contact")
     */
    public function contact(Request $request)
    {
        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('title', TextType::class)
            ->add('message', TextareaType::class)
            ->add('send', SubmitType::class, array('label' => 'Envoyer'));

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $datas = $form->getData();
            $validator = $this->get('validator');

            $liste_erreurs = $validator->validate($datas);

            if(!$form->isValid()) {
                return $this->render('contact/send.html.twig', array(
                    'form' => $form->createView(),
                    'errors' => $liste_erreurs
                ));
            }

            $contact = new Contact();
            $contact->setTitle($datas['title'])
                ->setMessage($datas['message'])
                ->setTime(new \DateTime())
                ->setUser($this->get('security.token_storage')->getToken()->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->clear();
            $this->addFlash(
                'notice',
                'Votre message a été envoyé'
            );
        }
        return $this->render('contact/send.html.twig', [
            'form' => $form->createView(),
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
}
