<?php
/**
 * Created by PhpStorm.
 * User: Olivier
 * Date: 06/05/2017
 * Time: 12:29
 */

namespace Mukadi\APIAccessBundle\Model;


use Doctrine\Common\Persistence\ObjectManager;

class ClientManager implements ClientManagerInterface {
    /**
     * @var ObjectManager
     */
    protected $om;
    /**
     * @var string
     */
    protected $class;
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $repo;

    public function __construct(ObjectManager $om,$class){
        $this->om = $om;
        $this->repo = $this->om->getRepository($class);

        $meta = $om->getClassMetadata($class);
        $this->class = $meta->getName();
    }

    /**
     * @return Client
     */
    public function create()
    {
        $class = $this->getClass();
        /** @var Client $c */
        $c = new $class;
        $c->setClientId(uniqid('ID_'));
        $c->setClientSecret(bin2hex(openssl_random_pseudo_bytes(16)));

        return $c;
    }

    /**
     * @param Client $c
     * @return mixed
     */
    public function delete(Client $c)
    {
        $this->om->remove($c);
        $this->om->flush();
    }

    /**
     * @param Client $c
     * @return Client
     */
    public function update(Client $c)
    {
        $this->om->persist($c);
        $this->om->flush();

        return $c;
    }


    /**
     * @param $client_id
     * @return Client|null
     */
    public function find($client_id)
    {
        return $this->repo->findOneBy(array('client_id' => $client_id));
    }


    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }


} 