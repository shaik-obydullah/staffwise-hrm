<?php

namespace App\Form;

use App\Entity\TaxYear;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxYearType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $money = static fn (string $step = '0.01') => ['attr' => ['step' => $step, 'min' => 0]];
        $percent = ['attr' => ['step' => '0.01', 'min' => 0, 'max' => 100]];

        $builder
            ->add('name', TextType::class, ['label' => 'Tax year name', 'help' => 'For example: 2026/27'])
            ->add('effectiveFrom', DateType::class, [
                'label' => 'Effective from',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('effectiveTo', DateType::class, [
                'label' => 'Effective to',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('personalAllowance', NumberType::class, ['label' => 'Personal allowance (£)'] + $money())
            ->add('basicRate', NumberType::class, ['label' => 'Basic rate (%)'] + $percent)
            ->add('basicRateLimit', NumberType::class, ['label' => 'Basic rate limit (£)'] + $money())
            ->add('higherRate', NumberType::class, ['label' => 'Higher rate (%)'] + $percent)
            ->add('higherRateLimit', NumberType::class, ['label' => 'Higher rate limit (£)'] + $money())
            ->add('additionalRate', NumberType::class, ['label' => 'Additional rate (%)'] + $percent)
            ->add('niPrimaryThreshold', NumberType::class, ['label' => 'NI primary threshold (£)'] + $money())
            ->add('niUpperEarningsLimit', NumberType::class, ['label' => 'NI upper earnings limit (£)'] + $money())
            ->add('niEmployeeRate', NumberType::class, ['label' => 'Employee NI rate (%)'] + $percent)
            ->add('niEmployeeUpperRate', NumberType::class, ['label' => 'Employee NI rate above upper limit (%)'] + $percent)
            ->add('niEmployerRate', NumberType::class, ['label' => 'Employer NI rate (%)'] + $percent)
            ->add('employerNiThreshold', NumberType::class, ['label' => 'Employer NI threshold (£)'] + $money())
            ->add('pensionLower', NumberType::class, ['label' => 'Pension qualifying earnings lower (£)'] + $money())
            ->add('pensionUpper', NumberType::class, ['label' => 'Pension qualifying earnings upper (£)'] + $money())
            ->add('pensionEmployeeRate', NumberType::class, ['label' => 'Default employee pension rate (%)'] + $percent)
            ->add('pensionEmployerRate', NumberType::class, ['label' => 'Default employer pension rate (%)'] + $percent)
            ->add('studentLoanPlan1Threshold', NumberType::class, ['label' => 'Student loan Plan 1 threshold (£)'] + $money())
            ->add('studentLoanPlan2Threshold', NumberType::class, ['label' => 'Student loan Plan 2 threshold (£)'] + $money())
            ->add('studentLoanPlan4Threshold', NumberType::class, ['label' => 'Student loan Plan 4 threshold (£)'] + $money())
            ->add('studentLoanPlan5Threshold', NumberType::class, ['label' => 'Student loan Plan 5 threshold (£)'] + $money())
            ->add('studentLoanPostgradThreshold', NumberType::class, ['label' => 'Postgraduate loan threshold (£)'] + $money())
            ->add('studentLoanRate', NumberType::class, ['label' => 'Student loan rate (%)'] + $percent)
            ->add('studentLoanPostgradRate', NumberType::class, ['label' => 'Postgraduate loan rate (%)'] + $percent)
            ->add('holidayAccrualRate', NumberType::class, ['label' => 'Holiday accrual rate (% of hours worked)'] + $percent)
            ->add('employmentAllowance', NumberType::class, ['label' => 'Employment Allowance (£)'] + $money())
            ->add('active', CheckboxType::class, ['label' => 'Set as the active tax year', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaxYear::class,
        ]);
    }
}
