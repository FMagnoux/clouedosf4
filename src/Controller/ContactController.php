<?php

namespace App\Controller;

use App\Service\Email;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Contact;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;


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
                ->setUser($this->get('security.token_storage')->getToken()->getUser())
                ->setResponse(false);

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
     * @Route("/admin/contact/{page}", defaults={"page": "0"}, name="app_admin_contact")
     */
    public function contactShow($page)
    {
        $contacts = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->getMessages($page * 10);
        $adapter = new DoctrineORMAdapter($contacts);
        $pagerfanta = new Pagerfanta($adapter);
        return $this->render('contact/admin_contact.html.twig', [
            'contacts' => $pagerfanta,
        ]);
    }

    /**
     * @Route("/admin/contact/response/{id}", name="app_contact_response")
     */
    public function contactResponse($id, Request $request, Email $email)
    {
        $contact = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->find($id);

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->add('message', TextareaType::class)
            ->add('send', SubmitType::class, array('label' => 'Envoyer'));

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $contact->getUser();
            $datas = $form->getData();
            $parameters = array(
                "message" => $datas['message'],
                "user" => $this->get('security.token_storage')->getToken()->getUser(),
                "contact" => $contact
            );
            $email->send(
                $user->getEmail(),
                "[Clouedo] Réponse pour le sujet :".$contact->getTitle(),
                'email/contact_response.html.twig', $parameters
            );

            $contact->setResponse(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->clear();
            $this->addFlash(
                'notice',
                'La réponse a été envoyée'
            );
            return $this->redirectToRoute('app_admin_contact');
        }
        return $this->render('contact/admin_contact_response.html.twig', [
            'contact' => $contact,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/contact/delete/{id}", name="app_contact_delete")
     */
    public function contactDelete($id)
    {
        $contact = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($contact);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->clear();
        $this->addFlash(
            'notice',
            'La réponse a été supprimée'
        );
        return $this->redirectToRoute('app_admin_contact');
    }
}
