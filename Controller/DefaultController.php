<?php

namespace Mukadi\APIAccessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MukadiAPIAccessBundle:Default:index.html.twig');
    }
}
