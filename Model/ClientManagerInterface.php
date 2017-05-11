<?php
/**
 * Created by PhpStorm.
 * User: Olivier
 * Date: 06/05/2017
 * Time: 12:33
 */

namespace Mukadi\APIAccessBundle\Model;


interface ClientManagerInterface {
    /**
     * @return Client
     */
    public function create();

    /**
     * @param Client $c
     * @return mixed
     */
    public function delete(Client $c);

    /**
     * @param Client $c
     * @return Client
     */
    public function update(Client $c);

    /**
     * @param $client_id
     * @return Client|null
     */
    public function find($client_id);

    /**
     * @return string
     */
    public function getClass();
}