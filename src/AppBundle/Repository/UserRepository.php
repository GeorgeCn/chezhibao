<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    /**
     * 用户查询
     */
    public function findUser($userId = null, $agencyId = null, $name = null, $mobile = null, $username = null, $companyId = null, $roles = null)
    {
        
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.agencyRels', 'ua')
            ->orderBy('u.id', 'ASC')
        ;

        if (!empty($userId)){
            $qb->andWhere('u.id != :userId')
                ->setParameter('userId', $userId)
            ;
        }

        if (!empty($agencyId)){
            $qb->andWhere('ua.agency = :agencyId')
                ->setParameter('agencyId', $agencyId)
            ;
        }

        if (!empty($companyId)){
            $qb->andWhere('ua.company = :companyId')
                ->setParameter('companyId', $companyId)
            ;
        }

        if (!empty($name)){
            $qb->andWhere('u.name like :name' )
                ->setParameter('name', '%'.$name.'%')
            ;
        }

        if (!empty($mobile)){
            $qb->andWhere('u.mobile like :mobile' )
                ->setParameter('mobile', '%'.$mobile.'%')
            ;
        }

        if (!empty($username)){
            $qb->andWhere('u.username like :username')
                ->setParameter('username', '%'.$username.'%')
            ;
        }

        if (!empty($roles)) {
            $qb->andWhere('u.roles in (:roles)')
                ->setParameter('roles', $roles)
            ;
        }

        return $qb->getQuery();
    }

    /**
     * 手机号码和用户名都需要互相查询看是否有重复，有id的是编辑模式，需排除自身，新增模式不需排除自身
     */
    public function findUniqueEntity(array $parameter)
    {
        $username = isset($parameter['username']) ? $parameter['username'] : null;
        $mobile = isset($parameter['mobile']) ? $parameter['mobile'] : null;
        $id = isset($parameter['id']) ? $parameter['id'] : null ;

        $qb = $this->createQueryBuilder('u');

        if ($id) {
            $qb->andWhere('u.id != :id')->setParameter('id', $id);
        }

        if ($username) {
            $qb->andWhere('u.mobile = :username')
                ->setParameter('username', $username)
            ;
        }

        if ($mobile) {
            $qb->andWhere('u.username = :mobile')
                ->setParameter('mobile', $mobile)
            ;
        }

        return $qb->getQuery()->getResult();
    }
    /**
     * 用户名和手机号码都允许登录
     */
    public function findUserByUsernameOrMobile($usernameOrMobile)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.username = :usernameOrMobile or u.mobile = :usernameOrMobile')
            ->setParameter('usernameOrMobile', $usernameOrMobile)
        ;

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * 注册第一步需要验证手机号是否在系统中重复
     */
    public function findUserByMobile($mobile)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.username = :mobile or u.mobile = :mobile')
            ->setParameter('mobile', $mobile)
        ;

        // return $qb->getQuery()->getOneOrNullResult();
        return $qb->getQuery()->getResult();
    }

    /**
     * 根据company和角色查找用户
     * @param $role
     * @param $companyCode
     * @return array
     */
    public function findUserByCompanyAndRole($role, $companyId = null)
    {
        $qb = $this->createQueryBuilder('u')
            ->join('u.agencyRels', 'ua')
            ->andWhere('u.roles like :roles')
            ->setParameter('roles','%'.$role.'%')
        ;
        if(null != $companyId){
            $qb ->andWhere('ua.company = :companyId')
                ->setParameter('companyId', $companyId)
            ;
        }
        return $qb->getQuery()->getArrayResult();
    }

    /**
     * 获取可以接收短信审核师的电话号码
     */
    public function findNeedSendExamerMobile()
    {
        $qb = $this->createQueryBuilder('u')
            ->select(array('u.mobile'))
            ->where('u.examerReceive = 1 and u.mobile is not null')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取指定用户
     */
    public function findSpecialUser($roles)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.enabled = 1')
            ->andWhere('u.roles in (:roles)')
            ->setParameter('roles', $roles)
        ;

        return $qb->getQuery();
    }
}
