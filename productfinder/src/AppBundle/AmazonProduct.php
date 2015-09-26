<?php

namespace AppBundle;

class AmazonProduct extends Product {
	protected $source = 'Amazon';
	protected $productName = 'Some Name';
	protected $productDescription = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus consectetur rutrum est. Praesent fringilla turpis et mi condimentum pretium. Etiam ac diam luctus, laoreet diam a, bibendum mi. Maecenas ipsum purus, hendrerit quis ultricies in, sagittis eget odio. Aliquam ac scelerisque lacus. Quisque aliqua';
	protected $productPrice = '$5.00';

	public function getProductName()
	{
		return $this->productName;
	}

	public function getProductDescription()
	{
		return $this->productDescription;
	}

	public function getProductPrice()
	{
		return $this->productPrice;
	}

	public function getSource()
	{
		return $this->source;
	}
}