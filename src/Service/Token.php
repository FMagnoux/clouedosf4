<?php

namespace App\Service;

use DateInterval;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class Token
 * @package App\Service
 */
class Token
{
    private $em;

    /**
     * @var
     */
    private $token;
    /**
     * @var array
     */
    private $types;

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
        $this->token = new \App\Entity\Token();
        $this->types = array(
            "TOKEN_VALID_ACCOUNT",
            "TOKEN_FORGOT_PASSWORD"
        );
    }

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
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }


}