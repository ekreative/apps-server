<?php

namespace App\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('app', FileType::class)
            ->add('comment', TextType::class, [
                'required' => false
            ])
            ->add('ref', TextType::class, [
                'required' => false
            ])
            ->add('commit', TextType::class, [
                'required' => false
            ])
            ->add('jobName', TextType::class, [
                'required' => false
            ])
            ->add('ci', TextType::class, [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApiForm::class
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
