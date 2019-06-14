<?php

declare(strict_types=1);

namespace App\ReadModel\Work\Projects\Calendar\Query;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $years = range(date('Y') - 5, date('Y') + 5);
        $months = range(1, 12);

        $builder
            ->add('year', Type\ChoiceType::class, [
                'choices' => array_combine($years, $years),
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('month', Type\ChoiceType::class, [
                'choices' => array_combine($months, $months),
                'attr' => ['onchange' => 'this.form.submit()']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Query::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
