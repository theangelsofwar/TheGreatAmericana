<?php
namespace AmazonImages\Services\PAApi;

use AmazonImages\Services\PAApi\Exceptions\ApiException;
use SimpleXMLElement;

/**
 * Class ResponseParser
 * @package AmazonImages\Services\PAApi
 */
class ResponseParser
{
    const AWSInvalidParameterValue = 2000;
    const AWSItemNotAccessible = 2001;
    const AWSUnknownError = 2002;
    const AWSItemNotEligibleForCart = 2003;
    const AWSCartItemNotAccessible = 2004;

    protected $response=null;
    protected $locale=null;
    protected $currency_divider=100; // 1 USD = 100 cents, 1 EUR = 100 cents, 1 JPY = 1 JPY

    /**
     * ResponseParser constructor.
     * @param SimpleXMLElement $response
     * @param $locale
     */
    public function __construct(SimpleXMLElement $response,$locale)
    {
        $this->response=$response;
        $this->locale=$locale;
        if($locale==='co.jp'){
            // Japanese currency don't have 1/100 part.
            $this->currency_divider=1;
        }
    }

    /**
     * @return array
     * @throws ApiException
     */

    public function parseProducts()
    {
        $this->checkItemResponseError($this->response);
        $Items = [];
        if (isset($this->response->Items->Item)) {
            if (count($this->response->Items->Item) > 0) {
                foreach ($this->response->Items->Item as $Item) {
                    $Items[] = $Item;
                }
            } else {
                $Items[] = $this->response->Items->Item;
            }
        }
        $products = [];
        foreach ($Items as $itemXml) {
            $itemDimensions=new ItemDimensions();
            $itemDimensions->setWeight($this->getItemWeight($itemXml));
            $itemDimensions->setHeight($this->getItemHeight($itemXml));
            $itemDimensions->setWidth($this->getItemWidth($itemXml));
            $itemDimensions->setLength($this->getItemLength($itemXml));

            $packageDimensions=new PackageDimensions();
            $packageDimensions->setWeight($this->getPackageWeight($itemXml));
            $packageDimensions->setHeight($this->getPackageHeight($itemXml));
            $packageDimensions->setWidth($this->getPackageWidth($itemXml));
            $packageDimensions->setLength($this->getPackageLength($itemXml));

            $product=new Product();
            $product->setAsin((string)$itemXml->ASIN);

            if(!empty($itemXml->ParentASIN)){
                $product->setParentAsin($itemXml->ParentASIN);
            }

            $product->setTitle($this->getTitle($itemXml));
            $product->setProductGroup($this->getProductGroup($itemXml));
            $product->setSmallImageUrl($this->getSmallImageUrl($itemXml));
            $product->setMediumImageUrl($this->getMediumImageUrl($itemXml));
            $product->setLargeImageUrl($this->getLargeImageUrl($itemXml));
            $product->setSalesrank($this->getSalesRank($itemXml));
            $product->setItemDimensions($itemDimensions);
            $product->setPackageDimensions($packageDimensions);

            $product->setTotalNew($this->getTotalNew($itemXml));
            $product->setTotalUsed($this->getTotalUsed($itemXml));
            $product->setBrand($this->getBrand($itemXml));
            $product->setEditorialReview($this->getEditorialReview($itemXml));
            $product->setFeatures($this->getFeatures($itemXml));
            $product->setMpn($this->getMPN($itemXml));
            $product->setColor($this->getColor($itemXml));
            $product->setSize($this->getSize($itemXml));
            $product->setAuthor($this->getAuthor($itemXml));
            $product->setManufacturer($this->getManufacturer($itemXml));
            $product->setSimilarProducts($this->getSimilarProducts($itemXml));
            $product->setMerchantName($this->getMerchantName($itemXml));
            $product->setEan($this->getEAN($itemXml));
            $product->setEanList($this->getEANList($itemXml));
            $product->setUpc($this->getUPC($itemXml));
            $product->setUpcList($this->getUPCList($itemXml));
            $product->setIsbn($this->getISBN($itemXml));
            $product->setOfferListingId($this->getOfferListingId($itemXml));
            $product->setNumberOfPages($this->getNumberOfPages($itemXml));
            $product->setPublicationDate($this->getPublicationDate($itemXml));
            $product->setBrowseNodePaths($this->getBrowseNodePaths($itemXml));
            $product->setCurrency($this->getCurrency($itemXml));

            $product->setLargeImages($this->getImages($itemXml,'LargeImage'));
            $product->setMediumImages($this->getImages($itemXml,'MediumImage'));
            $product->setSmallImages($this->getImages($itemXml,'SmallImage'));

            $product->setVariationsImages($this->getVariationsImages($itemXml));
            $product->setVariationsAsin($this->getVariationsASIN($itemXml));

            $price=null;
            $price = $this->getItemOfferListingPrice($itemXml);
            if (empty($price)){
                $price = $this->getItemOfferSummaryPrice($itemXml);
            }
            if (empty($price)){
                $price = $this->getVariationSummaryLowestPrice($itemXml);
            }
            if(isset($price['Price'])){
                $product->setPrice($price['Price']);
            }
            if(isset($price['SalePrice'])) {
                $product->setSalePrice($price['SalePrice']);
            }
            // availability
            $product->setAvailability($this->getAvailability($itemXml));
            $product->setIsEligibleForPrime($this->getIsEligibleForPrime($itemXml));
            $product->setIsEligibleForSuperSaverShipping($this->getIsEligibleForSuperSaverShipping($itemXml));
            $product->setAvailabilityAttributes($this->getAvailabilityAttributes($itemXml));
            $products[]=$product;
        }
        return $products;
    }


    /**
     * $item - item object from amazon api response.
     */

    public function getItemOfferListingPrice(SimpleXMLElement $item)
    {
        if (!isset($item->Offers) && empty($item->Offers)) {
            return [];
        }
        if (isset($item->Offers->TotalOffers)) {
            if ((int)$item->Offers->TotalOffers > 1) {
                $offer = $item->Offers->Offer[0];
            } elseif ((int)$item->Offers->TotalOffers == 1) {
                $offer = $item->Offers->Offer;
            } else {
                $offer = null;
            }
        } else {
            if (isset($item->Offers->Offer)) {
                $offer = $item->Offers->Offer;
            } else $offer = null;
        }
        if (empty($offer)) return [];

        $price = [];
        $price["Price"] = null;
        $price["SalePrice"] = null;
        $price["Currency"] = null;
        $price["Discount"] = null;


        if (isset($offer->OfferListing)) {
            if (isset($offer->OfferListing->SalePrice) && !empty($offer->OfferListing->SalePrice)) {
                $price["Price"] = round((float)$offer->OfferListing->Price->Amount / $this->currency_divider, 2);
                $price["SalePrice"] = round((float)$offer->OfferListing->SalePrice->Amount / $this->currency_divider, 2);
                $price["Currency"] = (string)$offer->OfferListing->SalePrice->CurrencyCode;
                if (isset($offer->OfferListing->PercentageSaved) && !empty($offer->OfferListing->PercentageSaved)) {
                    $price["Discount"] = (int)$offer->OfferListing->PercentageSaved;
                }
            } else {
                $price["Currency"] = (string)$offer->OfferListing->Price->CurrencyCode;
                $price["Price"] = round((float)$offer->OfferListing->Price->Amount / $this->currency_divider, 2);
                if (isset($offer->OfferListing->PercentageSaved) && !empty($offer->OfferListing->PercentageSaved)) {
                    $price["Discount"] = (int)$offer->OfferListing->PercentageSaved;
                    if (isset($offer->OfferListing->AmountSaved)) {
                        $price["SalePrice"] = round((float)$offer->OfferListing->Price->Amount / $this->currency_divider, 2);
                        $price['Price'] = round(((float)$offer->OfferListing->Price->Amount + (float)$offer->OfferListing->AmountSaved->Amount) / $this->currency_divider, 2);
                    }
                }
            }
        }
        return $price;
    }

    /**
     * @param SimpleXMLElement $item
     * @return array
     */

    public function getItemOfferSummaryPrice(SimpleXMLElement $item)
    {
        $price = [];
        // if offer not exists, then check LowestNewAmount
        if (!isset($item->OfferSummary->LowestNewPrice)) return $price;
        $price["Price"] = round((float)$item->OfferSummary->LowestNewPrice->Amount / $this->currency_divider, 2);
        $price["Currency"] = (string)$item->OfferSummary->LowestNewPrice->CurrencyCode;
        $price["SalePrice"] = null;
        $price["Discount"] = null;
        return $price;
    }

    /**
     * @param SimpleXMLElement $item
     * @return array
     */
    public function getVariationSummaryLowestPrice(SimpleXMLElement $item)
    {
        if (!isset($item->VariationSummary->LowestPrice->Amount)) {
            return [];
        }

        $price = [];
        $price["Price"] = round((float)$item->VariationSummary->LowestPrice->Amount / $this->currency_divider, 2);
        $price["Currency"] = (string)$item->VariationSummary->LowestPrice->CurrencyCode;
        if (isset($item->VariationSummary->LowestSalePrice)) {
            $price["SalePrice"] = round((float)$item->VariationSummary->LowestSalePrice->Amount / $this->currency_divider, 2);
            $price["Discount"] = round(100 - $price["SalePrice"] / $price["Price"] * $this->currency_divider, 0);
        } else {
            $price["SalePrice"] = null;
            $price["Discount"] = null;
        }
        return $price;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    public function getBrand(SimpleXMLElement $item)
    {
        if (!isset($item->ItemAttributes->Brand)) return null;
        else return (string)$item->ItemAttributes->Brand;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    public function getManufacturer(SimpleXMLElement $item)
    {
        if (!isset($item->ItemAttributes->Manufacturer)) return null;
        else return (string)$item->ItemAttributes->Manufacturer;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    public function getMerchantName(SimpleXMLElement $item)
    {
        if (!isset($item->Offers->Offer->Merchant->Name)) {
            return null;
        }
        return (string)$item->Offers->Offer->Merchant->Name;
    }


    /**
     * @param SimpleXMLElement $item
     * @return array
     */
    public function getSimilarProducts(SimpleXMLElement $item)
    {
        if (!isset($item->SimilarProducts->SimilarProduct)) return [];
        $products = [];
        foreach ($item->SimilarProducts->SimilarProduct as $key => $value) {
            $product["ASIN"] = (string)$value->ASIN;
            $product["Title"] = (string)$value->Title;
            $products[] = $product;
        }
        return $products;
    }

    /**
     * @param SimpleXMLElement $item
     * @return int|null
     */
    public function getTotalNew(SimpleXMLElement $item)
    {
        if (empty($item->OfferSummary->TotalNew)) return null;
        return (int)$item->OfferSummary->TotalNew;
    }

    /**
     * @param SimpleXMLElement $item
     * @return int|null
     */
    public function getTotalUsed(SimpleXMLElement $item)
    {
        if (empty($item->OfferSummary->TotalUsed)) return null;
        return (int)$item->OfferSummary->TotalUsed;
    }


    /**
     * @param SimpleXMLElement $response
     * @return bool
     * @throws ApiException
     */
    protected function checkItemResponseError(SimpleXMLElement $response)
    {
        if (!empty($response->Items->Request->Errors->Error)) {
            $mainMessage = '';
            $errors = [];
            if (count($response->Items->Request->Errors->Error) > 1) {
                foreach ($response->Items->Request->Errors->Error as $err) {
                    $errors[] = ['Message' => (string)$err->Message, 'Code' => (string)$err->Code];
                    $mainMessage .= (string)$err->Code . ": " . (string)$err->Message . ", ";
                }
                $mainMessage = trim($mainMessage, ",");
            } else {
                $errors[] = ['Message' => (string)$response->Items->Request->Errors->Error->Message, 'Code' => (string)$response->Items->Request->Errors->Error->Code];
                $mainMessage = (string)$response->Items->Request->Errors->Error->Message;
            }
            $AmazonException = new ApiException($mainMessage, E_USER_WARNING);
            foreach ($errors as $error) {
                $invalidASIN = null;
                switch ($error['Code']) {
                    case "AWS.ECommerceService.ItemNotAccessible":
                        $code = self::AWSItemNotAccessible;
                        if (count($errors) == 1) {
//							if(!empty($response->Items->Request->ItemLookupRequest->IdType) && $response->Items->Request->ItemLookupRequest->IdType=="ASIN"){
                            if (!empty($response->Items->Request->ItemLookupRequest->IdType)) { // asin/ean/upc
                                $invalidASIN = (string)$response->Items->Request->ItemLookupRequest->ItemId;
                            }
                        }
                        break;
                    case "AWS.ECommerceService.InvalidQuantity":
                        return true; // return no error, and check quantity.
                    case "AWS.ECommerceService.ItemNotEligibleForCart":
                        $code = self::AWSItemNotEligibleForCart;
                        break;
                    case "AWS.InvalidParameterValue":
                        $code = self::AWSInvalidParameterValue;
                        if (preg_match('#^(.+)\sis not a valid value for ItemId#siU', $error['Message'], $result)) {
                            $invalidASIN = $result[1];
                        }
                        elseif (preg_match('#^([0-9A-Z]{10}).*ItemId#siU', $error['Message'], $result)) {
                            $invalidASIN = $result[1];
                        }

                        break;
                    default:
                        $code = self::AWSUnknownError;
                }
                $AmazonException->addError($invalidASIN, $error["Message"], $code);
            }
            throw $AmazonException;
        }
        if (isset($response->Error)) {
            $message = (string)$response->Error->Message;
            $invalidASIN = null;
            switch ($response->Error->Code) {
                case "AWS.ECommerceService.ItemNotAccessible":
                    $code = self::AWSItemNotAccessible;
                    break;
                case "AWS.InvalidParameterValue":
                    $code = self::AWSInvalidParameterValue;
                    if (preg_match('#^(.+)\sis not a valid value for ItemId#siU', $message, $result)) {
                        $invalidASIN = trim($result[1]);
                    }
                    elseif (preg_match('#^([0-9A-Z]{10}).*ItemId#siU', $message, $result)) {
                        $invalidASIN = $result[1];
                    }
                    break;
                case "AWS.ECommerceService.InvalidQuantity":
                    return true; // return no error, and check quantity.
                default:
                    var_dump($response->Error);
                    $code = self::AWSUnknownError;
            }
            $AmazonException = new ApiException($message, $code);
            $AmazonException->addError($invalidASIN, $message, $code);
            throw $AmazonException;
        }
        return true;
    }

    /**
     * @param SimpleXMLElement $response
     * @return bool
     * @throws ApiException
     */
    public function checkCartResponseError(SimpleXMLElement $response)
    {
        if (!empty($response->Cart->Request->Errors->Error)) {
            $mainMessage = '';
            $errors = [];
            if (count($response->Cart->Request->Errors->Error) > 1) {
                foreach ($response->Cart->Request->Errors->Error as $err) {
                    $errors[] = ['Message' => (string)$err->Message, 'Code' => (string)$err->Code];
                    $mainMessage .= (string)$err->Code . ": " . (string)$err->Message . ", ";
                }
                $mainMessage = trim($mainMessage, ",");
            } else {
                $errors[] = ['Message' => (string)$response->Cart->Request->Errors->Error->Message, 'Code' => (string)$response->Cart->Request->Errors->Error->Code];
                $mainMessage = (string)$response->Cart->Request->Errors->Error->Message;
            }
            $AmazonException = new ApiException($mainMessage, E_USER_WARNING);
            foreach ($errors as $error) {
                $invalidASIN = null;
                switch ($error['Code']) {
                    case "AWS.ECommerceService.ItemNotAccessible":
                        $code = self::AWSCartItemNotAccessible;
                        $AmazonException->addError($invalidASIN, $error["Message"], $code);
                        break;
                    case "AWS.InvalidParameterValue":
                        $code = self::AWSInvalidParameterValue;
                        if (preg_match('#^(.+)\sis not a valid value for ItemId#siU', $error['Message'], $result)) {
                            $invalidASIN = $result[1];
                        }
                        elseif (preg_match('#^([0-9A-Z]{10}).*ItemId#siU', $error['Message'], $result)) {
                            $invalidASIN = $result[1];
                        }
                        $AmazonException->addError($invalidASIN, $error["Message"], $code);
                        break;
                    case "AWS.ECommerceService.InvalidQuantity":
                        // will not add to error array
                        break;
                    case "AWS.ECommerceService.ItemNotEligibleForCart":
                        $code = self::AWSItemNotEligibleForCart;
                        if (preg_match('#The item you specified, (.+), is not eligible to be added to the cart.#siU', $error['Message'], $result)) {
                            $invalidASIN = $result[1];
                        }
                        $AmazonException->addError($invalidASIN, $error["Message"], $code);
                        break;
                    default:
                        $code = self::AWSUnknownError;
                        $AmazonException->addError($invalidASIN, $error["Message"], $code);
                }

            }
            if (!empty($AmazonException->getErrors())) {
                throw $AmazonException;
            }
            return true;
        }

        if (isset($response->Error)) {
            switch ($response->Error->Code) {
                case "AWS.ECommerceService.ItemNotAccessible":
                    $code = self::AWSCartItemNotAccessible;
                    break;
                case "AWS.InvalidParameterValue":
                    $code = self::AWSInvalidParameterValue;
                    break;
                case "AWS.ECommerceService.InvalidQuantity":
                    return true; // return no error, and check quantity.
                default:
                    var_dump($response->Error);
                    $code = self::AWSUnknownError;
            }
            throw new ApiException((string)$response->Error->Message, $code);
        }
        return true;
    }

    protected function getEditorialReview(SimpleXMLElement $item)
    {
        if (empty($item->EditorialReviews)) {
            return null;
        }
        if (count($item->EditorialReviews->EditorialReview) > 1) {
            $editorialReview = $item->EditorialReviews->EditorialReview[0];
        } else {
            $editorialReview = $item->EditorialReviews->EditorialReview;
        }
        return (string)$editorialReview->Content;
    }

    protected function getEAN(SimpleXMLElement $item)
    {
        return !empty($item->ItemAttributes->EAN) ? (string)$item->ItemAttributes->EAN : null;
    }

    /**
     * @param SimpleXMLElement $item
     * @return array
     */
    protected function getEANList(SimpleXMLElement $item)
    {
        return !empty($item->ItemAttributes->EANList) ? (array)$item->ItemAttributes->EANList->EANListElement : [];
    }

    protected function getUPC(SimpleXMLElement $item)
    {
        return !empty($item->ItemAttributes->UPC) ? (string)$item->ItemAttributes->UPC : null;
    }

    /**
     * @param SimpleXMLElement $item
     * @return array
     */
    protected function getUPCList(SimpleXMLElement $item)
    {
        return !empty($item->ItemAttributes->UPCList) ? (array)$item->ItemAttributes->UPCList->UPCListElement : [];
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getISBN(SimpleXMLElement $item)
    {
        if (empty($item->ItemAttributes->ISBN)) return null;
        return (string)$item->ItemAttributes->ISBN;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getAuthor(SimpleXMLElement $item)
    {
        if (empty($item->ItemAttributes->Author)) return null;
        return (string)$item->ItemAttributes->Author;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getNumberOfPages(SimpleXMLElement $item)
    {
        if (empty($item->ItemAttributes->NumberOfPages)) return null;
        return (string)$item->ItemAttributes->NumberOfPages;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getTitle(SimpleXMLElement $item)
    {
        if (empty($item->ItemAttributes->Title)) return null;
        return (string)$item->ItemAttributes->Title;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getProductGroup(SimpleXMLElement $item)
    {
        if (empty($item->ItemAttributes->ProductGroup)) return null;
        return (string)$item->ItemAttributes->ProductGroup;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getMPN(SimpleXMLElement $item)
    {
        if (empty($item->ItemAttributes->MPN)) return null;
        return (string)$item->ItemAttributes->MPN;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getColor(SimpleXMLElement $item)
    {
        return !empty($item->ItemAttributes->Color) ? (string)$item->ItemAttributes->Color : null;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getSize(SimpleXMLElement $item)
    {
        return !empty($item->ItemAttributes->Size) ? (string)$item->ItemAttributes->Size : null;
    }

    /**
     * @param SimpleXMLElement $item
     * @return int|null
     */
    protected function getSalesRank(SimpleXMLElement $item)
    {
        if (!empty($item->SalesRank)) return (int)$item->SalesRank;
        return null;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getSmallImageUrl(SimpleXMLElement $item)
    {
        if (!empty($item->SmallImage->URL)) return (string)$item->SmallImage->URL;
        return null;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getMediumImageUrl(SimpleXMLElement $item)
    {
        if (!empty($item->MediumImage->URL)) return (string)$item->MediumImage->URL;
        return null;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getLargeImageUrl(SimpleXMLElement $item)
    {
        if (!empty($item->LargeImage->URL)) return (string)$item->LargeImage->URL;
        return null;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getPublicationDate(SimpleXMLElement $item)
    {
        if (empty($item->ItemAttributes->PublicationDate)) return null;
        return (string)$item->ItemAttributes->PublicationDate;
    }

    /**
     * @param SimpleXMLElement $item
     * @return array
     */
    protected function getBrowseNodePaths(SimpleXMLElement $item)
    {
        if (empty($item->BrowseNodes)) return [];
        $BrowseNodes = $item->BrowseNodes;
        $BrowseNodePaths = [];
        $childnodes = $BrowseNodes->children();
        foreach ($childnodes as $nodepath) {
            $isCategoryRoot = false;
            $path = [];
            if (isset($nodepath->IsCategoryRoot) && $nodepath->IsCategoryRoot === true) $isCategoryRoot = true;
            array_push($path, array("Name" => (string)$nodepath->Name, "BrowseNodeId" => (string)$nodepath->BrowseNodeId));
            $ancestors = $nodepath->Ancestors;
            while (true) {
                if (isset($ancestors->BrowseNode->IsCategoryRoot) && (bool)$ancestors->BrowseNode->IsCategoryRoot === true) {
                    $isCategoryRoot = true;
                } else {
                    if (isset($ancestors->BrowseNode->Name)) {
                        array_push($path, array("Name" => (string)$ancestors->BrowseNode->Name, "BrowseNodeId" => (string)$ancestors->BrowseNode->BrowseNodeId));
                    } else {
                        array_push($path, array("Name" => "", "BrowseNodeId" => (string)$ancestors->BrowseNode->BrowseNodeId));
                    }
                }
                if (isset($ancestors->BrowseNode->Ancestors) && is_object($ancestors->BrowseNode->Ancestors)) $ancestors = $ancestors->BrowseNode->Ancestors;
                else break;
            }
            //$BrowseNodePaths[]=array_reverse($path);
            $BrowseNodePaths[] = $path;
        }
        /* 	return first path from many */
        //if(is_array($BrowseNodePaths[0])) return array_reverse($BrowseNodePaths[0]);
        //return [];
        return $BrowseNodePaths;
    }


    /**
     * @param SimpleXMLElement $item
     * @return float|null
     * return weight in lbs as float
     */
    protected function getItemWeight(SimpleXMLElement $item, $UnitsReturned = 'lbs')
    {
        // if ItemDimensions is not set, then check Package Dimensions;
        if (empty($item->ItemAttributes->ItemDimensions->Weight)) {
            return null;
        }
        return $this->parseWeightElement($item->ItemAttributes->ItemDimensions->Weight, $UnitsReturned);
    }

    /**
     * @param SimpleXMLElement $item
     * @param string $UnitsReturned
     * @return float|null
     */
    protected function getPackageWeight(SimpleXMLElement $item, $UnitsReturned = 'lbs')
    {
        if (empty($item->ItemAttributes->PackageDimensions->Weight)) {
            return null;
        }
        return $this->parseWeightElement($item->ItemAttributes->PackageDimensions->Weight, $UnitsReturned);
    }

    /**
     * @param SimpleXMLElement $WeightElement
     * @param string $UnitsReturned
     * @return float
     */
    protected function parseWeightElement(SimpleXMLElement $WeightElement, $UnitsReturned = 'lbs')
    {
        $weight = (int)$WeightElement;
        $attributes = $WeightElement->attributes();
        $units = null;
        foreach ($attributes as $key => $value) {
            if (strtolower($key) == "units") {
                $units = (string)$value;
            }
        }
        switch ($units) {
            case "pounds":
                break;
            case "hundredths-pounds":
            default:
                $weight = $weight / 100; // convert to lbs
        }

        switch ($UnitsReturned) {
            case 'g':
                $weight = round($weight * 0.45359237 * 1000, 0); // convert to gramm
                break;
            case 'kg':
                $weight = round($weight * 0.45359237, 2); // convert to kg
                break;
            case 'lbs':
            default:
                $weight = round($weight, 2);
            // lbs is default. no action.
        }

        return (float)$weight;
    }

    /**
     * @param SimpleXMLElement $item
     * @param string $UnitsReturned
     * @return float|null
     */
    protected function getItemHeight(SimpleXMLElement $item, $UnitsReturned = 'inch')
    {
        // if ItemDimensions is not set, then check Package Dimensions;
        if (empty($item->ItemAttributes->ItemDimensions->Height)) {
            return null;
        }
        return $this->parseDimensionElement($item->ItemAttributes->ItemDimensions->Height, $UnitsReturned);
    }

    /**
     * @param SimpleXMLElement $item
     * @param string $UnitsReturned
     * @return float|null
     */
    protected function getItemWidth(SimpleXMLElement $item, $UnitsReturned = 'inch')
    {
        // if ItemDimensions is not set, then check Package Dimensions;
        if (empty($item->ItemAttributes->ItemDimensions->Width)) {
            return null;
        }
        return $this->parseDimensionElement($item->ItemAttributes->ItemDimensions->Width, $UnitsReturned);
    }

    /**
     * @param SimpleXMLElement $item
     * @param string $UnitsReturned
     * @return float|null
     */
    protected function getItemLength(SimpleXMLElement $item, $UnitsReturned = 'inch')
    {
        // if ItemDimensions is not set, then check Package Dimensions;
        if (empty($item->ItemAttributes->ItemDimensions->Length)) {
            return null;
        }
        return $this->parseDimensionElement($item->ItemAttributes->ItemDimensions->Length, $UnitsReturned);
    }

    /**
     * @param SimpleXMLElement $item
     * @param string $UnitsReturned
     * @return float|null
     */
    protected function getPackageHeight(SimpleXMLElement $item, $UnitsReturned = 'inch')
    {
        // if ItemDimensions is not set, then check Package Dimensions;
        if (empty($item->ItemAttributes->PackageDimensions->Height)) {
            return null;
        }
        return $this->parseDimensionElement($item->ItemAttributes->PackageDimensions->Height, $UnitsReturned);
    }

    /**
     * @param SimpleXMLElement $item
     * @param string $UnitsReturned
     * @return float|null
     */
    protected function getPackageWidth(SimpleXMLElement $item, $UnitsReturned = 'inch')
    {
        // if ItemDimensions is not set, then check Package Dimensions;
        if (empty($item->ItemAttributes->PackageDimensions->Width)) {
            return null;
        }
        return $this->parseDimensionElement($item->ItemAttributes->PackageDimensions->Width, $UnitsReturned);
    }

    /**
     * @param SimpleXMLElement $item
     * @param string $UnitsReturned
     * @return float|null
     */
    protected function getPackageLength(SimpleXMLElement $item, $UnitsReturned = 'inch')
    {
        // if ItemDimensions is not set, then check Package Dimensions;
        if (empty($item->ItemAttributes->PackageDimensions->Length)) {
            return null;
        }
        return $this->parseDimensionElement($item->ItemAttributes->PackageDimensions->Length, $UnitsReturned);
    }

    /**
     * @param SimpleXMLElement $DimensionElement
     * @param string $UnitsReturned
     * @return float
     */
    protected function parseDimensionElement(SimpleXMLElement $DimensionElement, $UnitsReturned = 'inch')
    {
        $dimension = (int)$DimensionElement;
        $attributes = $DimensionElement->attributes();
        $units = null;
        foreach ($attributes as $key => $value) {
            if (strtolower($key) == "units") {
                $units = (string)$value;
            }
        }
        switch ($units) {
            case "inches":
                break;
            case "hundredths-inches":
            default:
                $dimension = $dimension / 100; // convert to lbs
        }

        switch ($UnitsReturned) {
            case 'cm':
                $dimension = round($dimension * 2.54, 2); // convert to centimeters
                break;
            case 'inch':
            default:
                $dimension = round($dimension, 2);
            // lbs is default. no action.
        }
        return (float)$dimension;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getCurrency(SimpleXMLElement $item)
    {
        switch ($this->locale) {
            case "com":
                return "USD";
            case "co.uk":
                return "GBP";
            case "de":
            case "fr":
            case "it":
            case "es":
                return "EUR";
            case "ca":
                return "CAD";
            default:
                return null;
        }
    }

    /**
     * @param SimpleXMLElement $item
     * @return array
     */
    protected function getFeatures(SimpleXMLElement $item)
    {
        if (!empty($item->ItemAttributes->Feature)) {
            if (count($item->ItemAttributes->Feature) > 1) {
                return (array)$item->ItemAttributes->Feature;
            } else {
                return [(string)$item->ItemAttributes->Feature];
            }
        }
        return [];
    }

    /**
     * @param $item
     * @return null
     */
    protected function getFirstOffer($item){
        if (empty($item->Offers)) return null;

        if (!empty($item->Offers->TotalOffers)) {
            if ((int)$item->Offers->TotalOffers > 1) {
                $offer = $item->Offers->Offer[0];
            } elseif ((int)$item->Offers->TotalOffers == 1) {
                $offer = $item->Offers->Offer;
            } else {
                $offer = null;
            }
        } else {
            if (!empty($item->Offers->Offer)) {
                if (count($item->Offers->Offer) > 1) {
                    $offer = $item->Offers->Offer[0];
                } else {
                    $offer = $item->Offers->Offer;
                }
            } else $offer = null;
        }
        return $offer;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null|string
     */
    protected function getOfferListingId(SimpleXMLElement $item)
    {
        $offer=$this->getFirstOffer($item);
        if (empty($offer->OfferListing->OfferListingId)) {
            return null;
        }
        return (string)$offer->OfferListing->OfferListingId;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null
     */
    protected function getIsEligibleForPrime(SimpleXMLElement $item)
    {
        $offer=$this->getFirstOffer($item);
        return isset($offer->OfferListing->IsEligibleForPrime)?(string)$offer->OfferListing->IsEligibleForPrime:null;
    }

    /**
     * @param SimpleXMLElement $item
     * @return null
     */
    protected function getIsEligibleForSuperSaverShipping(SimpleXMLElement $item)
    {
        $offer=$this->getFirstOffer($item);
        return isset($offer->OfferListing->IsEligibleForSuperSaverShipping)?(string)$offer->OfferListing->IsEligibleForSuperSaverShipping:null;
    }


    protected function getAvailability(SimpleXMLElement $item)
    {
        $offer=$this->getFirstOffer($item);
        return isset($offer->OfferListing->Availability)?(string)$offer->OfferListing->Availability:null;
    }

    /**
     * @param SimpleXMLElement $item
     * @return AvailabilityAttributes
     */
    protected function getAvailabilityAttributes(SimpleXMLElement $item)
    {
        $offer=$this->getFirstOffer($item);
        $availabilityAttributes=new AvailabilityAttributes();
        if(isset($offer->OfferListing->AvailabilityAttributes)){
            $attributes=$offer->OfferListing->AvailabilityAttributes;
            if(isset($attributes->AvailabilityType)){
                $availabilityAttributes->setAvailabilityType((string)$attributes->AvailabilityType);
            }
            if(isset($attributes->MaximumHours)) {
                $availabilityAttributes->setMaximumHours((string)$attributes->MaximumHours);
            }
            if(isset($attributes->MinimumHours)) {
                $availabilityAttributes->setMinimumHours((string)$attributes->MinimumHours);
            }
        }
        return $availabilityAttributes;
    }

    /**
     * @return array
     */
    public function parseProductsStockInfo()
    {
        //$this->checkCartResponseError($this->response);
        $Items = [];
        if (!empty($this->response->Cart->CartItems->CartItem)) {
            if (count($this->response->Cart->CartItems->CartItem) > 0) {
                foreach ($this->response->Cart->CartItems->CartItem as $Item) {
                    $Items[] = $Item;
                }
            } else {
                $Items[] = $this->response->Cart->CartItems->CartItem;
            }
        }
        if (!empty($this->response->Cart->SavedForLaterItems->SavedForLaterItem)) {
            if (count($this->response->Cart->SavedForLaterItems->SavedForLaterItem) > 0) {
                foreach ($this->response->Cart->SavedForLaterItems->SavedForLaterItem as $Item) {
                    $Items[] = $Item;
                }
            } else {
                $Items[] = $this->response->Cart->SavedForLaterItems->SavedForLaterItem;
            }
        }

        $stocks = [];
        foreach ($Items as $Item) {
            $product=new Product();
            $product->setAsin((string)$Item->ASIN);
            $product->setQuantity((int)$Item->Quantity);
            $product->setPrice((float)round($Item->Price->Amount / $this->currency_divider, 2));
            $product->setCurrency((string)$Item->Price->CurrencyCode);
            $stocks[]=$product;
        }
        return $stocks;
    }

    /**
     * @param SimpleXMLElement $item
     * @param string $ImageSize
     * @return array
     */
    protected function getVariationsImages(SimpleXMLElement $item, $ImageSize = 'LargeImage')
    {
        if (empty($item->Variations)) {
            return [];
        }

        if ($item->Variations->TotalVariations > 1) {
            $vitems = (array)$item->Variations->Item;
        } elseif ($item->Variations->TotalVariations == 1) {
            $vitems = [$item->Variations->Item];
        } else {
            return [];
        }

        $images = [];
        foreach ($vitems as $vitem) {
            if (!empty($vitem->ImageSets->ImageSet)) {
                if (count($vitem->ImageSets->ImageSet) > 1) {
                    $primary = null;
                    foreach ($vitem->ImageSets->ImageSet as $ImageSet) {
                        $attr = $ImageSet->attributes();
                        if (isset($attr["Category"]) && $attr["Category"] == "primary") {
                            $primary = (string)$ImageSet->$ImageSize->URL;
                        } else {
                            $images[] = (string)$ImageSet->$ImageSize->URL;
                        }
                    }
                    array_unshift($images, $primary);
                } else {
                    $images[] = (string)$vitem->ImageSets->ImageSet->$ImageSize->URL;
                }
            } elseif (!empty($vitem->$ImageSize)) {
                $images[] = (string)$vitem->$ImageSize->URL;
            }
        }
        return $images;
    }

    /**
     * @param SimpleXMLElement $item
     * @param string $ImageSize
     * @return array
     */
    protected function getImages(SimpleXMLElement $item, $ImageSize = 'LargeImage')
    {
        $images = [];
        if (!empty($item->ImageSets->ImageSet)) {
            if (count($item->ImageSets->ImageSet) > 1) {
                $primary = null;
                foreach ($item->ImageSets->ImageSet as $ImageSet) {
                    $attr = $ImageSet->attributes();
                    if (isset($attr["Category"]) && $attr["Category"] == "primary") {
                        $primary = (string)$ImageSet->$ImageSize->URL;
                    } else {
                        $images[] = (string)$ImageSet->$ImageSize->URL;
                    }
                }
                if(!empty($primary)){
                    array_unshift($images, $primary);
                }
            } else {
                $images[] = (string)$item->ImageSets->ImageSet->$ImageSize->URL;
            }
        } elseif (!empty($item->$ImageSize)) {
            $images[] = (string)$item->$ImageSize->URL;
        }

        return array_values(array_unique($images));
    }

    /**
     * @param SimpleXMLElement $item
     * @return array
     */
    protected function getVariationsASIN(SimpleXMLElement $item)
    {
        if (empty($item->Variations)) {
            return [];
        }

        if ($item->Variations->TotalVariations > 1) {
            $vitems = $item->Variations->Item;
        } elseif ($item->Variations->TotalVariations == 1) {
            $vitems = [$item->Variations->Item];
        } else {
            return [];
        }

        $list = [];
        foreach ($vitems as $vitem) {
            if (!empty($vitem->ASIN)) {
                $list[] = (string)$vitem->ASIN;
            }
        }
        return $list;
    }
}