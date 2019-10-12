<?php

namespace FIDUSTREAM\VideoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class VideoType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('file' ,          FileType::class)
                ->add('title',          TextType::class)
                ->add('description',    TextAreaType::class)
                ->add('tags',           TextAreaType::class)
                
                ->add('authenticationLevel', IntegerType::class, array(
                'attr' => array(
                    'min' => 1,
                    'max' => 15,
                    'step' => 1,
                    'value' => 15,
    ),
                ))
                ->add('upload',         SubmitType::class )
                ;
    }
    
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FIDUSTREAM\VideoBundle\Entity\Video'
        ));
    }

    
    public function getBlockPrefix()
    {
        return 'fidustream_videobundle_video';
    }


}
