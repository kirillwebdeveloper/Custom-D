<?php

namespace App\Form\Company;

use App\Entity\Company\Company;
use App\Form\AddressType;
use App\Form\Bank\BankAccountIbanBicType;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CompanyType.
 */
class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'company.name',
                    'attr'  => [
                        'placeholder' => 'company.name',
                    ],
                ]
            )
            ->add(
                'email',
                TextType::class,
                [
                    'label'    => 'company.email',
                    'required' => false,
                    'attr'     => [
                        'placeholder' => 'company.email',
                    ],
                ]
            );

        if ($options['add_phone']) {
            $builder
                ->add(
                    'phone',
                    PhoneNumberType::class,
                    [
                        'format'          => PhoneNumberFormat::NATIONAL,
                        'default_region'  => 'FR',
                        'label'           => 'company.phone',
                        'required'        => false,
                        'attr'            => [
                            'placeholder' => 'company.phone',
                        ],
                        'constraints'     => [
                            new AssertPhoneNumber(),
                        ],
                    ]
                );
        }

        $builder->add(
            'address',
            AddressType::class
        );

        if ($options['add_bank_account']) {
            $builder
                ->add(
                    'bankAccount',
                    BankAccountIbanBicType::class,
                    [
                        'label' => false,
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'       => Company::class,
                'label'            => false,
                'add_bank_account' => false,
                'add_phone'        => false,
            ]
        );
    }
}
