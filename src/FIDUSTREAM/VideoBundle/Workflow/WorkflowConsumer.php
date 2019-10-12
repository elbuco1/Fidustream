<?php

namespace FIDUSTREAM\VideoBundle\Workflow;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqplib\Message\AMQPMessage;
use FIDUSTREAM\VideoBundle\Entity\Video;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Doctrine\ORM\EntityRepository;

class WorkflowConsumer implements ConsumerInterface
{
   public function __construct(EntityManager $em, $workflow, EntityRepository $repository, $filesystem, $kernel )
    {
        $this->em = $em;
        $this->workflow = $workflow;
        $this->repository = $repository;
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    
    public function execute(AMQPMessage $message)
    {
        $this->em->clear();
        $body = json_decode(utf8_decode($message->body),true);
        $success = $body["success"];
        $videoId = $body["id"];
        $errorMessage = $body["error"];
        echo " [x] id: ".$videoId." success: ".$success;
        
        $video = $this->repository->find($videoId);
        if(null === $video)
        {
            throw new Exception("Vidéo convertie mais introuvable dans la base de donnée");
        }
        
        if( $success == 0)
        {
            if($this->workflow->can($video, 'fail'))
            {
                    try
                    {
                        $this->workflow->apply($video, 'fail');
                        $video->setLifecycleMessage($errorMessage);
                        $this->em->flush();
                    }
                    catch(LogicException $e){} 
            }

        }
        

        
        
        if($this->workflow->can($video, 'to_validate'))
        {
                try
                {
                    $this->workflow->apply($video, 'to_validate');
                    $this->em->flush();
                }
                catch(LogicException $e){} 
        }
        return true;
    }
}