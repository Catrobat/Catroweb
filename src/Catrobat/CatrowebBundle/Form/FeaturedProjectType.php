<?php

namespace Catrobat\CatrowebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FeaturedProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image')
            ->add('project')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Catrobat\CatrowebBundle\Entity\FeaturedProject'
        ));
    }

    public function getName()
    {
        return 'catrobat_catrowebbundle_featuredprojecttype';
    }
}
