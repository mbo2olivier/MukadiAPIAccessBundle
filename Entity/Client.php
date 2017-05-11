<?php
/**
 * Created by PhpStorm.
 * User: Olivier
 * Date: 11/05/2017
 * Time: 14:39
 */

namespace Mukadi\APIAccessBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mukadi\APIAccessBundle\Model\Client as BaseClient;

/**
 * Class Client
 * @package Mukadi\APIAccessBundle\Entity
 *
 * @ORM\MappedSuperclass
 */
abstract class Client extends BaseClient{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", length=225, unique=true)
     */
    protected $client_id;
    /**
     * @var string
     *
     * @ORM\Column(name="client_secret", type="string", length=225)
     */
    protected $client_secret;
} 