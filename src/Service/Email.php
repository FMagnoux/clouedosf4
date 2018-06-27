<?php

namespace App\Service;

class Email
{
    private $mailer;
    private $templating;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating)
    {
        $this->mailer     = $mailer;
        $this->templating = $templating;
    }

    public function send($to, $title, $body, $parameters = array())
    {
        $message = (new \Swift_Message($title))
            ->setFrom("testflomagnoux@gmail.com")
            ->setTo($to)
            ->setBody(
                $this->templating->render(
                    $body,
                    $parameters
                ),
                'text/html'
            )
        ;
        $this->mailer->send($message);

    }
}