<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Traits\DoctrineAwareTrait;

/**
 * @Route("/system_api")
 */
class SystemApiController extends Controller
{
    use DoctrineAwareTrait;

    /**
     * @Route("/sync")
     */
    public function reportAction(Request $request)
    {
        $key = $request->query->get('key');
        $type = $request->query->get('type');
        $number = $request->query->get('number');
        $orderNo = $request->query->get('orderNo');
        $timestamp = $request->query->get('timestamp');
        $sign = $request->query->get('sign');
        if(empty($key) || !in_array($type, [1,2,3]) || empty($orderNo)) {
            return new JsonResponse(['success' => false, 'message' => '参数错误']);
        }
        $companyConfig = $this->getRepo('AppBundle:Config')->findoneby(['companyKey' => $key]);
        if(empty($companyConfig)) {
            return new JsonResponse(['success' => false, 'message' => 'key error!']);
        }
        $systemApiSign = $this->get('util.systemapisign');
        $retSign = $systemApiSign->enSign(
            [
                'key' => $key,
                'type' => $type,
                'number' => $number,
                'orderNo' => $orderNo,
                'timestamp' => $timestamp,
                'serect' => $companyConfig->getCompanySerect()
            ]
        );

        if($sign != $retSign) {
            return new JsonResponse(['success' => false, 'message' => '签名认证错误']);
        }
        $company = $companyConfig->getCompany();

        $syncObject = $this->get('app.business_factory')->getSystemSyncObject($company);
        $container = $this->container;
        $info = [];
        if($type == 1) {
            $info = $syncObject->systemSyncBaseInfo($company, $orderNo, $container, $number);
        } elseif ($type == 2) {
            $info = $syncObject->systemSyncPriceInfo($company, $orderNo, $container, $number);
        } elseif ($type == 3) {
            $info = $syncObject->systemSyncPicturesInfo(
                $company,
                $orderNo,
                $this->get('app.business_factory')->getMetadataManager($company),
                $container,
                $number
            );
        }
        if(empty($info)) {
            return new JsonResponse(['success' => false, 'message' => '查询信息错误，请检测参数是否正确！']);
        }
        return new JsonResponse(['success' => true, 'return' => $info]);
    }

    /**
     * @Route("/brand")
     */
    public function getBrandAction(Request $request)
    {
        $key = $request->query->get('key');
        $timestamp = $request->query->get('timestamp');
        $sign = $request->query->get('sign');
        $companyConfig = $this->getRepo('AppBundle:Config')->findoneby(['companyKey' => $key]);
        if(empty($companyConfig)) {
            return new JsonResponse(['success' => false, 'message' => 'key error!']);
        }
        $systemApiSign = $this->get('util.systemapisign');
        $retSign = $systemApiSign->enSign(
            [
                'key' => $key,
                'timestamp' => $timestamp,
                'serect' => $companyConfig->getCompanySerect()
            ]
        );
        if($sign != $retSign) {
            return new JsonResponse(['success' => false, 'message' => '签名认证错误']);
        }
        $company = $companyConfig->getCompany();

        $syncObject = $this->get('app.business_factory')->getSystemSyncObject($company);
        $container = $this->container;

        $info = $syncObject->getAllBrand($container);

        return new JsonResponse(['success' => true, 'return' => $info]);
    }

    /**
     * @Route("/series")
     */
    public function seriesAction(Request $request)
    {
        $key = $request->query->get('key');
        $brandid = $request->query->get('brandid');
        $timestamp = $request->query->get('timestamp');
        $sign = $request->query->get('sign');
        $companyConfig = $this->getRepo('AppBundle:Config')->findoneby(['companyKey' => $key]);
        if(empty($companyConfig)) {
            return new JsonResponse(['success' => false, 'message' => 'key error!']);
        }
        $systemApiSign = $this->get('util.systemapisign');
        $retSign = $systemApiSign->enSign(
            [
                'key' => $key,
                'brandid' => $brandid,
                'timestamp' => $timestamp,
                'serect' => $companyConfig->getCompanySerect()
            ]
        );
        if($sign != $retSign) {
            return new JsonResponse(['success' => false, 'message' => '签名认证错误']);
        }
        $company = $companyConfig->getCompany();

        $syncObject = $this->get('app.business_factory')->getSystemSyncObject($company);
        $container = $this->container;

        $info = $syncObject->getSeriesByBrand($container, $brandid);

        return new JsonResponse(['success' => true, 'return' => $info]);
    }

    /**
     * @Route("/year")
     */
    public function yearForSeriesAction(Request $request)
    {
        $key = $request->query->get('key');
        $sereisid = $request->query->get('sereisid');
        $timestamp = $request->query->get('timestamp');
        $sign = $request->query->get('sign');
        $companyConfig = $this->getRepo('AppBundle:Config')->findoneby(['companyKey' => $key]);
        if(empty($companyConfig)) {
            return new JsonResponse(['success' => false, 'message' => 'key error!']);
        }
        $systemApiSign = $this->get('util.systemapisign');
        $retSign = $systemApiSign->enSign(
            [
                'key' => $key,
                'sereisid' => $sereisid,
                'timestamp' => $timestamp,
                'serect' => $companyConfig->getCompanySerect()
            ]
        );
        if($sign != $retSign) {
            return new JsonResponse(['success' => false, 'message' => '签名认证错误']);
        }
        $company = $companyConfig->getCompany();

        $syncObject = $this->get('app.business_factory')->getSystemSyncObject($company);
        $container = $this->container;

        $info = $syncObject->getYearBySeries($container, $sereisid);

        return new JsonResponse(['success' => true, 'return' => $info]);
    }

    /**
     * @Route("/model")
     */
    public function modelByYearAndSeriesAction(Request $request)
    {
        $key = $request->query->get('key');
        $sereisid = $request->query->get('sereisid');
        $year = $request->query->get('year');
        $timestamp = $request->query->get('timestamp');
        $sign = $request->query->get('sign');
        $companyConfig = $this->getRepo('AppBundle:Config')->findoneby(['companyKey' => $key]);
        if(empty($companyConfig)) {
            return new JsonResponse(['success' => false, 'message' => 'key error!']);
        }
        $systemApiSign = $this->get('util.systemapisign');
        $retSign = $systemApiSign->enSign(
            [
                'key' => $key,
                'year' => $year,
                'sereisid' => $sereisid,
                'timestamp' => $timestamp,
                'serect' => $companyConfig->getCompanySerect()
            ]
        );
        if($sign != $retSign) {
            return new JsonResponse(['success' => false, 'message' => '签名认证错误']);
        }
        $company = $companyConfig->getCompany();

        $syncObject = $this->get('app.business_factory')->getSystemSyncObject($company);
        $container = $this->container;

        $info = $syncObject->getModelByYearAndSeries($container, $year, $sereisid);

        return new JsonResponse(['success' => true, 'return' => $info]);
    }
}