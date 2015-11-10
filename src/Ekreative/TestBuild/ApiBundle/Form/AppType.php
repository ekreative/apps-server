<?php
/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 6/27/15
 * Time: 3:10 PM
 */

namespace Ekreative\TestBuild\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AppType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('icon', 'file', ['mapped' => false])
            ->add('build', 'file', ['mapped' => false]);
    }

    public function getName()
    {
        return 'app';
    }
}