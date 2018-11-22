<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Agency;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Agency controller.
 * @Route("agency")
 * @Security("has_role('ROLE_ADMIN_HPL') or (has_role('ROLE_USER') and request.get('_route') in ['get_agencies'])")
 */
class AgencyController extends Controller
{
    /**
     * Lists all agency entities.
     * 
     * @Route("/", name="agency_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $title = '经销商列表';
        $vars['code'] = $request->query->get('vars')['code'];
        $vars['company'] = $request->query->get('vars')['company'];
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        if ($this->isGranted('ROLE_ADMIN')) {
            $company = null;
        } else {
            $company = $this->getUser()->getAgencyRels()[0]->getCompany()->getCompany();
        }

        $query = $this->getDoctrine()->getRepository('AppBundle:Agency')
            ->findAgency($query = true, $vars['company'] ?: $company, $vars['code'] ?: '');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('agency/index.html.twig', array(
            'title' => $title,
            'vars' => $vars,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
        ));
    }

    /**
     * Creates a new agency entity.
     *
     * @Route("/new", name="agency_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $agency = new Agency();
        $form = $this->createForm('AppBundle\Form\AgencyType', $agency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $agency->setCreater($this->getUser());
            if(!$this->isGranted('ROLE_ADMIN')) {
                $agency->setCompany($this->getUser()->getAgencyRels()[0]->getCompany());
            }
            $em->persist($agency);
            $em->flush($agency);

            return $this->redirectToRoute('agency_index');
        }

        return $this->render('agency/new.html.twig', array(
            'agency' => $agency,
            'form' => $form->createView(),
        ));
    }


    /**
     * Displays a form to edit an existing agency entity.
     *
     * @Route("/{id}/edit", name="agency_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Agency $agency)
    {
        $editForm = $this->createForm('AppBundle\Form\AgencyType', $agency);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $agency->setCreater($this->getUser());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('agency_index');
        }

        return $this->render('agency/edit.html.twig', array(
            'agency' => $agency,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="agency_delete")
     */
    public function deleteAction(Request $request, Agency $agency)
    {
        $em = $this->getDoctrine()->getManager();
        $agencyRel = $em->getRepository('AppBundle:AgencyRel')->findOneBy(['agency' => $agency]);
        if ($agencyRel) {
            $this->addFlash(
                'notice',
                '该经销商下面已经有用户，不能删除'
            );

            return $this->redirectToRoute('agency_index');
        }

        $em->remove($agency);
        $em->flush();

        $this->addFlash(
            'notice',
            '删除成功'
        );

        return $this->redirectToRoute('agency_index');
    }

    /**
     * 获取金融公司下面的经销商
     * @Route("/getAgencies", name="get_agencies")
     * @Method("GET")
     */
    public function getAgenciesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $companyName = $request->query->get('company');
        if ($companyName) {
            $company = $em->getRepository('AppBundle:Config')->findOneBy(['company' => $companyName]);
        } else {
            $company = null;
        }

        $agencies = [];
        if ($this->isGranted('ROLE_EXAMER')) {
            $agencies = $em->getRepository('AppBundle:Agency')->findAgencyNames($company);
        } elseif($this->isGranted('ROLE_ADMIN_HPL') || $this->isGranted('ROLE_EXAMER_HPL')) {
            $agencies = $em->getRepository('AppBundle:Agency')->findAgencyNames($company ?: $this->getUser()->getAgencyRels()[0]->getCompany());
        } elseif($this->isGranted('ROLE_LOADOFFICER_MANAGER')) {
            $agencyRels = $this->getUser()->getAgencyRels();
            foreach ($agencyRels as $agencyRel) {
                $ownCompany = $agencyRel->getCompany();
                $ownAgency = $agencyRel->getAgency()->getName();
                $ret = $em->getRepository('AppBundle:Agency')->findAgencyNames($company ?: $ownCompany, $ownAgency);
                $agencies = array_merge($agencies, $ret);
            }
        } else {
            $agencies = $em->getRepository('AppBundle:AgencyRel')->findAgencyNames($this->getUser(), $company ?: null);
        }

        if ($agencies) {
            $agencies = array_column($agencies, 'name');

            return new JsonResponse(array('success' => true, 'results' => $agencies));
        } else {
            return new JsonResponse(array('success' => false, 'msg' => '没有经销商'));
        }
    }
}
