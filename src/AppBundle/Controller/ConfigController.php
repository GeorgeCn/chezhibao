<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Config;
use AppBundle\Form\ConfigType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Agency;

/**
 * Config controller.
 * @Security("has_role('ROLE_ADMIN') or (has_role('ROLE_USER') and request.get('_route') in ['config_get_all_companies'])")
 * @Route("/config")
 */
class ConfigController extends Controller
{
    /**
     * Lists all Config entities.
     *
     * @Route("/", name="config_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $title = '公司配置';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('AppBundle:Config')->findAll();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit /*limit per page */
        );

        return $this->render('config/index.html.twig', array(
            'title' => $title,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
        ));
    }

    /**
     * Creates a new Config entity.
     *
     * @Route("/new", name="config_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $config = new Config();
        $form = $this->createForm('AppBundle\Form\ConfigType', $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $version = $config->getVersion() + 1;
            $config->setVersion($version);
            $config->setCompanyKey(md5($config->getCompany()));
            $config->setCompanySerect(md5($config->getCompany().rand(1000,2000)));

            $defaultProvince = $this->getDoctrine()->getRepository('AppBundle:Province')->findOneBy(['name' => '上海市']);
            $defaultCity = $this->getDoctrine()->getRepository('AppBundle:City')->findOneBy(['name' => '上海市']);
            $agency = new Agency();
            $agency->setName('管理员')
                ->setCode('admin')
                ->setCreater($this->getUser())
                ->setCompany($config)
                ->setProvince($defaultProvince)
                ->setCity($defaultCity)
            ;

            $em->persist($config);
            $em->persist($agency);
            $em->flush();

            return $this->redirectToRoute('config_index');
        }

        return $this->render('config/new.html.twig', array(
            'config' => $config,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Config entity.
     *
     * @Route("/{id}/edit", name="config_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Config $config)
    {
        $deleteForm = $this->createDeleteForm($config);
        $editForm = $this->createForm('AppBundle\Form\ConfigType', $config);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $version = $config->getVersion() + 1;
            $config->setVersion($version);
            $em->persist($config);
            $em->flush();

            return $this->redirectToRoute('config_index');
        }

        return $this->render('config/edit.html.twig', array(
            'config' => $config,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Config entity.
     *
     * @Route("/{id}/delete", name="config_delete")
     */
    public function deleteAction(Request $request, Config $config)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($config);
        $em->flush();

        $this->addFlash(
            'notice',
            '删除成功'
        );

        return $this->redirectToRoute('config_index');
    }

    /**
     * Creates a form to delete a Config entity.
     *
     * @param Config $config The Config entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Config $config)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('config_delete', array('id' => $config->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * 根据不同的角色获取公司
     * @Route("/getAllCompanies", name="config_get_all_companies")
     * @Method("GET")
     */
    public function getAllCompanysAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($this->isGranted('ROLE_EXAMER') || $this->isGranted('ROLE_EXAMER_RECHECK') ) {
            $companies = $em->getRepository('AppBundle:Config')->findCompanyNames();
        } else {
            $companies = $em->getRepository('AppBundle:AgencyRel')->findCompanyNames($this->getUser());
        } 

        if ($companies) {
            $companies = array_column($companies, 'company');

            return new JsonResponse(array('success' => true, 'results' => $companies));
        } else {
            return new JsonResponse(array('success' => false, 'msg' => '没有公司'));
        }
    }
}
