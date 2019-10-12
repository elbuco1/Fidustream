<?php

namespace FIDUSTREAM\VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

use FIDUSTREAM\VideoBundle\Entity\Video;
use FIDUSTREAM\UserBundle\Entity\User;
use FIDUSTREAM\VideoBundle\Form\VideoType;

class VideoController extends Controller
{
    private $resolutions = [ "_1080p", "_720p", "_480p", "_360p"];

    /**
    * @Security("has_role('ROLE_VIEWER')")
    */
        public function indexAction(Request $request)
    { 
        return $this->render('FIDUSTREAMVideoBundle:Video:index.html.twig');
    }

    /**
    * @Security("has_role('ROLE_CONTRIBUTOR')")
    */
    public function uploadedAction(Request $request)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('FIDUSTREAMVideoBundle:Video');
        $results=$repository->findUploadedVideo($this->getUser()->getId()); 
        return $this->render('FIDUSTREAMVideoBundle:Video:uploaded.html.twig',array('results'=> $results)); 
    }

    /**
    * @Security("has_role('ROLE_MODERATOR')")
    */
    public function moderatorAction(Request $request)
    {   
        $repository = $this->getDoctrine()->getManager()->getRepository('FIDUSTREAMVideoBundle:Video');
        $results=$repository->findVideoToValidate($this->getUser()->getId()); 
        return $this->render('FIDUSTREAMVideoBundle:Video:moderator.html.twig',array('results'=> $results)); 
    }

    /**
    * @Security("has_role('ROLE_CONTRIBUTOR')")
    */
    public function rejectedAction($id, Request $request)
    { 
        $repository = $this->getDoctrine()->getManager()->getRepository('FIDUSTREAMVideoBundle:Video');
        $video = $repository->find($id);
        if(null === $video)
        {
            throw new NotFoundHttpException("La vidéo n'est pas disponible");
        } 
        if($this->getUser()->getId() != $video->getContributor()->getId())
        {
            throw new AccessDeniedException("Vous n'avez pas les droits");
        }
        $tagsStr=$video->getTags();
        $tags= explode(",", $tagsStr);

        return $this->render('FIDUSTREAMVideoBundle:Video:rejected.html.twig',array("video" => $video, "tags"=>$tags)); 
    }

    /**
    * @Security("has_role('ROLE_CONTRIBUTOR')")
    */
    public function refactorAction($id, Request $request)
    { 
        $repository = $this->getDoctrine()->getManager()->getRepository('FIDUSTREAMVideoBundle:Video');
        $video = $repository->find($id);
        if(null === $video)
        {
            throw new NotFoundHttpException("La vidéo n'est pas disponible");
        } 
        if($this->getUser()->getId() != $video->getContributor()->getId())
        {
            throw new AccessDeniedException("Vous n'avez pas les droits");
        }

        return $this->render('FIDUSTREAMVideoBundle:Video:refactor.html.twig',array("video" => $video)); 
    }

    /**
    * @Security("has_role('ROLE_VIEWER')")
    */
    public function playerAction($id, Request $request)
    {   

        $logger = $this->get('logger');
        $repository = $this->getDoctrine()->getManager()->getRepository('FIDUSTREAMVideoBundle:Video');
        $video = $repository->find($id);
        if(null === $video)
        {
            throw new NotFoundHttpException("La vidéo n'est pas disponible");
        }
        $logger->info($this->getUser()->getAuthenticationLevel());
        $logger->info($video->getAuthenticationLevel());
        if($this->getUser()->getAuthenticationLevel() > $video->getAuthenticationLevel())
        {
            throw new AccessDeniedException("Vous n'avez pas les droits");
        }
        $availableRes = [];
        $logger->info(count($this->resolutions));
        $logger->info($video->getMaxQuality());
        if ($video->getMaxQuality() !== null )
        {
            $logger->info(array_search($video->getMaxQuality(), $this->resolutions));
            $availableRes = array_slice($this->resolutions, array_search($video->getMaxQuality(), $this->resolutions) );
        }
        $tagsStr=$video->getTags();
        $tags= explode(",", $tagsStr);
       

        if($request->isMethod('POST'))
        {
            $workflow = $this->container->get('state_machine.video_publishing');
            if($workflow->can($video, 'accept'))
            {
                try
                {
                    if($request->request->get("action") === "accept")
                    {
                        $workflow->apply($video, 'accept');
                    }
                    else if($request->request->get("action") === "modify")
                    {
                        $video->setLifecycleMessage($request->request->get("message"));
                        $workflow->apply($video, 'refactor');
                    }
                    else
                    {
                        $video->setLifecycleMessage($request->request->get("message"));
                        $workflow->apply($video, 'reject');
                    }
                
                    
                    $entityManager= $this->getDoctrine()->getManager();
                    $entityManager->persist($video);
                    $entityManager->flush();
                }
                catch(LogicException $e){}                
            }

        }
        
        return $this->render('FIDUSTREAMVideoBundle:Video:player.html.twig',array('video'=>$video, 'resolutions'=>$availableRes, 'tags'=>$tags)); 
    }

    /**
    * @Security("has_role('ROLE_CONTRIBUTOR')")
    */
    public function uploadAction(Request $request)
    {
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid() )
        {         
            $workflow = $this->container->get('state_machine.video_publishing');   
            $entityManager= $this->getDoctrine()->getManager();
            $video->setContributor($this->getUser());
            if($this->getUser()->hasRole('ROLE_ADMIN'))
            {
                $video->setValidator($this->getUser());
            }
            else
            {
                $video->setValidator($this->getUser()->getModerator());
            }
            $entityManager->persist($video);
            if($workflow->can($video, 'upload'))
            { 
                try
                {
                   $workflow->apply($video, 'upload');
                   
                }
                catch(LogicException $e){}                
            }
            $entityManager->flush();
            return $this->redirectToRoute('fidustream_video_resume', array('id' => $video->getId()));
        }

       return $this->render('FIDUSTREAMVideoBundle:Video:upload.html.twig',array(
           'form' => $form->createView(),
       ));
    }
    
    /**
    * @Security("has_role('ROLE_VIEWER')")
    */
    public function searchAction(Request $request)
    {
        $search = $request->query->get('search');
        $repository = $this->getDoctrine()->getManager()->getRepository('FIDUSTREAMVideoBundle:Video');
        $recent = $repository->latestVideo($this->getUser()->getAuthenticationLevel());
        if($search === null )
        {
            return $this->render('FIDUSTREAMVideoBundle:Video:search.html.twig',array('recent'=>$recent));
        }
        
        $results = $repository->contains($search, $this->getUser()->getAuthenticationLevel());
        
        return $this->render('FIDUSTREAMVideoBundle:Video:search.html.twig',array('keyword'=>$search , 'results'=>$results , 'recent'=>$recent));
    }


     /**
    * @Security("has_role('ROLE_CONTRIBUTOR')")
    */
    public function resumeAction($id, Request $request)
     {   
        $logger = $this->get('logger');
        $repository = $this->getDoctrine()->getManager()->getRepository('FIDUSTREAMVideoBundle:Video');
        $video = $repository->find($id);
        if(null === $video)
        {
            throw new NotFoundHttpException("La vidéo n'est pas disponible");
        } 
        $tagsStr=$video->getTags();
        $tags= explode(",", $tagsStr);
        if($request->isMethod('POST'))
        {
            $workflow = $this->container->get('state_machine.video_publishing');  

            try
            {
                if($request->request->get("action") === "transcode" && $workflow->can($video, 'transcode'))
                {
                    $workflow->apply($video, 'transcode');
                }
                elseif( $request->request->get("action") === "retranscode" && $workflow->can($video, 'retranscode'))
                {
                    $workflow->apply($video, 'retranscode');
                }
                elseif( $request->request->get("action") === "delete" && $workflow->can($video, 'delete'))
                {
                    $workflow->apply($video, 'delete');
                }
                elseif( $request->request->get("action") === "recompute" && $workflow->can($video, 'recompute'))
                {
                    $workflow->apply($video, 'recompute');
                }
                elseif( $request->request->get("action") === "abort" && $workflow->can($video, 'abort'))
                {
                    $workflow->apply($video, 'abort');
                }
                elseif( $request->request->get("action") === "resubmit" && $workflow->can($video, 'resubmit'))
                {
                    $video->setTitle($request->request->get("title"));
                    $video->setDescription($request->request->get("description"));
                    $video->setTags($request->request->get("tags"));
               
                    $workflow->apply($video, 'resubmit');
                }
                elseif( $request->request->get("action") === "cancel" && $workflow->can($video, 'cancel'))
                {
                    $workflow->apply($video, 'cancel');
                }
                $entityManager= $this->getDoctrine()->getManager();              
                $entityManager->flush();
            }
            catch(LogicExpression $e){}
            
        }         
        return $this->render('FIDUSTREAMVideoBundle:Video:resume.html.twig',array('video'=>$video, 'tags'=>$tags)); 
    }
    
}

