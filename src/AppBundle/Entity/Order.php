<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @ORM\Table(name="exam_order", options={"comment":"检测订单"}, indexes={
        @ORM\Index(name="idx_status", columns={"status"}),
        @ORM\Index(name="idx_order_no", columns={"order_no"}),
        @ORM\Index(name="idx_submited_at", columns={"submited_at"}),
        @ORM\Index(name="idx_notified_status", columns={"notified_status"}),
    })
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderRepository")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"comment":"评估单号"})
     */
    private $orderNo;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '信贷员'")
     */
    private $loadOfficer;

    /**
     * @ORM\OneToOne(targetEntity="OrderBack")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '最后一次退回的订单'")
     */
    private $lastBack;

    /**
     * @ORM\OneToMany(targetEntity="OrderBack", mappedBy="examOrder")
     */
    private $backs;

    /**
     * @ORM\Column(type="datetime", options={"comment":"创建时间"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", options={"comment":"提交时间", "default":0})
     */
    private $submitedAt;

    /**
     * @ORM\Column(type="smallint", options={"comment":"0编辑中，1待审核，2成功，3审核师复检中"})
     */
    private $status;
    const STATUS_EDIT = 0;
    const STATUS_EXAM = 1;
    const STATUS_DONE = 2;
    const STATUS_RECHECK = 3;

    /**
     * "订单报告"
     * @ORM\OneToOne(targetEntity="Report")
     */
    private $report;

    /**
     * @ORM\Column(type="json_array", options={"comment":"检测订单的图片信息"})
     */
    private $pictures;

    /**
     * @ORM\Column(type="integer", options={"comment":"估价，单位元"})
     */
    private $valuation;

    /**
     * @ORM\Column(type="string",options={"comment":"备注"})
     */
    private $remark;

    /**
     * @ORM\Column(type="boolean", options={"comment":"逻辑删除"})
     */
    private $disable;

    /**
     * @ORM\Column(type="string", length=255, options={"comment":"经度"})
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=255, options={"comment":"纬度"})
     */
    private $latitude;

    /**
     * @ORM\Column(type="text", nullable=true, options={"comment":"记录异常订单的操作日志"})
     */
    private $operateLog;

    /**
     * @ORM\Column(type="text", nullable=true, options={"comment":"记录修改预售价日志"})
     */
    private $valuationLog;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"comment":"业务流水号"})
     */
    private $businessNumber;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"是否已经发短信提醒过审核师了"})
     */
    private $sendMessage;

    /**
     * @ORM\Column(type="string" , length=255, options={"comment":"人所在的详细地址，根据app端提交的经纬度来转换"})
     */
    private $orderAddress;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"comment":"保险信息id"})
     */
    private $insuranceId;

    /**
     * @ORM\ManyToOne(targetEntity="Order")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '该单子的父订单'")
     */
    private $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Order")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '该单子被异常处理后生产的新单子'")
     */
    private $fork;

    /**
     * @ORM\ManyToOne(targetEntity="Config")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '该单子所属的公司'")
     */
    private $company;

    /**
     * @ORM\Column(type="integer", options={"comment":"允许复制的次数"})
     */
    private $allowCopyTimes;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment":"是否插队", "default": false})
     */
    private $jump;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"插队时间"})
     */
    private $jumpedAt;

    /**
     * @ORM\Column(type="boolean", options={"comment":"单子是否被领", "default": false})
     */
    private $locked;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '单子被领的主人'")
     */
    private $lockOwner;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"人所在省份"})
     */
    private $personProvince;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"人所在城市"})
     */
    private $personCity;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"车所在省份"})
     */
    private $carProvince;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"车所在城市"})
     */
    private $carCity;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"comment":"冗余的经销商id，不做外键关联，经销商可能会被金融公司删掉"})
     */
    private $agencyId;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"经销商名字"})
     */
    private $agencyName;

    /**
     * @ORM\Column(type="string", nullable=true, options={"comment":"经销商code"})
     */
    private $agencyCode;

    /**
     * @ORM\Column(type="json_array", nullable=true, options={"comment":"视频"})
     */
    private $videos;

    /**
     * @ORM\Column(type="smallint", options={"comment":"1通知成功，2通知失败", "default": 0})
     */
    private $notifiedStatus;
    const NOTIFIED_STATUS_SUCCESS = 1;
    const NOTIFIED_STATUS_FAILED = 2;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"通知时间"})
     */
    private $notifiedAt;

    /**
     * @ORM\Column(type="smallint", options={"comment":"通知次数", "default": 0})
     */
    private $notifiedTimes;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->submitedAt = new \DateTime();
        $this->status = self::STATUS_EDIT;
        $this->valuation = 0;
        $this->remark = "";
        $this->pictures = [];
        $this->disable = false;
        $this->backs = new ArrayCollection();
        $this->longitude = "";
        $this->latitude = "";
        $this->orderAddress = "";
        $this->sendMessage = false;
        $this->allowCopyTimes = 0;
        $this->jump = false;
        $this->locked = false;
        $this->videos = [];
        $this->notifiedStatus = 0;
        $this->notifiedTimes = 0;

    }

    /**
     * 拼出又一车前缀年月日五位数ID
     * @ORM\PostPersist 
     * @param string $orderNo
     * @return Order
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $orderNo = 'YYC'.date('Ymd', time()).sprintf("%05d", $this->id);
        $this->setOrderNo($orderNo);
        $em = $args->getEntityManager();
        $em->flush();
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Order
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
     * @param integer $status
     * @return Order
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
     * Set pictures
     *
     * @param array $pictures
     * @return Order
     */
    public function setPictures($pictures)
    {
        // 防止老数据字段丢失，一定要在这里用merge的方式赋值
        $this->pictures = array_merge($this->pictures, $pictures);

        return $this;
    }

    /**
     * Get pictures
     *
     * @return array 
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * Set valuation
     *
     * @param integer $valuation
     * @return Order
     */
    public function setValuation($valuation)
    {
        $this->valuation = $valuation;

        return $this;
    }

    /**
     * Get valuation
     *
     * @return integer 
     */
    public function getValuation()
    {
        return $this->valuation;
    }

    /**
     * Set remark
     *
     * @param string $remark
     * @return Order
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
     * Set loadOfficer
     *
     * @param \AppBundle\Entity\User $loadOfficer
     * @return Order
     */
    public function setLoadOfficer(\AppBundle\Entity\User $loadOfficer = null)
    {
        $this->loadOfficer = $loadOfficer;

        return $this;
    }

    /**
     * Get loadOfficer
     *
     * @return \AppBundle\Entity\User 
     */
    public function getLoadOfficer()
    {
        return $this->loadOfficer;
    }

    /**
     * Set lastBack
     *
     * @param \AppBundle\Entity\OrderBack $lastBack
     * @return Order
     */
    public function setLastBack(\AppBundle\Entity\OrderBack $lastBack = null)
    {
        $this->lastBack = $lastBack;

        return $this;
    }

    /**
     * Get lastBack
     *
     * @return \AppBundle\Entity\OrderBack 
     */
    public function getLastBack()
    {
        return $this->lastBack;
    }

    /**
     * Set report
     *
     * @param \AppBundle\Entity\Report $report
     * @return Order
     */
    public function setReport(\AppBundle\Entity\Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return \AppBundle\Entity\Report 
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Set disable
     *
     * @param string $disable
     * @return Order
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;

        return $this;
    }

    /**
     * Get disable
     *
     * @return string 
     */
    public function getDisable()
    {
        return $this->disable;
    }

    /**
     * Set submitedAt
     *
     * @param \DateTime $submitedAt
     * @return Order
     */
    public function setSubmitedAt($submitedAt)
    {
        $this->submitedAt = $submitedAt;

        return $this;
    }

    /**
     * Get submitedAt
     *
     * @return \DateTime 
     */
    public function getSubmitedAt()
    {
        return $this->submitedAt;
    }

    /**
     * Add backs
     *
     * @param \AppBundle\Entity\OrderBack $backs
     * @return Order
     */
    public function addBack(\AppBundle\Entity\OrderBack $backs)
    {
        $this->backs[] = $backs;

        return $this;
    }

    /**
     * Remove backs
     *
     * @param \AppBundle\Entity\OrderBack $backs
     */
    public function removeBack(\AppBundle\Entity\OrderBack $backs)
    {
        $this->backs->removeElement($backs);
    }

    /**
     * Get backs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBacks()
    {
        return $this->backs;
    }

    /**
     * Set orderNo
     *
     * @param string $orderNo
     * @return Order
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

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return Order
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return Order
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set businessNumber
     *
     * @param string $businessNumber
     * @return Order
     */
    public function setBusinessNumber($businessNumber)
    {
        $this->businessNumber = $businessNumber;

        return $this;
    }

    /**
     * Get businessNumber
     *
     * @return string 
     */
    public function getBusinessNumber()
    {
        return $this->businessNumber;
    }


    /**
     * Set orderAddress
     *
     * @param string $orderAddress
     * @return Order
     */
    public function setOrderAddress($orderAddress)
    {
        $this->orderAddress = $orderAddress;

        return $this;
    }

    /**
     * Get orderAddress
     *
     * @return string 
     */
    public function getOrderAddress()
    {
        return $this->orderAddress;
    }

    /*********************************---function--***********************************************/

    /**
     * Change pictures
     *
     * @param array $pictures
     * @return Order
     */
    public function changePicturesKeys($pictures)
    {
        //修改数据库 pictures key 临时使用
        $this->pictures = $pictures;
        return $this;
    }



    /**
     * Set sendMessage
     *
     * @param boolean $sendMessage
     * @return Order
     */
    public function setSendMessage($sendMessage)
    {
        $this->sendMessage = $sendMessage;

        return $this;
    }

    /**
     * Get sendMessage
     *
     * @return boolean 
     */
    public function getSendMessage()
    {
        return $this->sendMessage;
    }

    /**
     * Set insuranceId
     *
     * @param integer $insuranceId
     * @return Order
     */
    public function setInsuranceId($insuranceId)
    {
        $this->insuranceId = $insuranceId;

        return $this;
    }

    /**
     * Get insuranceId
     *
     * @return integer 
     */
    public function getInsuranceId()
    {
        return $this->insuranceId;
    }

    /**
     * Set company
     *
     * @param \AppBundle\Entity\Config $company
     *
     * @return Order
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
     * Set parent
     *
     * @param \AppBundle\Entity\Order $parent
     *
     * @return Order
     */
    public function setParent(\AppBundle\Entity\Order $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Order
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set allowCopyTimes
     *
     * @param integer $allowCopyTimes
     *
     * @return Order
     */
    public function setAllowCopyTimes($allowCopyTimes)
    {
        $this->allowCopyTimes = $allowCopyTimes;

        return $this;
    }

    /**
     * Get allowCopyTimes
     *
     * @return integer
     */
    public function getAllowCopyTimes()
    {
        return $this->allowCopyTimes;
    }

    /**
     * Set jump
     *
     * @param boolean $jump
     *
     * @return Order
     */
    public function setJump($jump)
    {
        $this->jump = $jump;

        return $this;
    }

    /**
     * Set personProvince
     *
     * @param string $personProvince
     *
     * @return Order
     */
    public function setPersonProvince($personProvince)
    {
        $this->personProvince = $personProvince;

        return $this;
    }

    /**
     * Get jump
     *
     * @return boolean
     */
    public function getJump()
    {
        return $this->jump;
    }

    /**
     * Set jumpedAt
     *
     * @param \DateTime $jumpedAt
     *
     * @return Order
     */
    public function setJumpedAt($jumpedAt)
    {
        $this->jumpedAt = $jumpedAt;

        return $this;
    }

    /**
     * Get jumpedAt
     *
     * @return \DateTime
     */
    public function getJumpedAt()
    {
        return $this->jumpedAt;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     *
     * @return Order
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set lockOwner
     *
     * @param \AppBundle\Entity\User $lockOwner
     *
     * @return Order
     */
    public function setLockOwner(\AppBundle\Entity\User $lockOwner = null)
    {
        $this->lockOwner = $lockOwner;

        return $this;
    }

    /**
     * Get lockOwner
     *
     * @return \AppBundle\Entity\User
     */
    public function getLockOwner()
    {
        return $this->lockOwner;
    }

    /**
     * Get personProvince
     *
     * @return string
     */
    public function getPersonProvince()
    {
        return $this->personProvince;
    }

    /**
     * Set personCity
     *
     * @param string $personCity
     *
     * @return Order
     */
    public function setPersonCity($personCity)
    {
        $this->personCity = $personCity;

        return $this;
    }

    /**
     * Get personCity
     *
     * @return string
     */
    public function getPersonCity()
    {
        return $this->personCity;
    }

    /**
     * Set carProvince
     *
     * @param string $carProvince
     *
     * @return Order
     */
    public function setCarProvince($carProvince)
    {
        $this->carProvince = $carProvince;

        return $this;
    }

    /**
     * Get carProvince
     *
     * @return string
     */
    public function getCarProvince()
    {
        return $this->carProvince;
    }

    /**
     * Set carCity
     *
     * @param string $carCity
     *
     * @return Order
     */
    public function setCarCity($carCity)
    {
        $this->carCity = $carCity;

        return $this;
    }

    /**
     * Get carCity
     *
     * @return string
     */
    public function getCarCity()
    {
        return $this->carCity;
    }

    /**
     * Set agencyId
     *
     * @param integer $agencyId
     *
     * @return Order
     */
    public function setAgencyId($agencyId)
    {
        $this->agencyId = $agencyId;

        return $this;
    }

    /**
     * Get agencyId
     *
     * @return integer
     */
    public function getAgencyId()
    {
        return $this->agencyId;
    }

    /**
     * Set agencyName
     *
     * @param string $agencyName
     *
     * @return Order
     */
    public function setAgencyName($agencyName)
    {
        $this->agencyName = $agencyName;

        return $this;
    }

    /**
     * Get agencyName
     *
     * @return string
     */
    public function getAgencyName()
    {
        return $this->agencyName;
    }

    /**
     * Set agencyCode
     *
     * @param string $agencyCode
     *
     * @return Order
     */
    public function setAgencyCode($agencyCode)
    {
        $this->agencyCode = $agencyCode;

        return $this;
    }

    /**
     * Get agencyCode
     *
     * @return string
     */
    public function getAgencyCode()
    {
        return $this->agencyCode;
    }

    /**
     * Set valuationLog
     *
     * @param string $valuationLog
     *
     * @return Order
     */
    public function setValuationLog($valuationLog)
    {
        $this->valuationLog .= $valuationLog;

        return $this;
    }

    /**
     * Get valuationLog
     *
     * @return string
     */
    public function getValuationLog()
    {
        return $this->valuationLog;
    }

    /**
     * Set fork
     *
     * @param \AppBundle\Entity\Order $fork
     *
     * @return Order
     */
    public function setFork(\AppBundle\Entity\Order $fork = null)
    {
        $this->fork = $fork;

        return $this;
    }

    /**
     * Get fork
     *
     * @return \AppBundle\Entity\Order
     */
    public function getFork()
    {
        return $this->fork;
    }

    /**
     * Set operateLog
     *
     * @param string $operateLog
     *
     * @return Order
     */
    public function setOperateLog($operateLog)
    {
        $this->operateLog = $operateLog;

        return $this;
    }

    /**
     * Get operateLog
     *
     * @return string
     */
    public function getOperateLog()
    {
        return $this->operateLog;
    }

    /**
     * Set videos
     *
     * @param array $videos
     *
     * @return Order
     */
    public function setVideos($videos)
    {
        $this->videos = $videos;

        return $this;
    }

    /**
     * Get videos
     *
     * @return array
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Set notifiedStatus
     *
     * @param integer $notifiedStatus
     *
     * @return Order
     */
    public function setNotifiedStatus($notifiedStatus)
    {
        $this->notifiedStatus = $notifiedStatus;

        return $this;
    }

    /**
     * Get notifiedStatus
     *
     * @return integer
     */
    public function getNotifiedStatus()
    {
        return $this->notifiedStatus;
    }

    /**
     * Set notifiedAt
     *
     * @param \DateTime $notifiedAt
     *
     * @return Order
     */
    public function setNotifiedAt($notifiedAt)
    {
        $this->notifiedAt = $notifiedAt;

        return $this;
    }

    /**
     * Get notifiedAt
     *
     * @return \DateTime
     */
    public function getNotifiedAt()
    {
        return $this->notifiedAt;
    }

    /**
     * Set notifiedTimes
     *
     * @param integer $notifiedTimes
     *
     * @return Order
     */
    public function setNotifiedTimes($notifiedTimes)
    {
        $this->notifiedTimes = $notifiedTimes;

        return $this;
    }

    /**
     * Get notifiedTimes
     *
     * @return integer
     */
    public function getNotifiedTimes()
    {
        return $this->notifiedTimes;
    }
}
