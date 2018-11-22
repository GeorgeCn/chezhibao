<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PageView
 *
 * @ORM\Table(indexes={@ORM\Index(name="page_origin", columns={"origin"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageViewRepository")
 */
class PageView
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     * 
     * @ORM\ManyToOne(
     *     targetEntity="User",
     *     inversedBy="PageView"
     * )
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $clientVersion;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $remark = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $ip;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $time;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $deepNum;

    /**
     * @var string
     *
     * @ORM\Column(name="origin", type="string", length=255)
     */
    private $origin;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $draft;

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
     * Set clientVersion
     *
     * @param length=255 $clientVersion
     * @return Temp
     */
    public function setClientVersion($clientVersion)
    {
        $this->clientVersion = $clientVersion;

        return $this;
    }

    /**
     * Get clientVersion
     *
     * @return length=255
     */
    public function getClientVersion()
    {
        return $this->clientVersion;
    }

    /**
     * Set remark
     *
     * @param string $remark
     * @return PageView
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
     * Set ip
     *
     * @param string $ip
     * @return PageView
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set time
     *
     * @param \DateTime $time
     * @return PageView
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime 
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return PageView
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set deepNum
     *
     * @param integer $deepNum
     * @return PageView
     */
    public function setDeepNum($deepNum)
    {
        $this->deepNum = $deepNum;

        return $this;
    }

    /**
     * Get deepNum
     *
     * @return integer 
     */
    public function getDeepNum()
    {
        return $this->deepNum;
    }

    /**
     * Set origin
     *
     * @param string $origin
     * @return AEPageView
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return string 
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set draft
     *
     * @param integer $draft
     * @return PageView
     */
    public function setDraft($draft)
    {
        $this->draft = $draft;

        return $this;
    }

    /**
     * Get draft
     *
     * @return integer 
     */
    public function getDraft()
    {
        return $this->draft;
    }
}
