<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Liyang
 *
 * @ORM\Table(name="liyang", options={"comment":"力洋vin码映射"})
 * @ORM\Entity()
 */
class Liyang
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=24, options={"comment":"力洋数据库id"})
     * @ORM\Id
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
     * @ORM\Column(type="string", length=8, options={"comment":"年款"})
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=16, options={"comment":"国产，进口，合资"})
     */
    private $prodcutCt;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
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

    /**
     * Set year
     *
     * @param string $year
     *
     * @return Liyang
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
     * Set prodcutCt
     *
     * @param string $prodcutCt
     *
     * @return Liyang
     */
    public function setProdcutCt($prodcutCt)
    {
        $this->prodcutCt = $prodcutCt;

        return $this;
    }

    /**
     * Get prodcutCt
     *
     * @return string
     */
    public function getProdcutCt()
    {
        return $this->prodcutCt;
    }
}
