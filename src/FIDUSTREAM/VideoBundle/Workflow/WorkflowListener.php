<?php

namespace FIDUSTREAM\VideoBundle\Workflow;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

use FIDUSTREAM\VideoBundle\Entity\Video;

class WorkflowListener implements EventSubscriberInterface
{
   

    public function __construct(WorkflowTranscoder $workflowTranscoder, ResolutionTranscoder $resolutionTranscoder)
    {
        $this->workflowTranscoder = $workflowTranscoder;
        $this->resolutionTranscoder = $resolutionTranscoder;
    }
    public function setPublicationDate(Event $event)
    {
        $this->resolutionTranscoder->setPublicationDate( $event->getSubject());
    }

    public function processTranscoding(Event $event)
    {
        $this->workflowTranscoder->transcode( $event->getSubject());
    }
    

    public function processComputing(Event $event)
    {
        $this->resolutionTranscoder->compute( $event->getSubject());
    }

    public function processDeletion(Event $event)
    {
       $this->workflowTranscoder->delete($event->getSubject());
    }

    public static function getSubscribedEvents()
    {
        return array(
             'workflow.video_publishing.enter.published' => 'setPublicationDate',
             'workflow.video_publishing.enter.transcoding' => 'processTranscoding',
             'workflow.video_publishing.enter.resolutions_computing' => 'processComputing',
             'workflow.video_publishing.transition.delete' => 'processDeletion',
             'workflow.video_publishing.transition.abort' => 'processDeletion',
             'workflow.video_publishing.transition.reject' => 'processDeletion',
             'workflow.video_publishing.transition.cancel' => 'processDeletion'
        );
    }
}