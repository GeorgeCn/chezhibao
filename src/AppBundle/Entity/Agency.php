<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Agency
 * @ORM\Table(name="agency", options={"comment":"经销商"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AgencyRepository")
 */
class Agency
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
     * @ORM\ManyToOne(targetEntity="Config", inversedBy="agencies")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '所属公司'")
     */
    private $company;

    /**
     * @var string
     * @Assert\NotBlank(message="名字不能为空")
     * @ORM\Column(name="name", type="string", length=255, options={"comment":"经销商名字"})
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank(message="code不能为空")
     * @ORM\Column(name="code", type="string", length=255, options={"comment":"经销商代码"})
     */
    private $code;

    /**
     * @Assert\NotBlank(message="省份不能为空")
     * @ORM\ManyToOne(targetEntity="Province")
     */
    private $province;

    /**
     * @Assert\NotBlank(message="城市不能为空")
     * @ORM\ManyToOne(targetEntity="City")
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '经销商创建者'")
     */
    private $creater;

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
     * Set name
     *
     * @param string $name
     *
     * @return Agency
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
     * Set code
     *
     * @param string $code
     *
     * @return Agency
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set province
     *
     * @param \AppBundle\Entity\Province $province
     *
     * @return Agency
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
     * @return Agency
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

    /**
     * Set company
     *
     * @param \AppBundle\Entity\Config $company
     *
     * @return Agency
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
     * Constructor
     */
    public function __construct()
    {
        $this->agencyRels = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add agencyRel
     *
     * @param \AppBundle\Entity\AgencyRel $agencyRel
     *
     * @return Agency
     */
    public function addAgencyRel(\AppBundle\Entity\AgencyRel $agencyRel)
    {
        $this->agencyRels[] = $agencyRel;

        return $this;
    }

    /**
     * Set creater
     *
     * @param \AppBundle\Entity\User $creater
     *
     * @return Agency
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
}
