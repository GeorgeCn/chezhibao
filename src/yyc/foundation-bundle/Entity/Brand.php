<?php

namespace YYC\FoundationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * 
 * @ORM\Table(name="foundation_brand", options={"comment":"将通过大圣来了接口获取到的品牌数据信息存放到数据库"})
 * @ORM\Entity(repositoryClass="YYC\FoundationBundle\Repository\BrandRepository")
 */
class Brand
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", unique=true, options={"comment":"大圣来了的品牌id"})
     */
    protected $brandId;

    /**
     * @ORM\Column(type="string", options={"comment":"大圣来了的品牌名字"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", options={"comment":"大圣来了的品牌提示"})
     */
    protected $brandTips;

    /**
     * @ORM\Column(type="integer", options={"comment":"大圣来了的品牌价格"})
     */
    protected $price;

    /**
     * @ORM\Column(type="boolean", options={"comment":"该品牌是否需要发动机号"})
     */
    protected $isNeedEngineNumber;

    /**
     * @ORM\Column(type="datetime", options={"comment":"记录最近一次拉取大圣来了数据的时间"})
     */
    protected $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

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
     * Set brandId
     *
     * @param integer $brandId
     * @return Brand
     */
    public function setBrandId($brandId)
    {
        $this->brandId = $brandId;

        return $this;
    }

    /**
     * Get brandId
     *
     * @return integer 
     */
    public function getBrandId()
    {
        return $this->brandId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Brand
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set brandTips
     *
     * @param string $brandTips
     * @return Brand
     */
    public function setBrandTips($brandTips)
    {
        $this->brandTips = $brandTips;

        return $this;
    }

    /**
     * Get brandTips
     *
     * @return string 
     */
    public function getBrandTips()
    {
        return $this->brandTips;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return Brand
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set isNeedEngineNumber
     *
     * @param boolean $isNeedEngineNumber
     * @return Brand
     */
    public function setIsNeedEngineNumber($isNeedEngineNumber)
    {
        $this->isNeedEngineNumber = $isNeedEngineNumber;

        return $this;
    }

    /**
     * Get isNeedEngineNumber
     *
     * @return boolean 
     */
    public function getIsNeedEngineNumber()
    {
        return $this->isNeedEngineNumber;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Brand
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
