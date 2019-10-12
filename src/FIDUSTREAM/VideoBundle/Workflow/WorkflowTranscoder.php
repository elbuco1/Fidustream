<?php

namespace FIDUSTREAM\VideoBundle\Workflow;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Psr\Log\LoggerInterface;
use FIDUSTREAM\VideoBundle\Entity\Video;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Config\Definition\Exception\Exception;


class WorkflowTranscoder 
{

    private function removeDirectory($path)
    {
        $success = true;
        try
        {
            if($this->filesystem->exists($path))
            {  
                $this->filesystem->remove($path);
            }
            
        }
        catch (IOExceptionInterface $e)
        {
            $success = false;
            echo "An error occurred while deleting your directory at ".$e->getPath();
        } 
        return $success;
    }
    
    private function createDirectory($path, $rights)
    {
        $success = true;
        try
        {
            if(!$this->filesystem->exists($path))
            {
                $this->filesystem->mkdir($path, $rights);
            }
            
        }
        catch (IOExceptionInterface $e)
        {
            $success = false;
            echo "An error occurred while creating your directory at ".$e->getPath();
        } 
        return $success;
    }
    public function __construct(LoggerInterface $logger, EntityManager $em, $producer, $filesystem)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->producer = $producer;
        $this->filesystem = $filesystem;
    }

    public function transcode(Video $video)
    {
        

        $this->logger->info("transcoding of: ".$video->getId()." has begun");
        
        $this->createDirectory($video->getMp4AbsolutePath(false), 0777);
        $this->createDirectory($video->getWebmAbsolutePath(false), 0777);
        $this->logger->info("path".$video->getMp4AbsolutePath(false));

        $message = array(
            "id"=>$video->getId(),
            "originalPath"=> $video->getAbsolutePath(), 
            "mp4Path" => $video->getMp4AbsolutePath(), 
            "webmPath" => $video->getWebmAbsolutePath(),
            "thumbnailPath" => $video->getThumbnail()
            );
       $this->producer->publish(json_encode($message));
       
    }

    public function delete(Video $video)
    {
       $this->removeDirectory($video->getVideoPath());
       
    }       
    
}