<?php
/**
 * Created by PhpStorm.
 * User: Vova
 * Date: 12.09.2017
 * Time: 17:39
 */
namespace AmazonImages\Services\PAApi;

/**
 * Class PackageDimensions
 * @package App\Services\PAApi
 */
class PackageDimensions
{
    protected $width=null;
    protected $height=null;
    protected $weight=null;
    protected $length=null;

    public function __construct()
    {
    }

    /**
     * @return null|float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return null|float
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return null|float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return null|float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param null|float $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @param null|float $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @param null|float $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @param null|float $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }
}