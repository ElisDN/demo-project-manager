<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Task\Priority;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('priority', Type\ChoiceType::class, ['choices' => [
                'Low' => 1,
                'Normal' => 2,
                'High' => 3,
                'Extra' => 4
            ], 'attr' => ['onchange' => 'this.form.submit()']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Command::class,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'priority';
    }
}
