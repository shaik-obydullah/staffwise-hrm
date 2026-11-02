<?php

namespace App\Form;

use App\Entity\Employee;
use App\Entity\PayrollRun;
use App\Entity\TaxYear;
use App\Repository\TaxYearRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PayrollRunType extends AbstractType
{
    public function __construct(private readonly TaxYearRepository $taxYears)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('frequency', ChoiceType::class, [
                'label' => 'Pay frequency',
                'choices' => ['Weekly' => Employee::FREQUENCY_WEEKLY, 'Monthly' => Employee::FREQUENCY_MONTHLY],
            ])
            ->add('taxYear', EntityType::class, [
                'label' => 'Tax year rates',
                'class' => TaxYear::class,
                'choices' => $this->taxYears->findAllOrdered(),
                'choice_label' => static fn (TaxYear $taxYear) => $taxYear->getName().($taxYear->isActive() ? ' (active)' : ''),
            ])
            ->add('periodStart', DateType::class, [
                'label' => 'Period start',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('periodEnd', DateType::class, [
                'label' => 'Period end',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('payDate', DateType::class, [
                'label' => 'Pay date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['rows' => 2],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PayrollRun::class,
        ]);
    }
}
