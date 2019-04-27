<?php

namespace App\ReadModel\User\Filter;

use App\Model\User\Entity\User\Role;
use App\Model\User\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['required' => false, 'attr' => ['placeholder' => 'Name']])
            ->add('email', Type\TextType::class, ['required' => false, 'attr' => ['placeholder' => 'Email']])
            ->add('status', Type\ChoiceType::class, ['choices' => [
                'Wait' => User::STATUS_WAIT,
                'Active' => User::STATUS_ACTIVE,
                'Blocked' => User::STATUS_BLOCKED,
            ], 'required' => false, 'placeholder' => 'All statuses'])
            ->add('role', Type\ChoiceType::class, ['choices' => [
                'User' => Role::USER,
                'Admin' => Role::ADMIN,
            ], 'required' => false, 'placeholder' => 'All roles']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
