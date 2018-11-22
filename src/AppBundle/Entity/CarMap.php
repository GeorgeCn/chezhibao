<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="exam_car_map", options={"comment":"存放平安需要的力扬和汽车之家的车型库对应关系"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CarMapRepository")
 */
class CarMap
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", options={"comment":"品牌"}, nullable=true)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", options={"comment":"车系"}, nullable=true)
     */
    private $series;

    /**
     * @ORM\Column(type="string", options={"comment":"车型"}, nullable=true)
     */
    private $model;

    /**
     * @ORM\Column(type="string", options={"comment":"年款"}, nullable=true)
     */
    private $year;

    /**
     * @ORM\Column(type="string", options={"comment":"厂商"}, nullable=true)
     */
    private $manufacturer;

    /**
     * @ORM\Column(type="string", options={"comment":"翻译的品牌"}, nullable=true)
     */
    private $transBrand;

    /**
     * @ORM\Column(type="string", options={"comment":"翻译的车系"}, nullable=true)
     */
    private $transSeries;

    /**
     * @ORM\Column(type="string", options={"comment":"翻译的车型"}, nullable=true)
     */
    private $transModel;

    /**
     * @ORM\Column(type="string", options={"comment":"翻译的年款"}, nullable=true)
     */
    private $transYear;

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
     * Set brand
     *
     * @param string $brand
     * @return CarMap
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
     * Set series
     *
     * @param string $series
     * @return CarMap
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
     * Set model
     *
     * @param string $model
     * @return CarMap
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string 
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set year
     *
     * @param string $year
     * @return CarMap
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return string 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set transBrand
     *
     * @param string $transBrand
     * @return CarMap
     */
    public function setTransBrand($transBrand)
    {
        $this->transBrand = $transBrand;

        return $this;
    }

    /**
     * Get transBrand
     *
     * @return string 
     */
    public function getTransBrand()
    {
        return $this->transBrand;
    }

    /**
     * Set transSeries
     *
     * @param string $transSeries
     * @return CarMap
     */
    public function setTransSeries($transSeries)
    {
        $this->transSeries = $transSeries;

        return $this;
    }

    /**
     * Get transSeries
     *
     * @return string 
     */
    public function getTransSeries()
    {
        return $this->transSeries;
    }

    /**
     * Set transModel
     *
     * @param string $transModel
     * @return CarMap
     */
    public function setTransModel($transModel)
    {
        $this->transModel = $transModel;

        return $this;
    }

    /**
     * Get transModel
     *
     * @return string 
     */
    public function getTransModel()
    {
        return $this->transModel;
    }

    /**
     * Set transYear
     *
     * @param string $transYear
     * @return CarMap
     */
    public function setTransYear($transYear)
    {
        $this->transYear = $transYear;

        return $this;
    }

    /**
     * Get transYear
     *
     * @return string 
     */
    public function getTransYear()
    {
        return $this->transYear;
    }

    /**
     * Set manufacturer
     *
     * @param string $manufacturer
     * @return CarMap
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * Get manufacturer
     *
     * @return string 
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }
}
