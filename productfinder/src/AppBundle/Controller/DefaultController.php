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
        //var_dump($session->get('userName'));

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
     */
    public function broadcastToClients(Request $request)
    {
        $json = $request->request->get('json'); // GET param

        $client = new Client("product_realm");
        $client->addTransportProvider(new PawlTransportProvider("ws://127.0.0.1:8080/"));
       // $json = '{ "keyword" : "nintendo", "results": [ { "name": "Pokemon Omega Ruby (Nintendo 3DS)", "price": "28.99", "rating": "4.836", "image": "http://i.walmartimages.com/i/p/00/04/54/96/74/0004549674292_100X100.jpg", "websiteURL": "http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FPokemon-Omega-Ruby-Nintendo-3DS%2F37202055%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi", "vendor": "Walmart", "description": "Pokemon Omega Ruby and Alpha Sapphire will take players on a journey like no other as they collect, battle, and trade Pokemon while trying to stop a shadowy group with plans to alter the Hoenn region forever." }, { "name": "Nintendo Wii U Super Mario 3D World Deluxe Set Console", "price": "276.97", "rating": "4.712", "image": "http://i.walmartimages.com/i/p/00/04/54/96/88/0004549688162_100X100.jpg", "websiteURL": "http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FNintendo-Wii-U-Super-Mario-3D-World-Deluxe-Set-Console%2F39404094%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi", "vendor": "Walmart", "description": "&lt;b&gt;Nintendo Wii U Super Mario 3D World Deluxe Set Console Includes:&lt;/b&gt;&lt;ul&gt;&lt;li&gt;Black Nintendo Wii U Super Mario 3D World Deluxe Set Console&lt;/li&gt;&lt;li&gt;Super Mario 3D World Game&lt;/li&gt;&lt;li&gt;Nintendo Land Game&lt;/li&gt;&lt;li&gt;Wii U deluxe set AC Adapter&lt;/li&gt;&lt;li&gt;Wii U GamePad AC Adapter&lt;/li&gt;&lt;li&gt;High Speed HDMI Cable&lt;/li&gt;&lt;li&gt;Sensor Bar&lt;/li&gt;&lt;li&gt;Wii U GamePad Cradle&lt;/li&gt;&lt;li&gt;Wii U GamePad Stand Support&lt;/li&gt;&lt;li&gt;Wii U deluxe set console stand&lt;/li&gt;&lt;/ul&gt;" } ] }';
        //echo $json;die();

        $jsonObject = json_decode($json);
        $tube = $jsonObject->{'keyword'};

        $client->on('open', function (ClientSession $session) use ($json, $tube) {
            // publish an event
            $session->publish($tube, [$json], [], ["acknowledge" => true])->then(
                function () {
                    echo "Publish Acknowledged!\n";
                    die(); //??? need to die out to keep it from going forever?
                },
                function ($error) {
                    // publish failed
                    echo "Publish Error {$error}\n";
                }
            );
        });

        $client->start();
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
		//var_dump($request->request->get('searchQuery'));
        $searchTerm = $request->request->get('searchQuery');
        //var_dump($sessionId);

        //check to see if keyword exists in product table
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Product');
        $product = $repository->findByName($searchTerm);

//        var_dump($product);

        if($product != null) //cache hit - return results from mysql table
        {
            return $this->render('default/searchResults.html.twig', array(
                'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
                'searchResults' => $product,
                'searchTerm' => $searchTerm
            ));
        } else { //cache miss - invoke scrapers
            // put it in the queue
            $queueName ='searchTerms';

            $queueValue = $searchTerm . ':' . time();

            $exchangeName = 'products.crawlers'; //the exchange for the crawlers from which the crawlers read

            //TODO: get username, port, pass from config file
            $connection = new AMQPStreamConnection('localhost', 5672, 'queue_user', 'BVfDqRGK9Y3G');
//sudo rabbitmqctl set_user_permissions queue_user ".*" ".*" ".*"
            $channel = $connection->channel();
            $channel->exchange_declare($exchangeName, 'fanout', false, false, false);

            //$channel->queue_declare($queueName, false, false, false, false);

            $msg = new AMQPMessage($queueValue);
            $channel->basic_publish($msg, $exchangeName, $queueName);

            echo " [x] placed in '$searchTerm' queue:  $queueValue\n";
            $channel->close();
            $connection->close();

            return $this->render('default/searchResults.html.twig', array(
                'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
                'searchResults' => '',
                'searchTerm' => $searchTerm
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
