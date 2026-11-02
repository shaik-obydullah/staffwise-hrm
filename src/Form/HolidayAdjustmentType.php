<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HolidayAdjustmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hours', NumberType::class, [
                'label' => 'Hours adjustment',
                'attr' => ['step' => '0.01'],
                'help' => 'Use a positive number to add hours or a negative number to remove them.',
            ])
            ->add('description', TextType::class, [
                'label' => 'Reason',
                'attr' => ['placeholder' => 'e.g. Manual correction agreed with employee'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
