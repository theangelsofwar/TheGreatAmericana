<?php
/**
 * Created by PhpStorm.
 * User: Vova
 * Date: 12.09.2017
 * Time: 17:39
 */

namespace AmazonImages\Services\PAApi;

/**
 * Class AvailabilityAttributes
 * @package AmazonImages\Services\PAApi
 */
class AvailabilityAttributes
{
    protected $availability_type = null;
    protected $maximum_hours = null;
    protected $minimum_hours = null;

    public function __construct()
    {
    }

    /**
     * @return null
     */
    public function getAvailabilityType()
    {
        return $this->availability_type;
    }

    /**
     * @param $value
     */
    public function setAvailabilityType($value)
    {
        $this->availability_type = $value;
    }

    /**
     * @return null
     */
    public function getMaximumHours()
    {
        return $this->maximum_hours;
    }

    /**
     * @param $value
     */
    public function setMaximumHours($value)
    {
        $this->maximum_hours = $value;
    }

    /**
     * @return null
     */
    public function getMinimumHours()
    {
        return $this->minimum_hours;
    }

    /**
     * @param $value
     */
    public function setMinimumHours($value)
    {
        $this->minimum_hours = $value;
    }

}