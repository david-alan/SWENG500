<?php

namespace AppBundle\Controller;

use AppBundle\PublishService;
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

use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homePage")
     */
    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        $session->start();

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
     * @Route("/broadcast", name="broadcast")
     *
     * Distribute the JSON sent as a POST request to subscribers
     * (note: our webscrapers are sending the JSON as the response
     * body rather than a parameter)
     */
    public function broadcastToClients(Request $request)
    {
        //$json = $request->request->get('json'); // POST param
        $json = $request->getContent(); //JSON sent as body of POST request

        $this->container->get('cache_service')->addCache($json);
        $this->container->get('publish_service')->sendPayload($json);
    }

    /**
     * @Route("/findProduct", name="searchResults")
     */
    public function searchAction(Request $request)
    {
        if(!$this->container->get('session')->isStarted()){
            $session = new Session();
        } else {
            $session = $this->container->get('session');
        }

        $session->start();//maybe use session->getSession() or something?
        $sessionId = $session->getId();
        $searchTerm = $request->request->get('searchQuery');

        //check to see if keyword exists in product table
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Product');
        $products = $repository->findBySearchTerm($searchTerm);

        if($products != null) //cache hit - return results from mysql table
        {
            return $this->render('default/searchResults.html.twig', array(
                'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
                'searchResults' => $products,
                'searchTerm' => $searchTerm
            ));
        } else { //cache miss - invoke scrapers
            // put it in the queue

            //sudo rabbitmqctl set_user_permissions queue_user ".*" ".*" ".*"
            $queueName ='products';
            $queueValue = $searchTerm;

            //TODO: get username, port, pass from config file
            $connection = new AMQPStreamConnection('localhost', 5672, 'queue_user', 'BVfDqRGK9Y3G');

            $channel = $connection->channel();
            $channel->queue_declare($queueName, false, true, false, false);

            $msg = new AMQPMessage($queueValue,
                array('delivery_mode' => 2) # make message persistent (flush to disk)
            );

            $channel->basic_publish($msg, '', $queueName);

            echo " [x] placed in '$searchTerm' queue:  $queueValue\n";
            $channel->close();
            $connection->close();

            return $this->render('default/searchResults.html.twig', array(
                'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
                'searchResults' => '',
                'searchTerm' => $searchTerm
            ));
        }
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
//check password is valid

                if($user->verifyPassword($request->request->get('login[password]'))){
                    $session->set('userName',$userSearch->getEmail());
                    return $this->redirectToRoute('homePage');
                } else {
                    throw new \Exception('username and password do not match');
                }
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
}
