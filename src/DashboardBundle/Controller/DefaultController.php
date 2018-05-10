<?php

namespace DashboardBundle\Controller;

use DashboardBundle\Entity\Notification;
use DashboardBundle\Entity\Server;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Unirest;

class DefaultController extends Controller
{
    static $nagios = '192.168.';
    public function indexAction()
    {
        $em=$this->getDoctrine()->getManager();
        $notfications = $em->getRepository('DashboardBundle:Notification')->getLast(5);
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
            $notification = new Notification();
            $notification->setContent("Server: Name: ".$server->getName()." , IP: ".$server->getIp()." Added.");
            $em->persist($notification);
            $em->flush();
            return $this->redirectToRoute('dashboard_serverList');
        }
        return $this->render('@Dashboard/Default/addServer.html.twig',array('error'=>null));
    }
    public function serverListAction(){
        $em=$this->getDoctrine()->getManager();
        $servers = $em->getRepository('DashboardBundle:Server')->findAll();
        return $this->render('@Dashboard/Default/listServers.html.twig',array('servers'=>$servers));
    }

    //ToDo:AssignServiceToServer
    public function serviceAssignAction(Request $request,$id)
    {
        $em=$this->getDoctrine()->getManager();
        $server = $em->getRepository('DashboardBundle:Server')->findOneById($id);
        $services = $em->getRepository('DashboardBundle:Service')->findAll();
        if($request->isMethod('POST')){
            $servicesSelectedIds = $request->get('services');
            $servicesPrevSel =$em->getRepository('DashboardBundle:Service')->findByServer($id);
            foreach ($servicesPrevSel as $ServicePrev){
                $ServicePrev->setServer(null);
                $em->merge($ServicePrev);
                $em->flush();
            }
            foreach ($servicesSelectedIds as $idserviceSelected){
                /*$service = $em->getReference("DashboardBundle:Service", $idserviceSelected);
                $server->getservices()->add($service);*/
                $service = $em->getRepository('DashboardBundle:Service')->findOneById($idserviceSelected)->setServer($server);
                $em->merge($service);
                $em->flush();
            }
            return $this->redirectToRoute('dashboard_serverList');
        }
        return $this->render('@Dashboard/Default/serviceAssign.html.twig',array("server"=>$server,"services"=>$services));
    }
    public function serverDeleteAction($id){
        $em=$this->getDoctrine()->getManager();
        $server = $em->getRepository('DashboardBundle:Server')->findOneById($id);
        $em->remove($server);
        $notification = new Notification();
        $notification->setContent("Server: Name:.".$server->getName()." , IP: ".$server->getIp()." Deleted.");
        $em->persist($notification);
        $em->flush();
        return $this->redirectToRoute('dashboard_serverList');
    }
    // ToDo:Monitoring
    public function monitoringAction()
    {
        /*
        Unirest\Request::auth('nagiosadmin', '0000');
        $response = Unirest\Request::get($this::$nagios, null, null);
        var_dump($response->body);
        exit(0);
        */
        $em=$this->getDoctrine()->getManager();
        $servers = $em->getRepository('DashboardBundle:Server')->findAll();
        return $this->render('@Dashboard/Default/monitoring.html.twig',array('servers'=>$servers));    }

    public function ajaxAction(Request $request)
    {
        if($request->isXmlHttpRequest()){
            $arrData = ['output' => 'here the result which will appear in div'];
            return new JsonResponse($arrData);
        }
        return $this->render('@Dashboard/Default/ajax.html.twig');

    }
}
