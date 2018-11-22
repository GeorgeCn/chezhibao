<?php

namespace YYC\FoundationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="foundation_maintain", options={"comment":"存放查询过各平台商的记录"}, indexes={
       @ORM\Index(name="IDX_VIN_STATUS_CREATEDAT", columns={"vin", "status", "created_at"}),
       @ORM\Index(name="IDX_ORDER_SUPPLIERTYPE_STATUS", columns={"order_id", "supplier_type", "status"}),
    })
 * @ORM\Entity(repositoryClass="YYC\FoundationBundle\Repository\MaintainRepository")
 */
class Maintain
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
     * @ORM\Column(type="string", options={"comment":"vin码"})
     */
    protected $vin;

    /**
     * @ORM\Column(type="string", options={"comment":"品牌名字"})
     */
    protected $brandName;

    /**
     * @ORM\Column(type="string", options={"comment":"查询该条记录的人员名字"})
     */
    protected $operator;

    /**
     * @ORM\Column(type="datetime", options={"comment":"什么时候查询的"})
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="json_array", nullable=true, options={"comment":"平台商返回的结果"})
     */
    protected $results;

    /**
     * @ORM\Column(type="smallint", options={"comment":"0正在查询，1查询成功，2查询失败"})
     */
    protected $status;
    const STATUS_WAIT = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL= 2;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"备注字段"})
     */
    protected $remark;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"comment":"提供查询的平台商类型 1为大圣来了平台 2为车鉴定平台 3为查博士平台 4为聚合 5为蚂蚁女王"})
     */
    protected $supplierType;
    const TYPE_DSLL = 1;
    const TYPE_CJD = 2;
    const TYPE_CBS = 3;
    const TYPE_JUHE = 4;
    const TYPE_ANTQUEEN = 5;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"车鉴定回调时的唯一标识"})
     */
    protected $orderId;

    /**
     * @ORM\Column(type="datetime", nullable=true , options={"comment":"第三方结果返回的时间"})
     */
    protected $returnAt;

    /**
     * @ORM\Column(type="integer", options={"comment":"查询起源 0:未知的历史记录(特殊处理) 1:有一车(ERP) 2:远程监测(HPL)","default": 0 })
     */
    protected $origins;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->status = self::STATUS_WAIT;
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
     * Set vin
     *
     * @param string $vin
     * @return Maintain
     */
    public function setVin($vin)
    {
        $this->vin = $vin;

        return $this;
    }

    /**
     * Get vin
     *
     * @return string
     */
    public function getVin()
    {
        return $this->vin;
    }

    /**
     * Set brandName
     *
     * @param string $brandName
     * @return Maintain
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;

        return $this;
    }

    /**
     * Get brandName
     *
     * @return string
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * Set operator
     *
     * @param string $operator
     * @return Maintain
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Maintain
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

    /**
     * Set results
     *
     * @param array $results
     * @return Maintain
     */
    public function setResults($results)
    {
        $this->results = $results;

        return $this;
    }

    /**
     * Get results
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Maintain
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set remark
     *
     * @param string $remark
     * @return Maintain
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;

        return $this;
    }

    /**
     * Get remark
     *
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    public function getSupplierType()
    {
        return $this->supplierType;
    }

    public function setSupplierType($supplierType)
    {
        $this->supplierType = $supplierType;

        return $this;
    }

    /**
     * Set orderId
     *
     * @param string $orderId
     * @return Maintain
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get orderId
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set origins
     *
     * @param integer $origin
     * @return Maintain
     */
    public function setOrigins($origin)
    {
        $this->origins = $origin;
        return $this;
    }

    /**
     * get origins
     *
     * return string
     */
    public function getOrigins()
    {
        return $this->origins;
    }

    /**
     * Set returnAt
     *
     * @param \DateTime $returnAt
     * @return Maintain
     */
    public function setReturnAt($returnAt)
    {
        $this->returnAt = $returnAt;

        return $this;
    }

    /**
     * Get returnAt
     *
     * @return \DateTime
     */
    public function getReturnAt()
    {
        return $this->returnAt;
    }

}
