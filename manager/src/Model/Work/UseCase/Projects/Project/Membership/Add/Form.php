<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Project\Membership\Add;

use App\ReadModel\Work\Members\Member\MemberFetcher;
use App\ReadModel\Work\Projects\Project\DepartmentFetcher;
use App\ReadModel\Work\Projects\RoleFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $members;
    private $roles;
    private $departments;

    public function __construct(MemberFetcher $members, RoleFetcher $roles, DepartmentFetcher $departments)
    {
        $this->members = $members;
        $this->roles = $roles;
        $this->departments = $departments;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $members = [];
        foreach ($this->members->activeGroupedList() as $item) {
            $members[$item['group']][$item['name']] = $item['id'];
        }

        $builder
            ->add('member', Type\ChoiceType::class, [
                'choices' => $members,
            ])
            ->add('departments', Type\ChoiceType::class, [
                'choices' => array_flip($this->departments->listOfProject($options['project'])),
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('roles', Type\ChoiceType::class, [
                'choices' => array_flip($this->roles->allList()),
                'expanded' => true,
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Command::class,
        ));
        $resolver->setRequired(['project']);
    }
}
