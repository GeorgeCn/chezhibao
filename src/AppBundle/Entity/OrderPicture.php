<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="exam_order_picture", options={"comment":"检测订单提交拍照操作记录"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderPictureRepository")
 */
class OrderPicture
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '信贷员'")
     */
    private $loadOfficer;

    /**
     * @ORM\ManyToOne(targetEntity="Order")
     * @ORM\JoinColumn(columnDefinition="INT COMMENT '信贷员提交的订单'")
     */
    private $order;

    /**
     * @ORM\Column(type="datetime", options={"comment":"创建时间"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", options={"comment":"拍照时间", "default":0})
     */
    private $pictureAt;


    /**
     * @ORM\Column(type="string", options={"comment":"检测订单的图片metadataKey"})
     */
    private $pictureKey;

    /**
     * @ORM\Column(type="string", options={"comment":"检测订单的图片名称"})
     */
    private $pictureName;

    /**
     * @ORM\Column(type="string", options={"comment":"检测订单的图片来源"})
     */
    private $pictureOrigin;

    /**
     * @ORM\Column(type="smallint", options={"comment":"重拍次数"})
     */
    private $rephotographTimes;

    /**
     * @ORM\Column(type="smallint", options={"comment":"校验次数"})
     */
    private $verifyTimes;

    /**
     * @ORM\Column(type="string", length=255, options={"comment":"经度"})
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=255, options={"comment":"纬度"})
     */
    private $latitude;


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
     * @return OrderPicture
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
     * Set loadOfficer
     *
     * @param \AppBundle\Entity\User $loadOfficer
     * @return OrderPicture
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
     * Set Order
     *
     * @param \AppBundle\Entity\Order $order
     * @return OrderPicture
     */
    public function setOrder(\AppBundle\Entity\Order $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get Order
     *
     * @return \AppBundle\Entity\Order 
     */
    public function getOrder()
    {
        return $this->order;
    }


    /**
     * Set pictureAt
     *
     * @param \DateTime $pictureAt
     * @return OrderPicture
     */
    public function setPictureAt($pictureAt)
    {
        $this->pictureAt = $pictureAt;

        return $this;
    }

    /**
     * Get pictureAt
     *
     * @return \DateTime 
     */
    public function getPictureAt()
    {
        return $this->pictureAt;
    }


    /**
     * Set pictureKey
     *
     * @param string $pictureKey
     * @return OrderPicture
     */
    public function setPictureKey($pictureKey)
    {
        $this->pictureKey = $pictureKey;

        return $this;
    }

    /**
     * Get pictureKey
     *
     * @return string 
     */
    public function getPictureKey()
    {
        return $this->pictureKey;
    }

    /**
     * Set pictureName
     *
     * @param string $pictureName
     * @return OrderPicture
     */
    public function setPictureName($pictureName)
    {
        $this->pictureName = $pictureName;

        return $this;
    }

    /**
     * Get pictureName
     *
     * @return string 
     */
    public function getPictureName()
    {
        return $this->pictureName;
    }

    /**
     * Set pictureOrigin
     *
     * @param string $pictureOrigin
     * @return OrderPicture
     */
    public function setPictureOrigin($pictureOrigin)
    {
        $this->pictureOrigin = $pictureOrigin;

        return $this;
    }

    /**
     * Get pictureOrigin
     *
     * @return string 
     */
    public function getPictureOrigin()
    {
        return $this->pictureOrigin;
    }

    /**
     * Set rephotographTimes
     *
     * @param smallint $rephotographTimes
     * @return OrderPicture
     */
    public function setRephotographTimes($rephotographTimes)
    {
        $this->rephotographTimes = $rephotographTimes;

        return $this;
    }

    /**
     * Get rephotographTimes
     *
     * @return smallint 
     */
    public function getRephotographTimes()
    {
        return $this->rephotographTimes;
    }

    /**
     * Set verifyTimes
     *
     * @param smallint $verifyTimes
     * @return OrderPicture
     */
    public function setVerifyTimes($verifyTimes)
    {
        $this->verifyTimes = $verifyTimes;

        return $this;
    }

    /**
     * Get verifyTimes
     *
     * @return smallint 
     */
    public function getVerifyTimes()
    {
        return $this->verifyTimes;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return OrderPicture
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
     * @return OrderPicture
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

}
