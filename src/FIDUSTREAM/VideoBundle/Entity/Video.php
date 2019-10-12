<?php

namespace FIDUSTREAM\VideoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Video
 *
 * @ORM\Table(name="video")
 * @ORM\Entity(repositoryClass="FIDUSTREAM\VideoBundle\Repository\VideoRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Video
{
    /**
     * @var string
     *
     * @ORM\Column(name="current_place", type="string", length=255)
     */
    public $currentPlace;
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
     * @ORM\Column(name="title", type="string", length=50)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="tags", type="string", length=255, nullable=true)
     * @Assert\Regex("/^\w+/")
     */
    private $tags;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="upload_date", type="datetime")
     */
    private $uploadDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publication_date", type="datetime", nullable=true)
     */
    private $publicationDate;
    /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=255)
     */
    private $extension;

    /**
     * @var string
     *
     * @ORM\Column(name="max_quality", type="string", length=255, nullable=true)
     */
    private $maxQuality;
    
    /**
     * @ORM\ManyToOne(targetEntity="FIDUSTREAM\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
     private $contributor;


     /**
     * @ORM\ManyToOne(targetEntity="FIDUSTREAM\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
     private $validator;

    // /**
    //  * @ORM\ManyToOne(targetEntity="FIDUSTREAM\UserBundle\Entity\Level")
    //  * @ORM\JoinColumn(nullable=true)
    //  */
    //  private $authenticationLevel;

    /**
     * @var integer
     *
     * @ORM\Column(name="authentication_level", type="integer", nullable=false)
     */
     private $authenticationLevel;
     private $file;

     private $tempFileName;

     /**
     * @var string
     *
     * @ORM\Column(name="lifecycle_message", type="string", length=255, nullable=true)
     */
     private $lifecycleMessage;

     

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
     * Set title
     *
     * @param string $title
     *
     * @return Video
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Video
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }



    /**
     * Set tags
     *
     * @param string $tags
     *
     * @return Video
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set uploadDate
     *
     * @param \DateTime $uploadDate
     *
     * @return Video
     */
    public function setUploadDate($uploadDate)
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    /**
     * Get uploadDate
     *
     * @return \DateTime
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    

    
    public function getOriginalPath()
    {
        return $this->getUploadDirectory().$this->contributor->getId().'/'.$this->id.'/original/'.$this->id.'.'.$this->extension;
    }
    
    public function getMp4Path($resolution = "")
    {
        return $this->getUploadDirectory().$this->contributor->getId().'/'.$this->id.'/mp4/'.$this->id.$resolution.'.mp4';
    }

    public function getWebmPath($resolution = "")
    {
        return $this->getUploadDirectory().$this->contributor->getId().'/'.$this->id.'/webm/'.$this->id.$resolution.'.webm';
    }
    /*------------------------------------------------------------*/
    /*To access path in the transcoder in an absolute way*/
    public function getAbsolutePath()
    {
        return $this->getUploadRootDirectory().$this->contributor->getId().'/'.$this->id.'/original/'.$this->id.'.'.$this->extension;
    }
    public function getThumbnailAsset()
    {
        return $this->getUploadDirectory().$this->contributor->getId().'/'.$this->id.'/'.$this->id.'.png';
    }
    public function getThumbnail()
    {
        return $this->getUploadRootDirectory().$this->contributor->getId().'/'.$this->id.'/'.$this->id.'.png';
    }
    //true: get the directory name / false: get the video path
    public function getMp4AbsolutePath($file = true)
    {
        if(!$file)
        {
            return $this->getUploadRootDirectory().$this->contributor->getId().'/'.$this->id.'/mp4';
        }
        return $this->getUploadRootDirectory().$this->contributor->getId().'/'.$this->id.'/mp4/'.$this->id.'.mp4';
    }

    //true: get the directory name / false: get the video path
    public function getWebmAbsolutePath($file = true)
    {
        if(!$file)
        {
            return $this->getUploadRootDirectory().$this->contributor->getId().'/'.$this->id.'/webm';
        }
        return $this->getUploadRootDirectory().$this->contributor->getId().'/'.$this->id.'/webm/'.$this->id.'.webm';
    }
    public function getVideoPath()
    {
        return $this->getUploadRootDirectory().$this->contributor->getId().'/'.$this->id;
    }
    /*--------------------------------------------------------------*/

    public function __construct()
    {
        $this->uploadDate = new \Datetime();
    }

    /**
     * Set contributor
     *
     * @param \FIDUSTREAM\VideoBundle\Entity\User $contributor
     *
     * @return Video
     */
    public function setContributor(\FIDUSTREAM\UserBundle\Entity\User $contributor)
    {
        $this->contributor = $contributor;

        return $this;
    }

    /**
     * Get contributor
     *
     * @return \FIDUSTREAM\VideoBundle\Entity\User
     */
    public function getContributor()
    {
        return $this->contributor;
    }

    /**
     * Set validator
     *
     * @param \FIDUSTREAM\VideoBundle\Entity\User $validator
     *
     * @return Video
     */
    public function setValidator(\FIDUSTREAM\UserBundle\Entity\User $validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Get validator
     *
     * @return \FIDUSTREAM\VideoBundle\Entity\User
     */
    public function getValidator()
    {
        return $this->validator;
    }

    public function getFile()
    {
    return $this->file;
    }

    public function setFile(UploadedFile $file )
    {
        $this->file = $file;

        if(null !== $this->extension)
        {
            $this->tempFileName = $this->extension;
            $this->extension=null;
        }
    }


     /**
    * @ORM\PrePersist()
    * @ORM\PreUpdate()
    */
    public function preUpload()
    {
        if(null === $this->file)
        {
            return;
        }

        $this->extension = $this->file->guessExtension();
    }

    /**
    * @ORM\PostPersist()
    * @ORM\PostUpdate()file->guessExtension()
    */
    public function upload()
    {
        if(null === $this->file)
        {
            return;
        }

        if (null !== $this->tempFileName) 
        {
            $oldFile = $this->getUploadRootDirectory().'/'.$this->id.'.'.$this->tempFilename;
            if (file_exists($oldFile)) 
            {
                unlink($oldFile);
            }
        }
        $upPath = $this->getUploadRootDirectory().$this->contributor->getId().'/'.$this->id.'/original/';
        $this->file->move($upPath, $this->id.'.'.$this->extension);
    }
    /**
   * @ORM\PreRemove()
   */
    public function preRemoveUpload()
    {
        $this->tempFilename = $this->getUploadRootDirectory().'/'.$this->id.'.'.$this->extension;
    }

   /**
   * @ORM\PostRemove()
   */
    public function removeUpload()
    {
        if (file_exists($this->tempFilename)) 
        {
            unlink($this->tempFilename);
        }
    }

    public function getUploadDirectory(/*user*/)
    {
        return 'uploads/';
    }

    protected function getUploadRootDirectory()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDirectory();
    }

    /**
     * Set currentPlace
     *
     * @param string $currentPlace
     *
     * @return Video
     */
    public function setCurrentPlace($currentPlace)
    {
        $this->currentPlace = $currentPlace;

        return $this;
    }

    /**
     * Get currentPlace
     *
     * @return string
     */
    public function getCurrentPlace()
    {
        return $this->currentPlace;
    }

    /**
     * Set maxQuality
     *
     * @param string $maxQuality
     *
     * @return Video
     */
    public function setMaxQuality($maxQuality)
    {
        $this->maxQuality = $maxQuality;

        return $this;
    }

    /**
     * Get maxQuality
     *
     * @return string
     */
    public function getMaxQuality()
    {
        return $this->maxQuality;
    }

    /**
     * Set extension
     *
     * @param string $extension
     *
     * @return Video
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set lifecycleMessage
     *
     * @param string $lifecycleMessage
     *
     * @return Video
     */
    public function setLifecycleMessage($lifecycleMessage)
    {
        $this->lifecycleMessage = $lifecycleMessage;

        return $this;
    }

    /**
     * Get lifecycleMessage
     *
     * @return string
     */
    public function getLifecycleMessage()
    {
        return $this->lifecycleMessage;
    }

   

    /**
     * Set authenticationLevel
     *
     * @param integer $authenticationLevel
     *
     * @return Video
     */
    public function setAuthenticationLevel($authenticationLevel)
    {
        $this->authenticationLevel = $authenticationLevel;

        return $this;
    }

    /**
     * Get authenticationLevel
     *
     * @return integer
     */
    public function getAuthenticationLevel()
    {
        return $this->authenticationLevel;
    }

    /**
     * Set publicationDate
     *
     * @param \DateTime $publicationDate
     *
     * @return Video
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * Get publicationDate
     *
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }
}
