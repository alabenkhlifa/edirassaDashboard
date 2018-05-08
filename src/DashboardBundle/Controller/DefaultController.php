<?php

namespace DashboardBundle\Controller;

use DashboardBundle\Entity\Notification;
use DashboardBundle\Entity\Server;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    static $nagios = '192.168.';
    public function indexAction()
    {
        $em=$this->getDoctrine()->getManager();
        $notfications = $em->getRepository('DashboardBundle:Notification')->getLast(10);
        $servercount = $em->getRepository('DashboardBundle:Server')->findCount();

        return $this->render('@Dashboard/Default/layout.html.twig',array('servercount'=>$servercount,'notifications'=>$notfications));
    }

    public function remoteAction()
    {
        $em=$this->getDoctrine()->getManager();
        $servers = $em->getRepository('DashboardBundle:Server')->findAll();
        return $this->render('@Dashboard/Default/remote.html.twig',array('servers'=>$servers));
    }

    public function serverAddAction(Request $request)
    {
        if($request->isMethod('POST')){
            $server = new Server();
            $server->setName($request->get('servername'));
            $server->setIp($request->get('ip_address'));
            $server->setUsername($request->get('username'));
            $server->setPassword($request->get('password'));
            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($server);
                $em->flush();
            }
            catch (\Exception $e){
                return $this->render('@Dashboard/Default/addServer.html.twig',array('error'=>'Duplicated Name or IP Address'));
            }
            return $this->redirectToRoute('dashboard_serverList');
        }
        return $this->render('@Dashboard/Default/addServer.html.twig',array('error'=>null));
    }
    public function serverListAction(){
        $em=$this->getDoctrine()->getManager();
        $servers = $em->getRepository('DashboardBundle:Server')->findAll();
        return $this->render('@Dashboard/Default/listServers.html.twig',array('servers'=>$servers));
    }
    public function serverDeleteAction($id){
        $em=$this->getDoctrine()->getManager();
        $server = $em->getRepository('DashboardBundle:Server')->findOneById($id);
        $em->remove($server);
        $em->flush();
        return $this->redirectToRoute('dashboard_serverList');
    }
    public function monitoringAction()
    {
        return $this->render('@Dashboard/Default/monitoring.html.twig');
    }

    public function ajaxAction(Request $request)
    {
        if($request->isXmlHttpRequest()){
            //make something curious, get some unbelieveable data
            $arrData = ['output' => 'here the result which will appear in div'];
            return new JsonResponse($arrData);
        }

        return $this->render('@Dashboard/Default/ajax.html.twig');

    }
}
