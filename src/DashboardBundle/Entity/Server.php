<?php
/**
 * Created by PhpStorm.
 * User: ALA
 * Date: 06/05/2018
 * Time: 01:18
 */


namespace DashboardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="DashboardBundle\Repository\ServerRepository")
 * @ORM\Table(name="Server")
 */
class Server
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30 , nullable=false, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=16 ,nullable=false, unique=true)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=50 ,nullable=false, unique=false)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=50 ,nullable=false, unique=false)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="DashboardBundle\Entity\Service", mappedBy="server")
     */
    private $services;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return ArrayCollection
     */
    public function getservices()
    {
        return $this->services;
    }

    /**
     * @param ArrayCollection $services
     */
    public function setservices($services)
    {
        $this->services = $services;
    }

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }


}