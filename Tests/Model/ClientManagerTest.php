<?php
/**
 * Created by PhpStorm.
 * User: Olivier
 * Date: 12/05/2017
 * Time: 18:37
 */

namespace Mukadi\APIAccessBundle\Tests\Model;

use Mukadi\APIAccessBundle\Model\ClientManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class ClientManagerTest extends WebTestCase{

    const CLIENT_CLASS = "APIBundle\\Entity\\APIClient";
    const CLIENT_ID = "ID_5915e9ffc6945";

    public function testCreate(){
        $m = $this->getManager();
        $c = $m->create();

        $this->assertInstanceOf(self::CLIENT_CLASS,$c);
    }

    public function __testUpdate(){
        $m = $this->getManager();
        $c = $m->create();
        $c = $m->update($c);

        $this->assertGreaterThan(0,$c->getId());
    }

    public function testFind(){
        $m = $this->getManager();
        $c = $m->find(self::CLIENT_ID);

        $this->assertEquals(self::CLIENT_ID,$c->getClientId());
    }

    public function testGetClass(){
        $m = $this->getManager();

        $this->assertEquals(self::CLIENT_CLASS,$m->getClass());
    }

    public function testDelete(){
        $m = $this->getManager();
        $c = $m->find(self::CLIENT_ID);
        $m->delete($c);
        $c = $m->find(self::CLIENT_ID);
        $this->assertNull($c);
    }

    /**
     * @return ClientManager
     */
    private function getManager(){
        $kernel=static::createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();
        $driver = $container->get("mukadi_api_access.driver");

        return new ClientManager($driver,self::CLIENT_CLASS);
    }
} 