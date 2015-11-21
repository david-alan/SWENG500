<?php
namespace AppBundle\Services;

use AppBundle\Entity\Product;
use AppBundle\Controller\DefaultController;

class CacheService extends DefaultController
{
    public function addCache($json)
    {
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
        }

    }
}