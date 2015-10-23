<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Entity\User;
use AppBundle\Entity\Product;
use AppBundle\Form\User\LoginType;
use AppBundle\Form\User\CreateAccountType;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homePage")
     */
    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        $session->start();
        var_dump($session->get('userName'));

        $userName = $session->get('userName');
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
            'userName' => $userName
        ));
    }

    /**
     * @Route("/createAccount", name="newaccountpage")
     */
    public function createAccountAction(Request $request)
    {
        return $this->render('login/createAccount.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ));
    }

    /**
     * @Route("/findProduct", name="searchResults")
     */
    public function searchAction(Request $request)
    {
        $city = '';
        if(!$this->container->get('session')->isStarted()){
            $session = new Session();
        } else {
            $session = $this->container->get('session');
        }

        $session->start();//maybe use session->getSession() or something?
        $sessionId = $session->getId();
		//var_dump($request->request->get('searchQuery'));
        $searchTerm = $request->request->get('searchQuery');
        //var_dump($sessionId);

        //check to see if keyword exists in product table
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Product');
        $product = $repository->findByName($searchTerm);

        var_dump($product);

        if($product != null) //cache hit - return results from mysql table
        {
            return $this->render('default/searchResults.html.twig', array(
                'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
                'searchResults' => $product,
            ));
        } else { //cache miss - invoke scrapers
            // put it in the queue
            $queueName ='searchTerms';

            $queueValue = $searchTerm . ':' . $city . ':' . time();
            //http://stackoverflow.com/questions/14699873/how-to-reset-user-for-rabbitmq-management
            $exchangeName = 'products';

            //TODO: get username, port, pass from config file
            $connection = new AMQPStreamConnection('localhost', 5672, 'queue_user', 'BVfDqRGK9Y3G');
            $channel = $connection->channel();
            $channel->exchange_declare($exchangeName, 'fanout', false, false, false);

            //$channel->queue_declare($queueName, false, false, false, false);

            $msg = new AMQPMessage($queueValue); //time tells us when to prune/expire old entries from cache
            $channel->basic_publish($msg, $exchangeName, $queueName);

            echo " [x] Sent $queueValue\n";

            $channel->close();
            $connection->close();

            return $this->render('default/searchResults.html.twig', array(
                'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
                'searchResults' => ''
            ));
        }

        return $this->render('default/searchResults.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ));
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(Request $request)
    {
        $session = $request->getSession();
        $session->invalidate();
        return $this->redirectToRoute('homePage');
    }

    /**
     * @Route("/login", name="loginForm")
     */
    public function loginForm(Request $request)
    {
        $user = new User();
        $loginForm = $this->createForm(new LoginType(), $user);
        $createAccountForm = $this->createForm(new CreateAccountType(), $user);

        $loginForm->handleRequest($request);
        $createAccountForm->handleRequest($request);

        $session = $request->getSession();
        $session->start();

        if($loginForm->isValid()) {
            try {
                $repository = $this->getDoctrine()->getRepository(User::class);
                $userSearch = $repository->findOneByEmail($user->getEmail());

                $session->set('userName',$userSearch->getEmail());
                return $this->redirectToRoute('homePage');
            } catch (\Exception $e) {
                //TODO: add flashbag error for db errors
                return $this->redirectToRoute('loginForm');
            }
        }

        if($createAccountForm->isValid()) {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            } catch (\Exception $e) {
                //TODO: add flashbag error for duplicate username
                return $this->redirectToRoute('loginForm');
            }

            $formData = $request->request->get('createAccount');
            $session->set('userName',$formData['email']);
            return $this->redirectToRoute('homePage');
        }

        return $this->render('login/loginForm.html.twig', array(
            'loginForm' => $loginForm->createView(),
            'createAccountForm' => $createAccountForm->createView()
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
