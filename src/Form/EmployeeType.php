<?php

namespace App\Form;

use App\Entity\Employee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'First name'])
            ->add('lastName', TextType::class, ['label' => 'Last name'])
            ->add('email', EmailType::class, ['label' => 'Email address', 'required' => false])
            ->add('phone', TextType::class, ['label' => 'Phone', 'required' => false])
            ->add('address', TextareaType::class, ['label' => 'Home address', 'required' => false, 'attr' => ['rows' => 2]])
            ->add('niNumber', TextType::class, ['label' => 'National Insurance number', 'required' => false, 'attr' => ['placeholder' => 'QQ 12 34 56 C']])
            ->add('jobTitle', TextType::class, ['label' => 'Job title', 'required' => false])
            ->add('department', TextType::class, ['label' => 'Department', 'required' => false])
            ->add('employmentStartDate', DateType::class, [
                'label' => 'Employment start date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('employmentEndDate', DateType::class, [
                'label' => 'Employment end date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => ['Active' => Employee::STATUS_ACTIVE, 'Inactive' => Employee::STATUS_INACTIVE],
            ])
            ->add('hourlyRate', NumberType::class, ['label' => 'Hourly pay rate (£)', 'attr' => ['step' => '0.01', 'min' => 0]])
            ->add('taxCode', TextType::class, ['label' => 'Tax code'])
            ->add('niCategory', ChoiceType::class, [
                'label' => 'NI category',
                'choices' => array_combine(['A', 'B', 'C', 'H', 'J', 'M', 'Z'], ['A', 'B', 'C', 'H', 'J', 'M', 'Z']),
            ])
            ->add('pensionOptIn', CheckboxType::class, [
                'label' => 'Enrolled in workplace pension',
                'required' => false,
            ])
            ->add('pensionEmployeeRate', NumberType::class, [
                'label' => 'Employee pension rate (%)',
                'required' => false,
                'attr' => ['step' => '0.01', 'min' => 0, 'placeholder' => 'Use tax year default'],
            ])
            ->add('pensionEmployerRate', NumberType::class, [
                'label' => 'Employer pension rate (%)',
                'required' => false,
                'attr' => ['step' => '0.01', 'min' => 0, 'placeholder' => 'Use tax year default'],
            ])
            ->add('studentLoanPlan', ChoiceType::class, [
                'label' => 'Student loan plan',
                'choices' => [
                    'No student loan' => Employee::LOAN_NONE,
                    'Plan 1' => Employee::LOAN_PLAN_1,
                    'Plan 2' => Employee::LOAN_PLAN_2,
                    'Plan 4 (Scotland)' => Employee::LOAN_PLAN_4,
                    'Plan 5' => Employee::LOAN_PLAN_5,
                    'Postgraduate loan' => Employee::LOAN_POSTGRAD,
                ],
            ])
            ->add('payFrequency', ChoiceType::class, [
                'label' => 'Pay frequency',
                'choices' => ['Weekly' => Employee::FREQUENCY_WEEKLY, 'Monthly' => Employee::FREQUENCY_MONTHLY],
            ])
            ->add('contractedWeeklyHours', NumberType::class, [
                'label' => 'Contracted hours per week',
                'attr' => ['step' => '0.01', 'min' => 0],
            ])
            ->add('holidayEntitlementWeeks', NumberType::class, [
                'label' => 'Holiday entitlement (weeks)',
                'attr' => ['step' => '0.01', 'min' => 0],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }
}
