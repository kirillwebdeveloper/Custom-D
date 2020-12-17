<?php

namespace App\Form\Country;

use App\Entity\Country\Country;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CountryType.
 */
class CountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'country.name',
                ]
            )
            ->add(
                'isoCode',
                TextType::class,
                [
                    'label' => 'country.iso',
                ]
            )
            ->add(
                'vatRate',
                NumberType::class,
                [
                    'label' => 'country.vatRate',
                    'scale' => 2,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Country::class,
            ]
        );
    }
}
