<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * 
 * @ORM\Table(name="exam_order_back",options={"comment":"被退回订单"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderBackRepository")
 * 
 */
class OrderBack
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * 被退回的订单号
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="backs")
     */
    private $examOrder;

    /**
     * @ORM\Column(type="json_array",options={"comment":"退回原因"})
     */
    private $reason;

    /**
     * @ORM\Column(type="text", options={"comment":"其他退回理由", "default":""})
     */
    private $mainReason;

    /**
     * @ORM\Column(type="datetime",options={"comment":"退回时间"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", options={"comment":"审核人id"})
     */
    private $examerId;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"原始单据的提交时间"})
     */
    private $orgSubmittedAt;

    /* ---------------------------------------------------functions-------------------------------------------------- */

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->mainReason = "";
    }

    /* ---------------------------------------------------generates-------------------------------------------------- */

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return OrderBack
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
     * Set reason
     *
     * @param array $reason
     * @return OrderBack
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return array 
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set examOrder
     *
     * @param \AppBundle\Entity\Order $examOrder
     * @return OrderBack
     */
    public function setExamOrder(\AppBundle\Entity\Order $examOrder = null)
    {
        $this->examOrder = $examOrder;

        return $this;
    }

    /**
     * Get examOrder
     *
     * @return \AppBundle\Entity\Order 
     */
    public function getExamOrder()
    {
        return $this->examOrder;
    }

    /**
     * Set mainReason
     *
     * @param string $mainReason
     * @return OrderBack
     */
    public function setMainReason($mainReason)
    {
        $this->mainReason = $mainReason;

        return $this;
    }

    /**
     * Get mainReason
     *
     * @return string 
     */
    public function getMainReason()
    {
        return $this->mainReason;
    }

    /**
     * Set examerId
     *
     * @param integer $examerId
     * @return OrderBack
     */
    public function setExamerId($examerId)
    {
        $this->examerId = $examerId;

        return $this;
    }

    /**
     * Get examerId
     *
     * @return integer 
     */
    public function getExamerId()
    {
        return $this->examerId;
    }

    /**
     * Set orgSubmittedAt
     *
     * @param \DateTime $orgSubmittedAt
     *
     * @return OrderBack
     */
    public function setOrgSubmittedAt($orgSubmittedAt)
    {
        $this->orgSubmittedAt = $orgSubmittedAt;

        return $this;
    }

    /**
     * Get orgSubmittedAt
     *
     * @return \DateTime
     */
    public function getOrgSubmittedAt()
    {
        return $this->orgSubmittedAt;
    }

}
