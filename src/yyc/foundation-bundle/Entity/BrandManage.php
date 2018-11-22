<?php

namespace YYC\FoundationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * 
 * @ORM\Table(name="foundation_brand_manage", options={"comment":"当查询维修记录时，品牌里面存放优先建议查询的平台商"})
 * @ORM\Entity(repositoryClass="YYC\FoundationBundle\Repository\BrandManageRepository")
 */
class BrandManage
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
     * @ORM\Column(type="string", options={"comment":"品牌名字"})
     */
    protected $name;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"comment":"建议的类型 1为大圣来了平台 2为车鉴定平台"})
     */
    protected $recommendedType ;

    const TYPE_DSLL = 1;
    const TYPE_CJD = 2;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"记录最近一次拉取大圣来了数据的时间"})
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

    public function getRecommendedType()
    {
        return $this->recommendedType;
    }

    public function setRecommendedType($recommendedType)
    {
        $this->recommendedType = $recommendedType;

        return $this;
    }


}
