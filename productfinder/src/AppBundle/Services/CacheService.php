<?php
namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Product;
use AppBundle\Controller\DefaultController;
use Symfony\Component\DependencyInjection\ContainerAware;

class CacheService extends ContainerAware
{
    private $logger;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function addCache($json)
    {

//        $this->logger->error('An error occurred');

        $jsonObject = json_decode($json);
        $products   = $jsonObject->{'results'};
        $searchTerm = $jsonObject->{'searchTerm'};

        for($i=0; $i< count($products); $i++) {
            $product = new Product();
            $product->setSearchTerm($searchTerm);
            $product->setName($products[$i]->name);
            $product->setImage($products[$i]->image);
            $product->setPrice(preg_replace("/[^0-9,.]/", "",$products[$i]->price));
            $product->setRating($products[$i]->rating);
            $product->setDescription($products[$i]->description);
            $product->setVendor($products[$i]->vendor);
            $product->setWebsiteURL($products[$i]->websiteURL);

            $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
            //$em = $this->em->getDoctrine()->getManager();
/*            $em->persist($product);
            $em->flush();
*/
        }

    }

    public function addLogger($logger)
    {
        $this->logger = $logger;
    }
}