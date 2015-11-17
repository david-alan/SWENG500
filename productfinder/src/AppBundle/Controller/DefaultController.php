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
        $json = $request->request->get('json'); // POST param

        $client = new Client("product_realm");
        $client->addTransportProvider(new PawlTransportProvider("ws://127.0.0.1:8080/"));
//$json = '{"searchTerm":"nintendo","results":[{"name":"Nintendo Wii U Deluxe Set: Super Mario 3D World and Nintendo Land Bundle - Black 32 GB","price":"$349.95","rating":"4.6","image":"http://ecx.images-amazon.com/images/I/514k8rSpymL._AA160_.jpg","websiteURL":"http://www.amazon.com/Nintendo-Wii-Deluxe-Set-Bundle-u/dp/B00MVUKM0A/ref=sr_1_1?ie=UTF8&qid=1447724303&sr=8-1&keywords=nintendo","vendor":"Amazon","description":""},{"name":"Nintendo New 3DS XL Black","price":"$195.93","rating":"4.4","image":"http://ecx.images-amazon.com/images/I/51JFoR4kQ5L._AA160_.jpg","websiteURL":"http://www.amazon.com/Nintendo-New-3DS-XL-Black/dp/B00S1LRX3W/ref=sr_1_3?ie=UTF8&qid=1447724303&sr=8-3&keywords=nintendo","vendor":"Amazon","description":""},{"name":"Nintendo Entertainment System Mouse Pad, Mousepad (10.2 x 8.3 x 0.12 inches)","price":"$12.80","rating":"","image":"http://ecx.images-amazon.com/images/I/51kAFhdt+9L._AA160_.jpg","websiteURL":"http://www.amazon.com/Nintendo-Entertainment-Mouse-Pad-Mousepad/dp/B00WR388H0/ref=sr_1_4?ie=UTF8&qid=1447724303&sr=8-4&keywords=nintendo","vendor":"Amazon","description":""},{"name":"Nintendo DS Lite Cobalt / Black","price":"$20.97","rating":"4.4","image":"http://ecx.images-amazon.com/images/I/415YsYGUm3L._AA160_.jpg","websiteURL":"http://www.amazon.com/Nintendo-DS-Lite-Cobalt-Black/dp/B001290A3U/ref=sr_1_5?ie=UTF8&qid=1447724303&sr=8-5&keywords=nintendo","vendor":"Amazon","description":""},{"name":"Nintendo Wii U 32GB Mario Kart 8 (Pre-Installed) Deluxe Set","price":"$299.00","rating":"4.6","image":"http://ecx.images-amazon.com/images/I/51ze8ZiikFL._AA160_.jpg","websiteURL":"http://www.amazon.com/Nintendo-32GB-Mario-Pre-Installed-Deluxe-u/dp/B011XO54MA/ref=sr_1_7?ie=UTF8&qid=1447724303&sr=8-7&keywords=nintendo","vendor":"Amazon","description":""},{"name":"Nintendo New 3DS XL System Black (REDSVAAA)","price":"$199.99","rating":"5.0","image":"http://www.staples-3p.com/s7/is/image/Staples/s0950523_sc7","websiteURL":"http://www.staples.comhttp://www.staples.com/Nintendo-New-3DS-XL-System-Black-REDSVAAA-/product_1598104","vendor":"Staples","description":""},{"name":"Nintendo® 3DS XL Handheld Gaming Console, 4GB SD Card, Black","price":"$222.69","rating":"4.5","image":"http://www.staples-3p.com/s7/is/image/Staples/m000181993_sc7","websiteURL":"http://www.staples.comhttp://www.staples.com/Nintendo-3DS-XL-Handheld-Gaming-Console-4GB-SD-Card-Black/product_478151","vendor":"Staples","description":""},{"name":"Insten® Travel Charger For Nintendo DSi/DSi LL/XL/2DS/3DS/3DS XL/LL, Gray","price":"$5.19","rating":"5.0","image":"http://www.staples-3p.com/s7/is/image/Staples/m000508850_sc7","websiteURL":"http://www.staples.comhttp://www.staples.com/Insten-Travel-Charger-For-Nintendo-DSi-DSi-LL-XL-2DS-3DS-3DS-XL-LL-Gray/product_972712","vendor":"Staples","description":""},{"name":"Nintendo Nunchuk, Black","price":"$17.29","rating":"5.0","image":"http://www.staples-3p.com/s7/is/image/Staples/s0797430_sc7","websiteURL":"http://www.staples.comhttp://www.staples.com/Nintendo-Nunchuk-Black/product_330755","vendor":"Staples","description":""},{"name":"Nintendo RVLAPNPC Princess Themed Remote Plus for Wii","price":"$38.99","rating":"","image":"http://www.staples-3p.com/s7/is/image/Staples/s0962881_sc7","websiteURL":"http://www.staples.comhttp://www.staples.com/Nintendo-RVLAPNPC-Princess-Themed-Remote-Plus-for-Wii/product_1605896","vendor":"Staples","description":""},{"name":"Nintendo CTRPECLE 3DS Pokemon Alpha Sapphire","price":"$39.99","rating":"","image":"http://www.staples-3p.com/s7/is/image/Staples/s0973204_sc7","websiteURL":"http://www.staples.comhttp://www.staples.com/Nintendo-CTRPECLE-3DS-Pokemon-Alpha-Sapphire/product_1671723","vendor":"Staples","description":""},{"name":"Nintendo 2DS Handheld Game Console - Crimson Red","price":"$99.0","rating":"4.313","image":"http://i.walmartimages.com/i/p/00/04/54/96/78/0004549678144_100X100.jpg","websiteURL":"http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FNintendo-2DS-with-Mario-Kart-7-Game-Crimson-Red%2F39797379%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi","vendor":"Walmart","description":"Experience the best of both worlds with the Nintendo 2DS with Mario Kart 7 Game. This product provides you with the latest gaming technology that brings together the power of two systems into a single, affordable package. Play all of the games you like from both the Nintendo DS and Nintendo 3DS in 2D. This stylish crimson red 2DS console lets you connect with friends, other players and even wireless hotspot using the wireless StreetPass and SpotPass communication modes. Unlock exclusive content for your games and download other forms of entertainment from games, to photos and beyond. This Nintendo 2DS game is extremely portable, so you can experience the ultimate in 2D gaming wherever you go."},{"name":"Nintendo 2DS - Handheld game console - electric blue","price":"$99.0","rating":"4.435","image":"http://i.walmartimages.com/i/p/00/04/54/96/78/0004549678143_100X100.jpg","websiteURL":"http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FNintendo-2DS-with-Mario-Kart-7-Game-Electric-Blue%2F39797378%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi","vendor":"Walmart","description":"Experience the best of both worlds with the Nintendo 2DS with Mario Kart 7 Game. This product provides you with the latest gaming technology that brings together the power of two systems into a single, affordable package. Play all of the games you like from both the Nintendo DS and Nintendo 3DS in 2D. This stylish electric blue Nintendo 2DS console lets you connect with friends, other players and even wireless hotspot using the wireless StreetPass and SpotPass communication modes. Unlock exclusive content for your games and download other forms of entertainment from games, to photos and beyond. This 2DS console is extremely portable, so you can experience the ultimate in gaming wherever you go."},{"name":"Nintendo 3DS XL, Assorted Colors","price":"$159.0","rating":"4.709","image":"http://i.walmartimages.com/i/p/00/04/54/96/78/0004549678081_Color_Black_SW_100X100.jpg","websiteURL":"http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FNintendo-3DS-XL-Assorted-Colors%2F26833356%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi","vendor":"Walmart","description":"The Nintendo 3DS XL system combines next-generation portable gaming with eye-popping 3D visuals without the need for special glasses, giving you a new dimension in entertainment. Take 3D photos or connect to friends and use wireless hot spots with the wireless StreetPass and SpotPass communication modes. From games to photos and beyond, the Nintendo 3DS XL system offers quality 3D entertainment. It comes bundled with a 4GB SD card, making it suitable for downloading content from the Nintendo eShop. The Nintendo 3DS XL console can also play all of the original Nintendo DS games. Nintendo DS games will not appear in 3D."},{"name":"Nintendo Luigis Mansion: Dark Moon - Action/Adventure Game - Cartridge - Nintendo 3DS","price":"$27.99","rating":"4.745","image":"http://i.walmartimages.com/i/p/00/04/54/96/74/0004549674215_100X100.jpg","websiteURL":"http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FLuigi-s-Mansion-Dark-Moon-Nintendo-3DS%2F23001144%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi","vendor":"Walmart","description":"Explore haunted mansions with Luigi! Guide clumsy Luigi through massive ghost-infested mansions across the Evershade Valley, each with their own distinct features and challenges, on an action-packed hunt for the pieces of the Dark Moon. Armed with the ghost-sucking Poltergust 5000 and other new gadgets, youll have to capture ghosts, solve puzzles, and battle monster-sized bosses. Can you and Luigi build up the courage to save the day? The pieces of the Dark Moon are scattered therein, so youll need to explore every ghoul-infested nook and cranny of each haunted mansion. Hope you dont spook easily!"},{"name":"Nintendo 3DS XL System - 4.9\" Active Matrix TFT Color LCD - Black - Dual Screen - 800 x 240 - 128 MB Memory Digital Media Professionals PICA200 - Wireless LAN - Battery Rechargeable","price":"$195.93","rating":"3.846","image":"http://i.walmartimages.com/i/p/00/04/54/96/78/0004549678151_100X100.jpg","websiteURL":"http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FNew-Nintendo-3DS-XL-Handheld-Black%2F43091538%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi","vendor":"Walmart","description":"This New Nintendo 3DS XL Handheld enhances your gaming experience with added amiibo support. Take 3D photos and have fun connecting with your friends. The Nintendo 3DS handheld combines next generation portable gaming with super stable 3D technology and added control features. It includes two screens: the bottom one makes use of a stylus that is stored in the unit and the top displays 3D visuals. It uses the same adapter as Nintendo DSi Nintendo 3DS and 2DS."},{"name":"New Nintendo 3DS XL - Red","price":"$194.09","rating":"3.4","image":"http://i.walmartimages.com/i/p/00/04/54/96/78/0004549678150_100X100.jpg","websiteURL":"http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FNew-Nintendo-3DS-XL-Handheld-Red%2F43091537%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi","vendor":"Walmart","description":"This New Nintendo 3DS XL Handheld enhances your gaming experience with added amiibo support. Take 3D photos and have fun connecting with your friends. The Nintendo 3DS handheld combines next generation portable gaming with super stable 3D technology and added control features. It includes two screens: the bottom one makes use of a stylus that is stored in the unit and the top displays 3D visuals. It uses the same adapter as Nintendo DSi Nintendo 3DS and 2DS."},{"name":"The Legend of Zelda Tri Force Heroes - Nintendo 3DS","price":"$36.49","rating":"","image":"http://i.walmartimages.com/i/p/00/04/54/96/74/0004549674334_100X100.jpg","websiteURL":"http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FThe-Legend-of-Zelda-Tri-Force-Heroes-Nintendo-3DS%2F45736671%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi","vendor":"Walmart","description":"In the visual style of the critically-acclaimed The Legend of Zelda: A Link Between Worlds game comes a new adventure. In this journey, three players team up - each as Link - to cooperatively make their way through inventive dungeons and battle bosses. Use the new Totem mechanic to stack three Links on top of each other to reach higher grounds and solve puzzles. Collect loot to create wearable outfits, each with a different boost or ability."},{"name":"Super Mario Maker - Wii U","price":"$55.97","rating":"5.0","image":"http://i.walmartimages.com/i/p/00/04/54/96/90/0004549690375_100X100.jpg","websiteURL":"http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FSuper-Mario-Maker-Wii-U%2F45725079%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi","vendor":"Walmart","description":"The ultimate evolution of Super Mario Bros. is here! The Mario experience of your dreams has arrived and is bursting with creativity... including yours! Play a near-limitless number of intensely creative Super Mario levels from players around the world. Its easy enough to create your own levels with the Wii U GamePad controller that it may feel like youre simply sketching out your ideas on paper, but you can now bring enemies and objects into a playable course in ways you could only dream of before. What was impossible in traditional Mario games is now impossibly fun, so let your imagination run wild!"},{"name":"Nintendo Wii Mini Red with Mario Kart","price":"$99.0","rating":"4.335","image":"http://i.walmartimages.com/i/p/00/04/54/96/88/0004549688127_100X100.jpg","websiteURL":"http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FNintendo-Wii-Mini-Red-with-Mario-Kart%2F30913801%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi","vendor":"Walmart","description":"Have hours of gaming fun with the Nintendo Wii Mini with Mario Kart. The new Wii mini offers up an unbelievable amount of single and multiplayer options. One of the bestselling games of all time, Mario Kart Wii, is included in the box. The red Nintendo Wii also includes a Wii Remote Plus and Nunchuk controller. The mini Nintendo Wii console does not connect to the Internet and does not offer online features in games."},{"name":"Pokemon Alpha Sapphire (Nintendo 3DS)","price":"$36.99","rating":"4.691","image":"http://i.walmartimages.com/i/p/00/04/54/96/74/0004549674294_100X100.jpg","websiteURL":"http://c.affil.walmart.com/t/api02?l=http%3A%2F%2Fwww.walmart.com%2Fip%2FPokemon-Alpha-Sapphire-Nintendo-3DS%2F37202057%3Faffp1%3DII40IhbphbkRO8fVRK1nX1fG6JoYNA4gn58AWmWaG-E%26affilsrc%3Dapi%26veh%3Daff%26wmlspartner%3Dreadonlyapi","vendor":"Walmart","description":"Pokemon Omega Ruby and Alpha Sapphire will take players on a journey like no other as they collect, battle, and trade Pokemon while trying to stop a shadowy group with plans to alter the Hoenn region forever."},{"name":"1 vs. 100 - Nintendo DS","price":"$14.99","rating":"null","image":"http://img.bbystatic.com/BestBuy_US/images/products/8998/8998757_sc.jpg","websiteURL":"http://www.bestbuy.com/site/1-vs-100-nintendo-ds/8998757.p?id=1218007385090&skuId=8998757","vendor":"Best Buy","description":"Prove yourself against 100 clever opponents"},{"name":"1,001 Touch Games - Nintendo DS","price":"$19.99","rating":"4.5","image":"http://img.bbystatic.com/BestBuy_US/images/products/3455/3455458_sc.jpg","websiteURL":"http://www.bestbuy.com/site/1001-touch-games-nintendo-ds/3455458.p?id=1218403504187&skuId=3455458","vendor":"Best Buy","description":"Discover 1,001 new ways to have fun"},{"name":"10 Minute Solution - Nintendo Wii","price":"$19.99","rating":"3.0","image":"http://img.bbystatic.com/BestBuy_US/images/products/9913/9913291.jpg","websiteURL":"http://www.bestbuy.com/site/10-minute-solution-nintendo-wii/9913291.p?id=1218194156063&skuId=9913291","vendor":"Best Buy","description":"Fit fitness into your hectic schedule"},{"name":"101-in-1 Sports Megamix - Nintendo DS","price":"$14.99","rating":"null","image":"http://img.bbystatic.com/BestBuy_US/images/products/1264/1264163.jpg","websiteURL":"http://www.bestbuy.com/site/101-in-1-sports-megamix-nintendo-ds/1264163.p?id=1218245467418&skuId=1264163","vendor":"Best Buy","description":"Dont settle for less &#8212; play more sports than ever"},{"name":"18 Classic Card Games - Nintendo DS","price":"$14.99","rating":"4.0","image":"http://img.bbystatic.com/BestBuy_US/images/products/1610/1610584_sc.jpg","websiteURL":"http://www.bestbuy.com/site/18-classic-card-games-nintendo-ds/1610584.p?id=1218307693304&skuId=1610584","vendor":"Best Buy","description":"The fun of cards meets the Nintendo DS"},{"name":"50 Classic Games - Nintendo DS","price":"$14.99","rating":"4.0","image":"http://img.bbystatic.com/BestBuy_US/images/products/9468/9468701_105x210_sc.jpg","websiteURL":"http://www.bestbuy.com/site/50-classic-games-nintendo-ds/9468701.p?id=1218109898184&skuId=9468701","vendor":"Best Buy","description":"Would you like to play a game?"},{"name":"50 Classic Games 3D - Nintendo 3DS","price":"$19.99","rating":"4.0","image":"http://img.bbystatic.com/BestBuy_US/images/products/7130/7130056_sc.jpg","websiteURL":"http://www.bestbuy.com/site/50-classic-games-3d-nintendo-3ds/7130056.p?id=1218831022937&skuId=7130056","vendor":"Best Buy","description":"Place your odds on fun in a collection of casino games, sports contests, puzzles and more"},{"name":"50 More Classic Games - Nintendo DS","price":"$14.99","rating":"null","image":"http://img.bbystatic.com/BestBuy_US/images/products/2762/2762361_sc.jpg","websiteURL":"http://www.bestbuy.com/site/50-more-classic-games-nintendo-ds/2762361.p?id=1218348992655&skuId=2762361","vendor":"Best Buy","description":"Enjoy hours of fun with all your favorite classic games"},{"name":"8Bitdo - Bluetooth Controller - Gray/Black/Red","price":"$39.99","rating":"null","image":"http://img.bbystatic.com/BestBuy_US/images/products/4670/4670100_sc.jpg","websiteURL":"http://www.bestbuy.com/site/8bitdo-bluetooth-controller-gray-black-red/4670100.p?id=1219794344496&skuId=4670100","vendor":"Best Buy","description":"8BITDO Bluetooth Controller: Compatible with most Android, Apple and Windows devices; Bluetooth connectivity; retro Nintendo style; Ring Stander device stand"},{"name":"8Bitdo - Bluetooth Controller - Gray/Purple/Black","price":"$34.99","rating":"null","image":"http://img.bbystatic.com/BestBuy_US/images/products/4670/4670101_sc.jpg","websiteURL":"http://www.bestbuy.com/site/8bitdo-bluetooth-controller-gray-purple-black/4670101.p?id=1219794340071&skuId=4670101","vendor":"Best Buy","description":"8BITDO Bluetooth Controller: Designed for most Android, Apple and Windows devices; Bluetooth connectivity; Super Nintendo style; Ring Stander device stand"}]}';

        $jsonObject = json_decode($json);
        $tube = $jsonObject->{'searchTerm'};

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
        $searchTerm = $request->request->get('searchQuery');

        //check to see if keyword exists in product table
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Product');
        $product = $repository->findByName($searchTerm);

        if($product != null) //cache hit - return results from mysql table
        {
            return $this->render('default/searchResults.html.twig', array(
                'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
                'searchResults' => $product,
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
