<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="exam_report", options={"comment":"检测报告"}, indexes={
        @ORM\Index(name="idx_status", columns={"status"}),
        @ORM\Index(name="idx_vin", columns={"vin"}),
        @ORM\Index(name="idx_brand", columns={"brand"}),
        @ORM\Index(name="idx_series", columns={"series"}),
        @ORM\Index(name="idx_model", columns={"model"}),
        @ORM\Index(name="idx_examed_at", columns={"examed_at"}),
    })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReportRepository")
 */
class Report
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", options={"comment":"车架号VIN码"}, nullable=true)
     */
    private $vin;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '审核师'")
     */
    private $examer;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"完成审核的时间"})
     */
    private $examedAt;

    /**
     * @ORM\Column(type="smallint", options={"comment":"0待审核,1审核通过,2审核拒绝"})
     */
    private $status;

    const STATUS_WAIT = 0;
    const STATUS_PASS = 1;
    const STATUS_REFUSE = 2;

    /**
     * @ORM\Column(type="boolean", options={"comment":"标识是否处于复审的检测状态", "default": false})
     */
    private $hplExaming;

    /**
     * @ORM\Column(type="text", options={"comment":"hpl高价复核退回原因"}, nullable=true)
     */
    private $hplReason;

    /**
     * @ORM\Column(type="string", options={"comment":"品牌"}, nullable=true)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", options={"comment":"车系"}, nullable=true)
     */
    private $series;

    /**
     * @ORM\Column(type="string", options={"comment":"车型"}, nullable=true)
     */
    private $model;

    /**
     * @ORM\Column(type="string", options={"comment":"年款"}, nullable=true)
     */
    private $year;

    /**
     * @ORM\Column(type="json_array", options={"comment":"报告"})
     */
    private $report;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"报告创建时间"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean", options={"comment":"是否已同步到又一车erp", "default": false})
     */
    private $synced;

    /**
     * @ORM\Column(type="json_array", nullable=true, options={"comment":"保存查询车三百时返回的结果"})
     */
    private $csbResults;

    /**
     * @ORM\Column(type="integer",nullable=true,options={"comment":"维保信息id","default":0})
     */
    private $maintain_id;

    /**
     * @ORM\Column(type="boolean", options={"comment":"目前是否有人在审核"})
     */
    private $locked;

    /**
     * @ORM\Column(type="smallint", options={"comment":"1基本信息审核中，2车型确定中，3配置确认中，4总结中，5核价中，6审核完成"})
     */
    private $stage;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"comment":"品牌id"})
     */
    private $brandId;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"comment":"车系id"})
     */
    private $seriesId;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"comment":"车型id"})
     */
    private $modelId;

    /**
     * @ORM\Column(type="string", options={"comment":"从车置宝获取到的平均价"}, nullable=true)
     */
    private $averagePrice;

    /**
     * @ORM\Column(type="string", options={"comment":"从车置宝获取到竞价次数"}, nullable=true)
     */
    private $biddingCount;

    const STAGE_BASIC = 1;
    const STAGE_MODEL = 2;
    const STAGE_CONFIG = 3;
    const STAGE_SUMMARIZE = 4;
    const STAGE_PRICE = 5;
    const STAGE_FINISH = 6;

    /**
     * @ORM\Column(type="string", options={"comment":"销售价"}, nullable=true)
     */
    private $sellPrice;

    /**
     * @ORM\Column(type="string", options={"comment":"收购价"}, nullable=true)
     */
    private $purchasePrice;

    /**
     * @ORM\Column(type="string", options={"comment":"新车指导价"}, nullable=true)
     */
    private $guidePrice;

    /**
     * @ORM\Column(type="string", options={"comment":"未来价格"}, nullable=true)
     */
    private $futurePrice;

    /**
     * @ORM\Column(type="string", options={"comment":"公里数"}, nullable=true)
     */
    private $kilometer;

    /**
     * @ORM\Column(type="string", options={"comment":"注册日期"}, nullable=true)
     */
    private $registerDate;

    /**
     * @ORM\Column(type="json_array", options={"comment":"北极星估计返回结果"}, nullable=true)
     */
    private $bjxResult;

    /**
     * @ORM\Column(type="json_array", options={"comment":"复检报告"})
     */
    private $secReport;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '复检师'")
     */
    private $rechecker;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"复审开始时间"})
     */
    private $startAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"复审结束时间"})
     */
    private $endAt;
    /* ---------------------------------------------------functions-------------------------------------------------- */

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->status = self::STATUS_WAIT;
        $this->hplExaming = false;
        $this->synced = false;
        $this->locked = true;
        $this->stage = self::STAGE_BASIC;
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
     * Set vin
     *
     * @param string $vin
     * @return Report
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
     * Set brand
     *
     * @param string $brand
     * @return Report
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set series
     *
     * @param string $series
     * @return Report
     */
    public function setSeries($series)
    {
        $this->series = $series;

        return $this;
    }

    /**
     * Get series
     *
     * @return string
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Set model
     *
     * @param string $model
     * @return Report
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set report
     *
     * @param array $report
     * @return Report
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report(union-report)
     *
     * @return array
     */
    public function getReport()
    {
        $secReport = $this->getSecReport();
        $data = [];
        if(!empty($secReport)) {
            foreach ($secReport as $key => $value) {
                if($value['diff'] == true) {
                    $data[$key] = $value['new'];
                } else {
                    $data[$key] = $value['old'];
                }
            }
        } else {
            return $this->report;
        }

        return $data;
    }

    /**
     * Get primaryReport(union-report)
     *
     * @return array
     */
    public function getPrimaryReport()
    {
        return $this->report;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Report
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
     * Set examedAt
     *
     * @param \DateTime $examedAt
     * @return Report
     */
    public function setExamedAt($examedAt)
    {
        $this->examedAt = $examedAt;

        return $this;
    }

    /**
     * Get examedAt
     *
     * @return \DateTime
     */
    public function getExamedAt()
    {
        return $this->examedAt;
    }

    /**
     * Set examer
     *
     * @param \AppBundle\Entity\User $examer
     * @return Report
     */
    public function setExamer(\AppBundle\Entity\User $examer = null)
    {
        $this->examer = $examer;

        return $this;
    }

    /**
     * Get examer
     *
     * @return \AppBundle\Entity\User
     */
    public function getExamer()
    {
        return $this->examer;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Report
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
     * Set hplExaming
     *
     * @param boolean $hplExaming
     * @return Report
     */
    public function setHplExaming($hplExaming)
    {
        $this->hplExaming = $hplExaming;

        return $this;
    }

    /**
     * Get hplExaming
     *
     * @return boolean
     */
    public function getHplExaming()
    {
        return $this->hplExaming;
    }

    /**
     * Set hplReason
     *
     * @param string $hplReason
     * @return Report
     */
    public function setHplReason($hplReason)
    {
        $this->hplReason = $hplReason;

        return $this;
    }

    /**
     * Get hplReason
     *
     * @return string
     */
    public function getHplReason()
    {
        return $this->hplReason;
    }

    /**
     * Set synced
     *
     * @param boolean $synced
     * @return Report
     */
    public function setSynced($synced)
    {
        $this->synced = $synced;

        return $this;
    }

    /**
     * Get synced
     *
     * @return boolean
     */
    public function getSynced()
    {
        return $this->synced;
    }

    /**
     * Set csbResults
     *
     * @param array $csbResults
     * @return Report
     */
    public function setCsbResults($csbResults)
    {
        $this->csbResults = $csbResults;

        return $this;
    }

    /**
     * Get csbResults
     *
     * @return array
     */
    public function getCsbResults()
    {
        return $this->csbResults;
    }

    /**
     * Set Maintain_id
     *
     * @param integer $maintain_id
     * @return Report
     */
    public function setMaintain($maintain_id)
    {
        $this->maintain_id = $maintain_id;

        return $this;
    }

    /**
     * Get Maintain_id
     *
     * @return integer
     */
    public function getMaintain()
    {
        return $this->maintain_id;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * Set maintain_id
     *
     * @param integer $maintainId
     * @return Report
     */
    public function setMaintainId($maintainId)
    {
        $this->maintain_id = $maintainId;

        return $this;
    }

    /**
     * Get maintain_id
     *
     * @return integer 
     */
    public function getMaintainId()
    {
        return $this->maintain_id;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     *
     * @return Report
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
     * Set stage
     *
     * @param integer $stage
     *
     * @return Report
     */
    public function setStage($stage)
    {
        $this->stage = $stage;

        return $this;
    }

    /**
     * Get stage
     *
     * @return integer
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Set brandId
     *
     * @param integer $brandId
     *
     * @return Report
     */
    public function setBrandId($brandId)
    {
        $this->brandId = $brandId;

        return $this;
    }

    /**
     * Get brandId
     *
     * @return integer
     */
    public function getBrandId()
    {
        return $this->brandId;
    }

    /**
     * Set seriesId
     *
     * @param integer $seriesId
     *
     * @return Report
     */
    public function setSeriesId($seriesId)
    {
        $this->seriesId = $seriesId;

        return $this;
    }

    /**
     * Get seriesId
     *
     * @return integer
     */
    public function getSeriesId()
    {
        return $this->seriesId;
    }

    /**
     * Set modelId
     *
     * @param integer $modelId
     *
     * @return Report
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;

        return $this;
    }

    /**
     * Get modelId
     *
     * @return integer
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * Set averagePrice
     *
     * @param string $averagePrice
     *
     * @return Report
     */
    public function setAveragePrice($averagePrice)
    {
        $this->averagePrice = $averagePrice;

        return $this;
    }

    /**
     * Get averagePrice
     *
     * @return string
     */
    public function getAveragePrice()
    {
        return $this->averagePrice;
    }

    /**
     * Set biddingCount
     *
     * @param string $biddingCount
     *
     * @return Report
     */
    public function setBiddingCount($biddingCount)
    {
        $this->biddingCount = $biddingCount;

        return $this;
    }

    /**
     * Get biddingCount
     *
     * @return string
     */
    public function getBiddingCount()
    {
        return $this->biddingCount;
    }

    /**
     * Set sellPrice
     *
     * @param string $sellPrice
     *
     * @return Report
     */
    public function setSellPrice($sellPrice)
    {
        $this->sellPrice = $sellPrice;

        return $this;
    }

    /**
     * Get sellPrice
     *
     * @return string
     */
    public function getSellPrice()
    {
        return $this->sellPrice;
    }

    /**
     * Set purchasePrice
     *
     * @param string $purchasePrice
     *
     * @return Report
     */
    public function setPurchasePrice($purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    /**
     * Get purchasePrice
     *
     * @return string
     */
    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }

    /**
     * Set guidePrice
     *
     * @param string $guidePrice
     *
     * @return Report
     */
    public function setGuidePrice($guidePrice)
    {
        $this->guidePrice = $guidePrice;

        return $this;
    }

    /**
     * Get guidePrice
     *
     * @return string
     */
    public function getGuidePrice()
    {
        return $this->guidePrice;
    }

    /**
     * Set futurePrice
     *
     * @param string $futurePrice
     *
     * @return Report
     */
    public function setFuturePrice($futurePrice)
    {
        $this->futurePrice = $futurePrice;

        return $this;
    }

    /**
     * Get futurePrice
     *
     * @return string
     */
    public function getFuturePrice()
    {
        return $this->futurePrice;
    }

    /**
     * Set registerDate
     *
     * @param string $registerDate
     *
     * @return Report
     */
    public function setRegisterDate($registerDate)
    {
        $this->registerDate = $registerDate;

        return $this;
    }

    /**
     * Get registerDate
     *
     * @return string
     */
    public function getRegisterDate()
    {
        return $this->registerDate;
    }

    /**
     * Set kilometer
     *
     * @param string $kilometer
     *
     * @return Report
     */
    public function setKilometer($kilometer)
    {
        $this->kilometer = $kilometer;

        return $this;
    }

    /**
     * Get kilometer
     *
     * @return string
     */
    public function getKilometer()
    {
        return $this->kilometer;
    }

    /**
     * Set bjxResult
     *
     * @param array $bjxResult
     *
     * @return Report
     */
    public function setBjxResult($bjxResult)
    {
        $this->bjxResult = $bjxResult;

        return $this;
    }

    /**
     * Get bjxResult
     *
     * @return array
     */
    public function getBjxResult()
    {
        return $this->bjxResult;
    }

    /**
     * Set secReport
     *
     * @param array $secReport
     *
     * @return Report
     */
    public function setSecReport($secReport)
    {
        $this->secReport = $secReport;

        return $this;
    }

    /**
     * Get secReport
     *
     * @return array
     */
    public function getSecReport()
    {
        return $this->secReport;
    }

    /**
     * Set startAt
     *
     * @param \DateTime $startAt
     *
     * @return Report
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set endAt
     *
     * @param \DateTime $endAt
     *
     * @return Report
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * Get endAt
     *
     * @return \DateTime
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * Set rechecker
     *
     * @param \AppBundle\Entity\User $rechecker
     *
     * @return Report
     */
    public function setRechecker(\AppBundle\Entity\User $rechecker = null)
    {
        $this->rechecker = $rechecker;

        return $this;
    }

    /**
     * Get rechecker
     *
     * @return \AppBundle\Entity\User
     */
    public function getRechecker()
    {
        return $this->rechecker;
    }
}
