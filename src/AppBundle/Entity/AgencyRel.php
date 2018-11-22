<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AgencyRel
 *
 * @ORM\Table(name="agency_rel", options={"comment":"经销商和用户多对多中间表，附加额外字段"}, indexes={
       @ORM\Index(name="IDX_rel_user_grade", columns={"user_id","grade"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AgencyRelRepository")
 */
class AgencyRel
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '创建者'")
     */
    private $creater;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", options={"comment":"授权时间"})
     */
    private $createdAt;

    /** 
     * @ORM\ManyToOne(targetEntity="User", inversedBy="agencyRels")
     */
    private $user;

    /** 
     * @ORM\ManyToOne(targetEntity="Agency")
     */
    private $agency;

    /** 
     * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '在中间表中加入公司字段方便直接获取公司'")
     */
    private $company;

    /**
     * @ORM\Column(type="smallint", options={"comment":"账号等级(1-高，2-中，3-低)", "default": 0})
     */
    private $grade;

    public function __construct()
    {
        $this->grade = 0;
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return AgencyRel
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return AgencyRel
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set agency
     *
     * @param \AppBundle\Entity\Agency $agency
     *
     * @return AgencyRel
     */
    public function setAgency(\AppBundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return \AppBundle\Entity\Agency
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Set company
     *
     * @param \AppBundle\Entity\Config $company
     *
     * @return AgencyRel
     */
    public function setCompany(\AppBundle\Entity\Config $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \AppBundle\Entity\Config
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set creater
     *
     * @param \AppBundle\Entity\User $creater
     *
     * @return AgencyRel
     */
    public function setCreater(\AppBundle\Entity\User $creater = null)
    {
        $this->creater = $creater;

        return $this;
    }

    /**
     * Get creater
     *
     * @return \AppBundle\Entity\User
     */
    public function getCreater()
    {
        return $this->creater;
    }

    /**
     * Set grade
     *
     * @param integer $grade
     *
     * @return AgencyRel
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade
     *
     * @return integer
     */
    public function getGrade()
    {
        return $this->grade;
    }
}
