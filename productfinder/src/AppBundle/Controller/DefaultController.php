<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ));
    }

    /**
     * @Route("/findProduct", name="searchResults")
     */
    public function searchAction(Request $request)
    {
		var_dump($request->request->get('searchQuery'));
		$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
		
        return $this->render('default/searchResults.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ));
    }

    /**
     * @Route("/api/findProduct")
     */
    public function apiNumberAction()
    {
        $data = array('1' =>
                        array('name' => 'THIS HAS BEEN CHANGED Some Name goes here',
                            'price' => '$123.45',
                            'decsription' => 'Lorem ipsum...'),
            '2' =>
                        array('name' => 'Second product name',
                            'price' => '$555',
                            'decsription' => 'Lorem ipsum...'),

            '3' =>
                    array('name' => 'The third product name',
                        'price' => '$25',
                        'decsription' => 'Lorem ipsum...'),
                        );

        return new Response(
            json_encode($data),
            200,
            array('Content-Type' => 'application/json')
        );
    }
}
