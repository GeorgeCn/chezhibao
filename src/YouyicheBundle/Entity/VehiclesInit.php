<?php

namespace YouyicheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * 车型库，该entity是copy自youyiche项目的
 * VehiclesInit
 *
 * @ORM\Table(name="vehicles_init", indexes={
 *      @ORM\Index(name="search_idx", columns={"level_id", "brand", "series", "brand_letter"}),
 *      @ORM\Index(name="IDX_BR_SE_BR", columns={"brand", "series", "brand_letter"})
 * })
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class VehiclesInit
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="level_id", type="string", length=255)
     */
    private $levelId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="vender", type="string", length=30, nullable=true)
     */
    private $vender;
    
    /**
     * @var string
     *
     * @ORM\Column(name="brand", type="string", length=30)
     */
    private $brand;
    
    /**
     * @var string
     *
     * @ORM\Column(name="brand_letter", type="string", length=30)
     */
    private $brandLetter;
    
    /**
     * @var string
     *
     * @ORM\Column(name="series", type="string", length=30)
     */
    private $series;
    
    /**
     * @var string
     *
     * @ORM\Column(name="models", type="string", length=30)
     */
    private $models;
    
    /**
     * @var string
     *
     * @ORM\Column(name="sale_name", type="string", length=30, nullable=true)
     */
    private $saleName;

    /**
     * @var integer
     *
     * @ORM\Column(name="year_listing", type="integer", nullable=true)
     */
    private $yearListing;

    /**
     * @var string
     *
     * @ORM\Column(name="emission_standard", type="string", length=30)
     */
    private $emissionStandard;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=30, nullable=true)
     */
    private $type;
    
    /**
     * @var string
     *
     * @ORM\Column(name="level", type="string", length=20, nullable=true)
     */
    private $level;
    
    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="market_year", type="integer", nullable=true)
     */
    private $marketYear;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="market_month", type="smallint", nullable=true)
     */
    private $marketMonth;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="produce_year", type="integer", nullable=true)
     */
    private $produceYear;
    
    /**
     * @var float
     *
     * @ORM\Column(name="displacement", type="float", nullable=true)
     */
    private $displacement;
    
    /**
     * @var array
     *
     * @ORM\Column(name="collocate", type="json_array", nullable=true)
     */
    private $collocate;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set levelId
     *
     * @param string $levelId
     * @return VehiclesInit
     */
    public function setLevelId($levelId)
    {
        $this->levelId = $levelId;

        return $this;
    }

    /**
     * Get levelId
     *
     * @return string 
     */
    public function getLevelId()
    {
        return $this->levelId;
    }

    /**
     * Set vender
     *
     * @param string $vender
     * @return VehiclesInit
     */
    public function setVender($vender)
    {
        $this->vender = $vender;

        return $this;
    }

    /**
     * Get vender
     *
     * @return string 
     */
    public function getVender()
    {
        return $this->vender;
    }

    /**
     * Set brand
     *
     * @param string $brand
     * @return VehiclesInit
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return string 
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set brandLetter
     *
     * @param string $brandLetter
     * @return VehiclesInit
     */
    public function setBrandLetter($brandLetter)
    {
        $this->brandLetter = $brandLetter;

        return $this;
    }

    /**
     * Get brandLetter
     *
     * @return string 
     */
    public function getBrandLetter()
    {
        return $this->brandLetter;
    }

    /**
     * Set series
     *
     * @param string $series
     * @return VehiclesInit
     */
    public function setSeries($series)
    {
        $this->series = $series;

        return $this;
    }

    /**
     * Get series
     *
     * @return string 
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Set models
     *
     * @param string $models
     * @return VehiclesInit
     */
    public function setModels($models)
    {
        $this->models = $models;

        return $this;
    }

    /**
     * Get models
     *
     * @return string 
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * Set saleName
     *
     * @param string $saleName
     * @return VehiclesInit
     */
    public function setSaleName($saleName)
    {
        $this->saleName = $saleName;

        return $this;
    }

    /**
     * Get saleName
     *
     * @return string 
     */
    public function getSaleName()
    {
        return $this->saleName;
    }

    /**
     * Set yearListing
     *
     * @param integer $yearListing
     * @return VehiclesInit
     */
    public function setYearListing($yearListing)
    {
        $this->yearListing = $yearListing;

        return $this;
    }

    /**
     * Get yearListing
     *
     * @return integer 
     */
    public function getYearListing()
    {
        return $this->yearListing;
    }

    /**
     * Set emissionStandard
     *
     * @param string $emissionStandard
     * @return VehiclesInit
     */
    public function setEmissionStandard($emissionStandard)
    {
        $this->emissionStandard = $emissionStandard;

        return $this;
    }

    /**
     * Get emissionStandard
     *
     * @return string 
     */
    public function getEmissionStandard()
    {
        return $this->emissionStandard;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return VehiclesInit
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set level
     *
     * @param string $level
     * @return VehiclesInit
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return string 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return VehiclesInit
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set marketYear
     *
     * @param integer $marketYear
     * @return VehiclesInit
     */
    public function setMarketYear($marketYear)
    {
        $this->marketYear = $marketYear;

        return $this;
    }

    /**
     * Get marketYear
     *
     * @return integer 
     */
    public function getMarketYear()
    {
        return $this->marketYear;
    }

    /**
     * Set marketMonth
     *
     * @param integer $marketMonth
     * @return VehiclesInit
     */
    public function setMarketMonth($marketMonth)
    {
        $this->marketMonth = $marketMonth;

        return $this;
    }

    /**
     * Get marketMonth
     *
     * @return integer 
     */
    public function getMarketMonth()
    {
        return $this->marketMonth;
    }

    /**
     * Set produceYear
     *
     * @param integer $produceYear
     * @return VehiclesInit
     */
    public function setProduceYear($produceYear)
    {
        $this->produceYear = $produceYear;

        return $this;
    }

    /**
     * Get produceYear
     *
     * @return integer 
     */
    public function getProduceYear()
    {
        return $this->produceYear;
    }

    /**
     * Set displacement
     *
     * @param float $displacement
     * @return VehiclesInit
     */
    public function setDisplacement($displacement)
    {
        $this->displacement = $displacement;

        return $this;
    }

    /**
     * Get displacement
     *
     * @return float 
     */
    public function getDisplacement()
    {
        return $this->displacement;
    }

    /**
     * Set collocate
     *
     * @param array $collocate
     * @return VehiclesInit
     */
    public function setCollocate($collocate)
    {
        $this->collocate = $collocate;

        return $this;
    }

    /**
     * Get collocate
     *
     * @return array 
     */
    public function getCollocate()
    {
        return $this->collocate;
    }
}
