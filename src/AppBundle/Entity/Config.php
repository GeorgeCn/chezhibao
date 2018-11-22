<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @UniqueEntity(fields="company", message="公司名称不能重复")
 * @ORM\Table(name="exam_company_config", options={"comment":"公司配置表"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ConfigRepository")
 */
class Config
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="公司名称不能为空")
     * @ORM\Column(type="string", unique=true, options={"comment":"公司名称"})
     */
    private $company;

    /**
     * @ORM\Column(type="string", unique=true, length=32, nullable=true, options={"comment":"签名认证的key"})
     */
    private $companyKey;

    /**
     * @ORM\Column(type="string", length=32, nullable=true, options={"comment":"签名认证的serect"})
     */
    private $companySerect;

    /**
     * 
     * @ORM\Column(type="json_array", nullable=true, options={"comment":"对接对方公司接口的相关参数信息"})
     */
    private $parameter;

    /**
     * 
     * @ORM\Column(type="json_array", nullable=true, options={"comment":"审核要求 （原经营条件） "})
     */
    private $jytj;

    /**
     * 
     * @ORM\Column(type="json_array", nullable=true, options={"comment":"基本信息"})
     */
    private $info;

    /**
     * 
     * @ORM\Column(type="json_array", nullable=true, options={"comment":"控制字段显示策略"})
     */
    private $policy;

    /**
     * @ORM\Column(type="integer", options={"comment":"policy修改次数记录"})
     */
    private $version = 0;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"设置的分钟限制来发短信提醒审核师有单子需要及时审核了"})
     */
    private $timeLimit;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"是否为新打单公司", "default": false})
     */
    private $companyNew;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"是否需要进入复审", "default": false})
     */
    private $needRecheck;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"是否需要视频", "default": false})
     */
    private $needVideo;

    /**
     * @ORM\OneToMany(targetEntity="Agency", mappedBy="company")
     */
    private $agencies;

    /**
     * @ORM\Column(type="boolean", options={"comment":"是否允许新人审核", "default": false})
     */
    private $noobAllowed;

    const COMPANY_HPL = '先锋太盟';
    const COMPANY_HPL_CBT = '先锋太盟-车白条';
    const COMPANY_PINGAN = '平安租赁';
    const COMPANY_MLJR = '美利金融';
    const COMPANY_HTHY = '海通恒运';
    const COMPANY_KFCJ = '客服创建';
    const COMPANY_JGQC = '成都建国汽车';
    const COMPANY_ZTR = '浙江中投融汽车';
    const COMPANY_MCT = '美车堂';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->agencies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->companyNew = false;
        $this->needRecheck = false;
        $this->noobAllowed = false;
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
     * Set company
     *
     * @param string $company
     * @return Config
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string 
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * has name
     *
     * @return string
     */
    public function hasName()
    {
        return $this->company;
    }

    /**
     * Set parameter
     *
     * @param array $parameter
     * @return Config
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Get parameter
     *
     * @return array 
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Set jytj
     *
     * @param array $jytj
     * @return Config
     */
    public function setJytj($jytj)
    {
        $this->jytj = $jytj;

        return $this;
    }

    /**
     * Get jytj
     *
     * @return array 
     */
    public function getJytj()
    {
        return $this->jytj;
    }

    /**
     * Set info
     *
     * @param array $info
     * @return Config
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Get info
     *
     * @return array 
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Set policy
     *
     * @param array $policy
     * @return Config
     */
    public function setPolicy($policy)
    {
        $this->policy = $policy;

        return $this;
    }

    /**
     * Get policy
     *
     * @return array 
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * Set version
     *
     * @param integer $version
     * @return Config
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return integer 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set timeLimit
     *
     * @param string $timeLimit
     * @return Config
     */
    public function setTimeLimit($timeLimit)
    {
        $this->timeLimit = $timeLimit;

        return $this;
    }

    /**
     * Get timeLimit
     *
     * @return string 
     */
    public function getTimeLimit()
    {
        return $this->timeLimit;
    }


    /**
     * Set companyKey
     *
     * @param string $companyKey
     *
     * @return Config
     */
    public function setCompanyKey($companyKey)
    {
        $this->companyKey = $companyKey;

        return $this;
    }

    /**
     * Get companyKey
     *
     * @return string
     */
    public function getCompanyKey()
    {
        return $this->companyKey;
    }

    /**
     * Set companySerect
     *
     * @param string $companySerect
     *
     * @return Config
     */
    public function setCompanySerect($companySerect)
    {
        $this->companySerect = $companySerect;

        return $this;
    }

    /**
     * Get companySerect
     *
     * @return string
     */
    public function getCompanySerect()
    {
        return $this->companySerect;
    }

    /**
     * Set companyNew
     *
     * @param boolean $companyNew
     *
     * @return Config
     */
    public function setCompanyNew($companyNew)
    {
        $this->companyNew = $companyNew;

        return $this;
    }

    /**
     * Get companyNew
     *
     * @return boolean
     */
    public function getCompanyNew()
    {
        return $this->companyNew;
    }

    /**
     * Add agency
     *
     * @param \AppBundle\Entity\Agency $agency
     *
     * @return Config
     */
    public function addAgency(\AppBundle\Entity\Agency $agency)
    {
        $this->agencies[] = $agency;

        return $this;
    }

    /**
     * Remove agency
     *
     * @param \AppBundle\Entity\Agency $agency
     */
    public function removeAgency(\AppBundle\Entity\Agency $agency)
    {
        $this->agencies->removeElement($agency);
    }

    /**
     * Get agencies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAgencies()
    {
        return $this->agencies;
    }

    /**
     * Set needRecheck
     *
     * @param boolean $needRecheck
     *
     * @return Config
     */
    public function setNeedRecheck($needRecheck)
    {
        $this->needRecheck = $needRecheck;

        return $this;
    }

    /**
     * Get needRecheck
     *
     * @return boolean
     */
    public function getNeedRecheck()
    {
        return $this->needRecheck;
    }

    /**
     * Set needVideo
     *
     * @param boolean $needVideo
     *
     * @return Config
     */
    public function setNeedVideo($needVideo)
    {
        $this->needVideo = $needVideo;

        return $this;
    }

    /**
     * Get needVideo
     *
     * @return boolean
     */
    public function getNeedVideo()
    {
        return $this->needVideo;
    }

    /**
     * Set noobAllowed
     *
     * @param boolean $noobAllowed
     *
     * @return Config
     */
    public function setNoobAllowed($noobAllowed)
    {
        $this->noobAllowed = $noobAllowed;

        return $this;
    }

    /**
     * Get noobAllowed
     *
     * @return boolean
     */
    public function getNoobAllowed()
    {
        return $this->noobAllowed;
    }
}
