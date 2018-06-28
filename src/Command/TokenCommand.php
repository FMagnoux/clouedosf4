<?php

namespace App\Command;

use App\Service\Token;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TokenCommand extends ContainerAwareCommand {

    protected function configure () {
        $this->setName('app:cleantokens');
        $this->setDescription("Nettoie les tokens dont la durée de vie est inférieur à la date du jour");
        $this->setHelp("Help");
    }

    public function execute (InputInterface $input, OutputInterface $output) {
        $output->writeln('Recuperation des tokens');
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $tokens = $em
            ->getRepository(\App\Entity\Token::class)
            ->findOutDated();

        foreach ($tokens as $token){
            $em->remove($token);
        }
        $em->flush();
        $output->writeln('Les tokens ont ete supprimes');
    }
}