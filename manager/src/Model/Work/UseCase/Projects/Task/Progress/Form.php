<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Task\Progress;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('progress', Type\ChoiceType::class, ['choices' => [
                0 => 0,
                25 => 25,
                50 => 50,
                75 => 75,
                100 => 100
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
        return 'progress';
    }
}
