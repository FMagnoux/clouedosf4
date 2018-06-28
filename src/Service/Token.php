<?php

namespace App\Service;

use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class Token
 * @package App\Service
 */
class Token
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var
     */
    private $token;

    /**
     * @var array
     */
    private $types;

    /**
     * Token constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
        $this->token = new \App\Entity\Token();
        $this->types = array(
            "TOKEN_VALID_ACCOUNT",
            "TOKEN_FORGOT_PASSWORD"
        );
    }

    /**
     * @param $value
     * @return bool
     * @throws \Exception
     */
    public function initialize($value){
        $this->token = $this->em
            ->getRepository(\App\Entity\Token::class)
            ->findOneBy(array('value' => $value));
        if(!$this->token){
            throw new \Exception("Unable to find this token");
        }
        else if (!$this->available()){
            throw new \Exception("This token is not active");
        }

        return true;
    }
    /**
     * @param $type
     * @throws \Exception
     */
    public function generate($type)
    {
        if(!in_array($type, $this->types)){
            throw new \Exception("Token's type is undefined");
        }

        $tokens = $this->em
            ->getRepository(\App\Entity\Token::class)
            ->findBy(array('user' => $this->token->getUser()));

        if(count($tokens) > 3){
            throw new \Exception('More 3 tokens are active, try a other time');
        }

        $this->token->setValue(md5(random_bytes(10)));
        $this->token->setType($type);

        $datetime = new \DateTime();
        $datetime->add(new DateInterval('P3D'));
        $this->token->setLife($datetime);

        $entityManager = $this->em;
        $entityManager->persist($this->token);
        $entityManager->flush();
    }

    /**
     * @return bool
     */
    public function available(){
        $datetime = new \DateTime();
        if($this->token->getLife() < $datetime){
            return false;
        }

        return true;
    }

    /**
     *
     */
    public function clean(){
        $tokens = $this->em
            ->getRepository(Token::class)
            ->findOutDated();

        foreach ($tokens as $token){
            $this->em->remove($token);
        }
        $this->em->flush();
    }
    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }



}