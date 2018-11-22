<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="exam_examer_log",options={"comment":"记录一条单子审核人的操作日志"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ExamerLogRepository")
 * 
 */
class ExamerLog
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
     * @ORM\Column(type="datetime", options={"comment":"开始审核时间"})
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"保存报告时间"})
     */
    private $savedAt;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"comment":"退回次数"})
     */
    private $backTimes;

    public function __construct()
    {
        $this->backTimes = 0;
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
     * @return ExamerLog
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
     * @return ExamerLog
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
     * Set startedAt
     *
     * @param \DateTime $startedAt
     * @return ExamerLog
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * Get startedAt
     *
     * @return \DateTime 
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set savedAt
     *
     * @param \DateTime $savedAt
     * @return ExamerLog
     */
    public function setSavedAt($savedAt)
    {
        $this->savedAt = $savedAt;

        return $this;
    }

    /**
     * Get savedAt
     *
     * @return \DateTime 
     */
    public function getSavedAt()
    {
        return $this->savedAt;
    }

    /**
     * Set backTimes
     *
     * @param integer $backTimes
     * @return ExamerLog
     */
    public function setBackTimes($backTimes)
    {
        $this->backTimes = $backTimes;

        return $this;
    }

    /**
     * Get backTimes
     *
     * @return integer 
     */
    public function getBackTimes()
    {
        return $this->backTimes;
    }
}
