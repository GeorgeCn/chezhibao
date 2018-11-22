<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller
{
    /**
     * @Security("has_role('ROLE_USER')")
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboardAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('vehicle_report');
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_LOADOFFICER') or $this->get('security.authorization_checker')->isGranted('ROLE_LOADOFFICER_MANAGER') ) {
            return $this->redirectToRoute('vehicle_report');
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_EXAMER_MANAGER')) {
            return $this->redirectToRoute('order_task_list');
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_EXAMER')) {
            return $this->redirectToRoute('order_task');
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_EXAMER_RECHECK')) {
            return $this->redirectToRoute('order_getconfirm'); 
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_EXAMER_HPL')) {
            return $this->redirectToRoute('order_recheck_list');
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN_HPL')) {
            return $this->redirectToRoute('vehicle_report');
        } else {
            return $this->render('dashboard/dashboard.html.twig');
        }
    }

    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        return $this->forward("FOSUserBundle:Security:login");
    }
}
