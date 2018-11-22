<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * 因为手机号码和用户名都可做用户名登录，需要分别在编辑和新增模式下，用自定义的方法进行唯一性验证
 * @UniqueEntity(fields={"mobile", "id"}, message="系统中已存在该手机号的用户！", repositoryMethod="findUniqueEntity", groups={"CustomEdit"})
 * @UniqueEntity(fields={"mobile"}, message="系统中已有该手机号的用户！", repositoryMethod="findUniqueEntity", groups={"CustomNew"})
 * @UniqueEntity(fields={"username", "id"}, message="系统中已存在该用户！", repositoryMethod="findUniqueEntity", groups={"CustomEdit"})
 * @UniqueEntity(fields={"username"}, message="系统中已有该用户！", repositoryMethod="findUniqueEntity", groups={"CustomNew", "Kefu"})
 * 
 * @UniqueEntity(fields="username", message="用户名已创建过，不能重复", groups={"Custom", "Kefu"})
 * @UniqueEntity(fields="mobile", message="手机号码已存在，不能重复", groups={"Custom"})
 * @ORM\Table(name="user", options={"comment":"用户"}, indexes={
       @ORM\Index(name="IDX_user_mobile", columns={"mobile"})})
 * 覆盖原有必填的email字段
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="email", column=@ORM\Column(nullable=true)),
 *      @ORM\AttributeOverride(name="emailCanonical", column=@ORM\Column(nullable=true, unique=false))
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * 
     * @Assert\NotBlank(groups={"Custom", "CustomRegistration", "Kefu"}, message="姓名不能为空")
     * @ORM\Column(type="string", nullable=true, length=10, options={"comment":"姓名"})
     *
     */
    private $name;

    /**
     * @Assert\NotBlank(groups={"Custom", "CustomRegistration"}, message="手机号码不能为空")
     * @Assert\Length(min="11",max="11",groups={"Custom", "CustomRegistration"}, exactMessage="手机号码长度应为11位")
     * @Assert\Regex(pattern="/[0-9]/",groups={"Custom", "CustomRegistration"}, message="手机号码应为数字")
     * @ORM\Column(type="string", nullable=true, length=20, options={"comment":"手机号码"})
     *
     */
    private $mobile;

    /**
     * @ORM\ManyToOne(targetEntity="Province")
     */
    private $province;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     */
    private $city;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"comment":"1 试用账号，2 正式账号，3 内部账号，4 临时账号"})
     */
    private $type;
    const TYPE_TRIAL = 1;
    const TYPE_OFFICIAL = 2;
    const TYPE_INTERNAL = 3;
    const TYPE_TEMP = 4;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"用户来源信息如ip地址"})
     */
    private $source;

    /**
     * 
     * @ORM\Column(type="json_array", nullable=true, options={"comment":"存放额外的信息"})
     */
    private $data;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"创建时间"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"是否接收本人订单审核通过的短信","default": true})
     */
    private $receiveOwner;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"是否接收下级订单状态变更的短信","default": false})
     */
    private $receiveLower;

    /**
     * @ORM\Column(type="json_array", nullable=true, options={"comment":"接收短信提醒的下级订单状态变更类型。1 提交时，2 被又一车审核通过时，3 被又一车审核失败时"})
     */
    private $receiveTypes;

    const RECEIVE_TYPE_SUBMIT = 1;
    const RECEIVE_TYPE_REPORT_PASS = 2;
    const RECEIVE_TYPE_REPORT_FAIL = 3;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"审核师是否开启短信接收通知"})
     */
    private $examerReceive;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"是否是异常组", "default": false})
     */
    private $abnormal;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"审核师是否是新手", "default": false})
     */
    private $noob;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"comment":"1审核师"})
     */
    private $roleType;
    const TYPE_EXAMER = 1;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"是否上班", "default":false})
     */
    private $isJob;

    /**
     * @ORM\OneToMany(targetEntity="AgencyRel", mappedBy="user")
     */
    private $agencyRels;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '创建者'")
     */
    private $creater;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"comment":"上次登录的ip", "unsigned":true})
     *
     */
    private $lastIp;

    public function __construct()
    {
        parent::__construct();
        $this->enabled = true;//默认账号是启用的
        $this->createdAt = new \DateTime();

        $this->receiveOwner = true;//默认接收本人订单审核通过的短信
        $this->receiveTypes = array();
        $this->abnormal = false;
        $this->noob = false;
        $this->isJob = false;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
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
     * Set mobile
     *
     * @param string $mobile
     * @return User
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string 
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set province
     *
     * @param \AppBundle\Entity\Province $province
     * @return User
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
     * @return User
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
     * Set type
     *
     * @param integer $type
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set source
     *
     * @param string $source
     * @return User
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return User
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
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
     * Set receiveOwner
     *
     * @param boolean $receiveOwner
     * @return User
     */
    public function setReceiveOwner($receiveOwner)
    {
        $this->receiveOwner = $receiveOwner;

        return $this;
    }

    /**
     * Get receiveOwner
     *
     * @return boolean
     */
    public function getReceiveOwner()
    {
        return $this->receiveOwner;
    }

    /**
     * Set receiveLower
     *
     * @param boolean $receiveLower
     * @return User
     */
    public function setReceiveLower($receiveLower)
    {
        $this->receiveLower = $receiveLower;

        return $this;
    }

    /**
     * Get receiveLower
     *
     * @return boolean
     */
    public function getReceiveLower()
    {
        return $this->receiveLower;
    }

    /**
     * Set receiveTypes
     *
     * @param array $receiveTypes
     * @return User
     */
    public function setReceiveTypes($receiveTypes)
    {
        $this->receiveTypes = $receiveTypes;

        return $this;
    }

    /**
     * Get receiveTypes
     *
     * @return array 
     */
    public function getReceiveTypes()
    {
        return $this->receiveTypes;
    }

    /**
     * Set examerReceive
     *
     * @param boolean $examerReceive
     * @return User
     */
    public function setExamerReceive($examerReceive)
    {
        $this->examerReceive = $examerReceive;

        return $this;
    }

    /**
     * Get examerReceive
     *
     * @return boolean 
     */
    public function getExamerReceive()
    {
        return $this->examerReceive;
    }

    /**
     * Set abnormal
     *
     * @param boolean $abnormal
     *
     * @return User
     */
    public function setAbnormal($abnormal)
    {
        $this->abnormal = $abnormal;

        return $this;
    }

    /**
     * Get abnormal
     *
     * @return boolean
     */
    public function getAbnormal()
    {
        return $this->abnormal;
    }

    /**
     * Set roleType
     *
     * @param integer $roleType
     *
     * @return User
     */
    public function setRoleType($roleType)
    {
        $this->roleType = $roleType;

        return $this;
    }

    /**
     * Get roleType
     *
     * @return integer
     */
    public function getRoleType()
    {
        return $this->roleType;
    }

    /**
     * 如果用户的角色是审核师，则置类型为TYPE_EXAMER,反之为null
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function initRoleType()
    {
        foreach ($this->getRoles() as $role) {
            if (in_array($role, ['ROLE_EXAMER', 'ROLE_EXAMER_MANAGER', 'ROLE_ADMIN'])) {
                $this->setRoleType(self::TYPE_EXAMER);
                break;
            }
        }

        if ($this->getRoleType() !== self::TYPE_EXAMER) {
            $this->setRoleType(null);
        }
    }

    /**
     * Set isJob
     *
     * @param boolean $isJob
     *
     * @return User
     */
    public function setIsJob($isJob)
    {
        $this->isJob = $isJob;

        return $this;
    }

    /**
     * Get isJob
     *
     * @return boolean
     */
    public function getIsJob()
    {
        return $this->isJob;
    }

    /**
     * Add agency
     *
     * @param \AppBundle\Entity\AgencyRel $agency
     *
     * @return User
     */
    public function addAgency(\AppBundle\Entity\AgencyRel $agency)
    {
        $this->agencies[] = $agency;

        return $this;
    }

    /**
     * Add agencyRel
     *
     * @param \AppBundle\Entity\AgencyRel $agencyRel
     *
     * @return User
     */
    public function addAgencyRel(\AppBundle\Entity\AgencyRel $agencyRel)
    {
        $this->agencyRels[] = $agencyRel;

        return $this;
    }

    /**
     * Remove agencyRel
     *
     * @param \AppBundle\Entity\AgencyRel $agencyRel
     */
    public function removeAgencyRel(\AppBundle\Entity\AgencyRel $agencyRel)
    {
        $this->agencyRels->removeElement($agencyRel);
    }

    /**
     * Get agencyRels
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAgencyRels()
    {
        return $this->agencyRels;
    }

    /**
     * Set creater
     *
     * @param string $creater
     *
     * @return User
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
     * Set noob
     *
     * @param boolean $noob
     *
     * @return User
     */
    public function setNoob($noob)
    {
        $this->noob = $noob;

        return $this;
    }

    /**
     * Get noob
     *
     * @return boolean
     */
    public function getNoob()
    {
        return $this->noob;
    }

    /**
     * Set lastIp
     *
     * @param integer $lastIp
     *
     * @return User
     */
    public function setLastIp($lastIp)
    {
        if (is_string($lastIp)) {
            $lastIp = ip2long($lastIp);
        }
        $this->lastIp = $lastIp;

        return $this;
    }

    /**
     * Get lastIp
     *
     * @return integer
     */
    public function getLastIp()
    {
        return $this->lastIp;
    }
}
