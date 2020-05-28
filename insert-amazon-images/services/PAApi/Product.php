<?php
/**
 * Created by PhpStorm.
 * User: Vova
 * Date: 12.09.2017
 * Time: 17:31
 */

namespace AmazonImages\Services\PAApi;

/**
 * Class Product
 * @package AmazonImages\Services\PAApi
 */
class Product
{
    protected $asin = null;
    protected $parent_asin=null;
    protected $title = null;
    protected $product_group = null;
    protected $small_image_url = null;
    protected $medium_image_url = null;
    protected $large_image_url = null;
    protected $salesrank = null;
    protected $item_dimensions = null;
    protected $package_dimensions = null;
    protected $total_new = null;
    protected $total_used = null;
    protected $brand = null;
    protected $editorial_review = null;
    protected $features = [];
    protected $mpn = null;
    protected $color = null;
    protected $size = null;
    protected $author = null;
    protected $manufacturer = null;
    protected $similar_products = null;
    protected $merchant_name = null;
    protected $ean = null;
    protected $ean_list = [];
    protected $upc = null;
    protected $upc_list = [];
    protected $isbn = null;
    protected $offer_listing_id = null;
    protected $number_of_pages = null;
    protected $publication_date = null;
    protected $browsenode_paths = [];
    protected $currency = null;
    protected $large_images = [];
    protected $medium_images = [];
    protected $small_images = [];
    protected $variations_images = [];
    protected $variations_asin = [];
    protected $price = null;
    protected $sale_price = null;
    protected $availability = null;
    protected $is_eligible_for_prime = null;
    protected $is_eligible_for_super_saver_shipping = null;
    protected $availability_attributes = null;
    protected $stock = null;
    protected $quantity = null;


    /**
     * @return null|int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return null|int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param null|int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @param null|int $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    /**
     * @param null|string $product_group
     */
    public function setProductGroup($product_group)
    {
        $this->product_group = $product_group;
    }

    /**
     * @return null|string
     */
    public function getAsin()
    {
        return $this->asin;
    }

    /**
     * @return null|string
     */
    public function getParentAsin()
    {
        return $this->parent_asin;
    }

    /**
     * @return null|string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return null|string
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @return null|AvailabilityAttributes
     */
    public function getAvailabilityAttributes()
    {
        return $this->availability_attributes;
    }

    /**
     * @return null|string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return array
     */
    public function getBrowsenodePaths()
    {
        return $this->browsenode_paths;
    }

    /**
     * @return null|string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return null|string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return null|string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @return array
     */
    public function getEanList()
    {
        return $this->ean_list;
    }

    /**
     * @return null|string
     */
    public function getEditorialReview()
    {
        return $this->editorial_review;
    }

    /**
     * @return array
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @return array
     */
    public function getLargeImages()
    {
        return $this->large_images;
    }

    /**
     * @return array
     */
    public function getMediumImages()
    {
        return $this->medium_images;
    }

    /**
     * @return array
     */
    public function getSmallImages()
    {
        return $this->small_images;
    }

    /**
     * @return null|string
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * @return null|string
     */
    public function getIsEligibleForPrime()
    {
        return $this->is_eligible_for_prime;
    }

    /**
     * @return null|string
     */
    public function getIsEligibleForSuperSaverShipping()
    {
        return $this->is_eligible_for_super_saver_shipping;
    }

    /**
     * @return ItemDimensions
     */
    public function getItemDimensions()
    {
        return $this->item_dimensions;
    }

    /**
     * @return null|string
     */
    public function getLargeImageUrl()
    {
        return $this->large_image_url;
    }

    /**
     * @return null|string
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @return null|string
     */
    public function getMediumImageUrl()
    {
        return $this->medium_image_url;
    }

    /**
     * @return null|string
     */
    public function getMerchantName()
    {
        return $this->merchant_name;
    }

    /**
     * @return null|string
     */
    public function getMpn()
    {
        return $this->mpn;
    }

    /**
     * @return null|string
     */
    public function getNumberOfPages()
    {
        return $this->number_of_pages;
    }

    /**
     * @return null|string
     */
    public function getOfferListingId()
    {
        return $this->offer_listing_id;
    }

    /**
     * @return PackageDimensions
     */
    public function getPackageDimensions()
    {
        return $this->package_dimensions;
    }

    /**
     * @return null|float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return null|string
     */
    public function getProductGroup()
    {
        return $this->product_group;
    }

    /**
     * @return null|string
     */
    public function getPublicationDate()
    {
        return $this->publication_date;
    }

    /**
     * @return null|float
     */
    public function getSalePrice()
    {
        return $this->sale_price;
    }

    /**
     * @return null|int
     */
    public function getSalesrank()
    {
        return $this->salesrank;
    }

    /**
     * @return null
     */
    public function getSimilarProducts()
    {
        return $this->similar_products;
    }

    /**
     * @return null|string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return null|string
     */
    public function getSmallImageUrl()
    {
        return $this->small_image_url;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return null|int
     */
    public function getTotalNew()
    {
        return $this->total_new;
    }

    /**
     * @return null|int
     */
    public function getTotalUsed()
    {
        return $this->total_used;
    }

    /**
     * @return null|string
     */
    public function getUpc()
    {
        return $this->upc;
    }

    /**
     * @return array
     */
    public function getUpcList()
    {
        return $this->upc_list;
    }

    /**
     * @return array
     */
    public function getVariationsAsin()
    {
        return $this->variations_asin;
    }

    /**
     * @return array
     */
    public function getVariationsImages()
    {
        return $this->variations_images;
    }

    /**
     * @param null|string $asin
     */
    public function setAsin($asin)
    {
        $this->asin = $asin;
    }

    /**
     * @param null|string $asin
     */
    public function setParentAsin($value)
    {
        $this->parent_asin = $value;
    }


    /**
     * @param null|string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @param null|string $availability
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;
    }

    /**
     * @param null|AvailabilityAttributes $availability_attributes
     */
    public function setAvailabilityAttributes($availability_attributes)
    {
        $this->availability_attributes = $availability_attributes;
    }

    /**
     * @param null|string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @param array $browsenode_paths
     */
    public function setBrowsenodePaths(array $browsenode_paths)
    {
        $this->browsenode_paths = $browsenode_paths;
    }

    /**
     * @param null|string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @param null|string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @param null|string $ean
     */
    public function setEan($ean)
    {
        $this->ean = $ean;
    }

    /**
     * @param array $ean_list
     */
    public function setEanList(array $ean_list)
    {
        $this->ean_list = $ean_list;
    }

    /**
     * @param null|string $editorial_reviews
     */
    public function setEditorialReview($editorial_review)
    {
        $this->editorial_review = $editorial_review;
    }

    /**
     * @param array $features
     */
    public function setFeatures(array $features)
    {
        $this->features = $features;
    }

    /**
     * @param array $images
     */
    public function setLargeImages(array $images)
    {
        $this->large_images = $images;
    }

    /**
     * @param array $images
     */
    public function setMediumImages(array $images)
    {
        $this->medium_images = $images;
    }

    /**
     * @param array $images
     */
    public function setSmallImages(array $images)
    {
        $this->small_images = $images;
    }

    /**
     * @param null|string $isbn
     */
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
    }

    /**
     * @param null|string $is_eligible_for_prime
     */
    public function setIsEligibleForPrime($is_eligible_for_prime)
    {
        $this->is_eligible_for_prime = $is_eligible_for_prime;
    }

    /**
     * @param null|string $is_eligible_for_super_saver_shipping
     */
    public function setIsEligibleForSuperSaverShipping($is_eligible_for_super_saver_shipping)
    {
        $this->is_eligible_for_super_saver_shipping = $is_eligible_for_super_saver_shipping;
    }

    /**
     * @param ItemDimensions $item_dimensions
     */
    public function setItemDimensions(ItemDimensions $item_dimensions)
    {
        $this->item_dimensions = $item_dimensions;
    }

    /**
     * @param null|string $large_image_url
     */
    public function setLargeImageUrl($large_image_url)
    {
        $this->large_image_url = $large_image_url;
    }

    /**
     * @param null|string $manufacturer
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @param null|string $medium_image_url
     */
    public function setMediumImageUrl($medium_image_url)
    {
        $this->medium_image_url = $medium_image_url;
    }

    /**
     * @param null|string $merchant_name
     */
    public function setMerchantName($merchant_name)
    {
        $this->merchant_name = $merchant_name;
    }

    /**
     * @param null|string $mpn
     */
    public function setMpn($mpn)
    {
        $this->mpn = $mpn;
    }

    /**
     * @param null|int $number_of_pages
     */
    public function setNumberOfPages($number_of_pages)
    {
        $this->number_of_pages = $number_of_pages;
    }

    /**
     * @param null|string $offer_listing_id
     */
    public function setOfferListingId($offer_listing_id)
    {
        $this->offer_listing_id = $offer_listing_id;
    }

    /**
     * @param PackageDimensions $package_dimensions
     */
    public function setPackageDimensions(PackageDimensions $package_dimensions)
    {
        $this->package_dimensions = $package_dimensions;
    }

    /**
     * @param null|float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @param null|string $publication_date
     */
    public function setPublicationDate($publication_date)
    {
        $this->publication_date = $publication_date;
    }

    /**
     * @param null|float $sale_price
     */
    public function setSalePrice($sale_price)
    {
        $this->sale_price = $sale_price;
    }

    /**
     * @param null|int $salesrank
     */
    public function setSalesrank($salesrank)
    {
        $this->salesrank = $salesrank;
    }

    /**
     * @param null $similar_products
     */
    public function setSimilarProducts($similar_products)
    {
        $this->similar_products = $similar_products;
    }

    /**
     * @param null|string $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @param null|string $small_image_url
     */
    public function setSmallImageUrl($small_image_url)
    {
        $this->small_image_url = $small_image_url;
    }

    /**
     * @param null|string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param null|int $total_new
     */
    public function setTotalNew($total_new)
    {
        $this->total_new = $total_new;
    }

    /**
     * @param null|int $total_used
     */
    public function setTotalUsed($total_used)
    {
        $this->total_used = $total_used;
    }

    /**
     * @param null|string $upc
     */
    public function setUpc($upc)
    {
        $this->upc = $upc;
    }

    /**
     * @param null $upc_list
     */
    public function setUpcList(array $upc_list)
    {
        $this->upc_list = $upc_list;
    }

    /**
     * @param array $variations_asin
     */
    public function setVariationsAsin(array $variations_asin)
    {
        $this->variations_asin = $variations_asin;
    }

    /**
     * @param array $variations_images
     */
    public function setVariationsImages(array $variations_images)
    {
        $this->variations_images = $variations_images;
    }
}