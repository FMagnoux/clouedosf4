<?php

namespace App\Controller;

use http\Env\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EmailController extends Controller
{

    /**
     * @Route("/email", name="send")
     */
    public function send($to, $body)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('testflomagnoux@gmail.com')
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    'email/forgot_password.html.twig'
                ),
                'text/html'
            )
        ;
        $this->get('mailer')->send($message);

        return new Response();
    }
}
