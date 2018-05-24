<?php

namespace DashboardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Service
 *
 * @ORM\Table(name="service")
 * @ORM\Entity(repositoryClass="DashboardBundle\Repository\ServiceRepository")
 */
class Service
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="daemon", type="string", length=255)
     */
    private $daemon;

    /**
     * @ORM\ManyToOne(targetEntity="DashboardBundle\Entity\Server", inversedBy="services")
     * @ORM\JoinColumn(name="server_id", referencedColumnName="id",onDelete="SET NULL")
     */

    private $server;

    /**
     * @return string
     */
    public function getDaemon()
    {
        return $this->daemon;
    }

    /**
     * @param string $daemon
     */
    public function setDaemon($daemon)
    {
        $this->daemon = $daemon;
    }




    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Service
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set serverId
     *
     * @param Server $server
     *
     * @return Service
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Get server
     *
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }
}

