<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 
 * @ORM\Table(name="agency_apply", options={"comment":"授权申请"}, indexes={
       @ORM\Index(name="IDX_apply_grade", columns={"grade"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ApplyRepository")
 * 
 */
class Apply
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=6, options={"fixed":true, "comment":"生成随机数"})
     */
    private $rand;

    /**
     * @ORM\ManyToOne(targetEntity="Config")
     */
    private $company;

     /**
     * @ORM\ManyToOne(targetEntity="Agency")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $agency;

    /**
     * @Assert\NotBlank(message="手机号码不能为空")
     * @Assert\Length(min="11",max="11", exactMessage="手机号码长度应为11位")
     * @Assert\Regex(pattern="/[0-9]/", message="手机号码应为数字")
     * @ORM\Column(type="string", length=30, options={"comment":"手机号码"})
     */
    private $mobile;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"创建时间"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="smallint", options={"comment":"授权状态(0-授权中，1-授权完成，2-授权取消)", "default": 0})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=36, nullable=true, options={"comment":"授权人"})
     */
    private $creater;

    /**
     * @Assert\Regex(pattern="/[1-3]/", message="请先选中创建的账号等级")
     * @ORM\Column(type="smallint", options={"comment":"账号等级(1-高，2-中，3-低)", "default": 0})
     */
    private $grade;

    /**
     * @ORM\ManyToOne(targetEntity="Province")
     */
    private $province;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     */
    private $city;

    public function __construct()
    {
        $this->grade = 0;//默认未授权
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
     * Set rand
     *
     * @param string $rand
     *
     * @return Apply
     */
    public function setRand($rand)
    {
        $this->rand = $rand;

        return $this;
    }

    /**
     * Get rand
     *
     * @return string
     */
    public function getRand()
    {
        return $this->rand;
    }

    /**
     * Set mobile
     *
     * @param integer $mobile
     *
     * @return Apply
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return integer
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set userName
     *
     * @param string $userName
     *
     * @return Apply
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Apply
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
     * Set status
     *
     * @param boolean $status
     *
     * @return Apply
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set creater
     *
     * @param string $creater
     *
     * @return Apply
     */
    public function setCreater($creater)
    {
        $this->creater = $creater;

        return $this;
    }

    /**
     * Get creater
     *
     * @return string
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
     * @return Apply
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

    /**
     * Set company
     *
     * @param \AppBundle\Entity\Config $company
     *
     * @return Apply
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
     * Set agency
     *
     * @param \AppBundle\Entity\Agency $agency
     *
     * @return Apply
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
     * Set province
     *
     * @param \AppBundle\Entity\Province $province
     *
     * @return Apply
     */
    public function setProvince(\AppBundle\Entity\Province $province = null)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return \AppBundle\Entity\Province
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     *
     * @return Apply
     */
    public function setCity(\AppBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \AppBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }
}
