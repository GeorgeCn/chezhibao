<?php

namespace YYC\FoundationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="foundation_insurance", options={"comment":"存放查询过各平台商的记录"}, indexes={
       @ORM\Index(name="IDX_VIN_STATUS_CREATEDAT", columns={"vin", "status", "created_at"}),
    })
 * @ORM\Entity(repositoryClass="YYC\FoundationBundle\Repository\InsuranceRepository")
 */
class Insurance
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
     * @ORM\Column(type="smallint", nullable=true, options={"comment":"提供查询的平台商类型 1为老司机"})
     */
    protected $supplierType;
    const TYPE_LSJ = 1;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"对方回调时的唯一标识"})
     */
    protected $callbackId;

    /**
     * @ORM\Column(type="datetime", nullable=true , options={"comment":"对方结果返回的时间"})
     */
    protected $returnAt;

    /**
     * @ORM\Column(type="integer", options={"comment":"1:有一车(ERP) 2:远程监测(HPL)"})
     */
    protected $origin;
    const TYPE_ERP = 1;
    const TYPE_HPL = 2;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"远程检测评估单号"})
     */
    protected $orderNo;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->status = self::STATUS_WAIT;
        $this->origin = self::TYPE_HPL;
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
     * @return Insurance
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
     * Set operator
     *
     * @param string $operator
     * @return Insurance
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
     * @return Insurance
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
     * @return Insurance
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
     * @return Insurance
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
     * @return Insurance
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

    /**
     * Set supplierType
     *
     * @param integer $supplierType
     * @return Insurance
     */
    public function setSupplierType($supplierType)
    {
        $this->supplierType = $supplierType;

        return $this;
    }

    /**
     * Get supplierType
     *
     * @return integer 
     */
    public function getSupplierType()
    {
        return $this->supplierType;
    }

    /**
     * Set callbackId
     *
     * @param string $callbackId
     * @return Insurance
     */
    public function setCallbackId($callbackId)
    {
        $this->callbackId = $callbackId;

        return $this;
    }

    /**
     * Get callbackId
     *
     * @return string 
     */
    public function getCallbackId()
    {
        return $this->callbackId;
    }

    /**
     * Set returnAt
     *
     * @param \DateTime $returnAt
     * @return Insurance
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

    /**
     * Set origin
     *
     * @param integer $origin
     * @return Insurance
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return integer 
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set orderNo
     *
     * @param string $orderNo
     * @return Insurance
     */
    public function setOrderNo($orderNo)
    {
        $this->orderNo = $orderNo;

        return $this;
    }

    /**
     * Get orderNo
     *
     * @return string 
     */
    public function getOrderNo()
    {
        return $this->orderNo;
    }
}
