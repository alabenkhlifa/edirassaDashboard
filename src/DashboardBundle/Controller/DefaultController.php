<?php

namespace DashboardBundle\Controller;

use DashboardBundle\Entity\Notification;
use DashboardBundle\Entity\Server;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use phpseclib\Net\SSH2;
use Unirest;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $sizeFTPFolder = "0 Mo";
        try{
            $ssh = new ssh2('192.168.3.36','22');
            if (!$ssh->login('root', 'ala')) {
                echo "Server Down";
            }
            $sizeFTPFolder = $ssh->exec('du -h /var/ftp/virtual_users/vsftpd | cut -f1 | tail -1');
        }
        catch (\Exception $e){
            var_dump($e);
            die();
        }
        finally{
            $em=$this->getDoctrine()->getManager();
            $notfications = $em->getRepository('DashboardBundle:Notification')->getLast(3);
            $servercount = $em->getRepository('DashboardBundle:Server')->findCount();

            return $this->render('@Dashboard/Default/layout.html.twig',array('servercount'=>$servercount,'notifications'=>$notfications,"ftpSize"=>$sizeFTPFolder));
        }
    }

    public function serviceStatusAction(){
        $em=$this->getDoctrine()->getManager();
        $servers = $em->getRepository('DashboardBundle:Server')->findAll();
        $services = $em->getRepository('DashboardBundle:Service')->findAssigned();
        $runningServices = array();
        $stoppedServices = array();
        foreach ($services as $service){
            $server = $em->getRepository('DashboardBundle:Server')->findOneById($service->getServer());
            $ssh = new ssh2($server->getIp(),'22');
            if (!$ssh->login($server->getUsername(), $server->getPassword())) {
                echo "Server".$server->getIp()." is Down";
                die();
            }
            $result = $ssh->exec('service '.$service->getDaemon().' status');
            if(strpos($result,"stopped")!=false){
                array_push($stoppedServices,$service);
            }
            else{
                array_push($runningServices,$service);
            }
        }
        return $this->render('@Dashboard/Default/statusService.html.twig',array('servers'=>$servers,'runningservices'=>$runningServices));
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

    public function serviceStartAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $service = $em->getRepository('DashboardBundle:Service')->findOneBy(array('id'=>$id));
        $server = $em->getRepository('DashboardBundle:Server')->findOneBy(array('id'=>$service->getServer()));
        $ssh = new ssh2($server->getip(),'22');
        if (!$ssh->login($server->getUsername(), $server->getPassword())) {
            echo "Server ".$server->getIp()." is Down";
        }
        $ssh->exec('service '.$service->getDaemon().' start');
        $notification = new Notification();
        $notification->setContent('Service '.$service->getName().' Started On '.$server->getName().'.');
        $em->persist($notification);
        $em->flush();
        return $this->redirectToRoute('dashboard_serivceStatus');
    }

    public function serviceStopAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $service = $em->getRepository('DashboardBundle:Service')->findOneBy(array('id'=>$id));
        $server = $em->getRepository('DashboardBundle:Server')->findOneBy(array('id'=>$service->getServer()));
        $ssh = new ssh2($server->getip(),'22');
        if (!$ssh->login($server->getUsername(), $server->getPassword())) {
            echo "Server ".$server->getIp()." is Down";
        }
        $ssh->exec('service '.$service->getDaemon().' stop');
        $notification = new Notification();
        $notification->setContent('Service '.$service->getName().' Stopped On '.$server->getName().'.');
        $em->persist($notification);
        $em->flush();
        return $this->redirectToRoute('dashboard_serivceStatus');
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
