<?php

namespace Ekreative\AppsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $required = true;
        if ($options['data']->getId()) {
            $required = false;
            $builder->add('username', null, ['read_only' => true]);
        } else {
            $builder->add('username');
        }

        $builder
            ->add('name');
        if ($options['data']->getId()) {
            $required = false;
            $builder->add('username', null, ['read_only' => true]);
            $builder ->add('password',null,['required'=>false]);
        } else {
            $builder->add('username');
            $builder ->add('password');
        }

       //     ->add('email',null,['required'=>false])



        $builder->add('androidApps',null,['required'=>false])
            ->add('iosApps',null,['required'=>false])
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ekreative\AppsBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ekreative_appsbundle_user';
    }
}
