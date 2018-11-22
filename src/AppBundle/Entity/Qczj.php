<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Qczj
 * @ORM\Table(name="qczj", options={"comment":"汽车之家临时映射表"}, indexes={@ORM\Index(name="idx_brand_series", columns={"brand", "series"})})
 * @ORM\Entity()
 */
class Qczj
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
     * @ORM\Column(type="string", length=64, options={"comment":"品牌"})
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=64, options={"comment":"车系"})
     */
    private $series;

    /**
     * @ORM\Column(type="string", length=255, options={"comment":"车型"})
     */
    private $model;

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
     *
     * @return Liyang
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
     *
     * @return Liyang
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
     *
     * @return Liyang
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
}
