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
        $searchTerm = $request->request->get('searchQuery');

        //check to see if keyword exists in product table

        $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
        $products = $repository->findBySearchTerm($searchTerm);

        if($products != null) //cache hit - return results from db cache
        {
            return $this->render('default/searchResults.html.twig', array(
                'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
                'searchResults' => $products,
                'searchTerm' => $searchTerm
            ));
        } else { //cache miss - place in queue and invoke scrapers
            $this->container->get('queue_service')->enqueue($searchTerm);

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
            $userEmail = $this->container->get('login_service')->logUserIn($user,$request->request->get('login')['password']);

            if($userEmail) {
                $session->set('userName', $userEmail);
                return $this->redirectToRoute('homePage');
            } else {
                //TODO: add flashbag error for db errors
                return $this->redirectToRoute('loginForm');
            }
        }

        if($createAccountForm->isValid()) {
             $userEmail = $this->container->get('login_service')->createAccountAndLogUserIn($user);

            if($userEmail) {
                $session->set('userName', $userEmail);
                return $this->redirectToRoute('homePage');
            } else {
                //TODO: add flashbag error for db errors
                return $this->redirectToRoute('loginForm');
            }
        }

        return $this->render('login/loginForm.html.twig', array(
            'loginForm' => $loginForm->createView(),
            'createAccountForm' => $createAccountForm->createView()
        ));
    }
}
