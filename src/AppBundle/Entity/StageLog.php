<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="exam_stage_log",options={"comment":"记录审核页面不同阶段日志"}, indexes={
        @ORM\Index(name="idx_report_id", columns={"report_id"}),
        @ORM\Index(name="idx_examer_id", columns={"examer_id"}),
    })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StageLogRepository")
 * 
 */
class StageLog
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", options={"comment":"报告单号id"})
     */
    private $reportId;

    /**
     * @ORM\Column(type="integer", options={"comment":"审核人id"})
     */
    private $examerId;

    /**
     * @ORM\Column(type="datetime", options={"comment":"创建每条记录的时间"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="smallint", options={"comment":"记录审核所处的阶段"})
     */
    private $stage;

    /**
     * @ORM\Column(type="smallint", options={"comment":"日志记录类型 1为加锁时记录，2审核阶段，3为解锁记录"})
     */
    private $type;
    const TYPE_LOCK = 1;
    const TYPE_AUDIT = 2;
    const TYPE_UNLOCK = 3;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * Set reportId
     *
     * @param integer $reportId
     *
     * @return StageLog
     */
    public function setReportId($reportId)
    {
        $this->reportId = $reportId;

        return $this;
    }

    /**
     * Get reportId
     *
     * @return integer
     */
    public function getReportId()
    {
        return $this->reportId;
    }

    /**
     * Set examerId
     *
     * @param integer $examerId
     *
     * @return StageLog
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return StageLog
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
     * Set stage
     *
     * @param integer $stage
     *
     * @return StageLog
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
     * Set type
     *
     * @param integer $type
     *
     * @return StageLog
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
}
