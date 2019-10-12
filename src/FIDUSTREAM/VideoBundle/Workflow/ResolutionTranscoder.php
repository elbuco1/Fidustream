<?php

namespace FIDUSTREAM\VideoBundle\Workflow;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Psr\Log\LoggerInterface;
use FIDUSTREAM\VideoBundle\Entity\Video;
use Doctrine\ORM\EntityManager;

class ResolutionTranscoder 
{
    
    public function __construct(LoggerInterface $logger, EntityManager $em, $producer, $filesystem)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->producer = $producer;
        $this->filesystem = $filesystem;
    }
    public function setPublicationDate(Video $video)
    {
        $video->setPublicationDate(new \Datetime());
        $this->em->flush();
    }
    public function compute(Video $video)
    {
        $this->logger->info("computing resolutions of: ".$video->getTitle()." has begun");

        $mp4Message = array("id"=>$video->getId(),"originalPath"=> $video->getMp4AbsolutePath(), "dir" => $video->getMp4AbsolutePath(false), "ext" => "mp4");
        $webmMessage = array("id"=>$video->getId(),"originalPath"=> $video->getWebmAbsolutePath(), "dir" => $video->getWebmAbsolutePath(false), "ext" => "webm");

        $this->producer->publish(json_encode(array($mp4Message, $webmMessage)));
       
    }
}