<?php

namespace App\ReadModel\Work\Projects\Task\Filter;

use App\Model\Work\Entity\Projects\Task\Status;
use App\Model\Work\Entity\Projects\Task\Type as TaskType;
use App\ReadModel\Work\Members\Member\MemberFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $members;

    public function __construct(MemberFetcher $members)
    {
        $this->members = $members;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $members = [];
        foreach ($this->members->activeGroupedList() as $item) {
            $members[$item['group']][$item['name']] = $item['id'];
        }

        $builder
            ->add('text', Type\TextType::class, ['required' => false, 'attr' => [
                'placeholder' => 'Search...',
                'onchange' => 'this.form.submit()',
            ]])
            ->add('type', Type\ChoiceType::class, ['choices' => [
                'None' => TaskType::NONE,
                'Error' => TaskType::ERROR,
                'Feature' => TaskType::FEATURE,
            ], 'required' => false, 'placeholder' => 'All types', 'attr' => ['onchange' => 'this.form.submit()']])
            ->add('status', Type\ChoiceType::class, ['choices' => [
                'New' => Status::NEW,
                'Working' => Status::WORKING,
                'Need Help' => Status::HELP,
                'Checking' => Status::CHECKING,
                'Rejected' => Status::REJECTED,
                'Done' => Status::DONE,
            ], 'required' => false, 'placeholder' => 'All statuses', 'attr' => ['onchange' => 'this.form.submit()']])
            ->add('priority', Type\ChoiceType::class, ['choices' => [
                'Low' => 1,
                'Normal' => 2,
                'High' => 3,
                'Extra' => 4
            ], 'required' => false, 'placeholder' => 'All priorities', 'attr' => ['onchange' => 'this.form.submit()']])
            ->add('author', Type\ChoiceType::class, [
                'choices' => $members,
                'required' => false, 'placeholder' => 'All authors', 'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('executor', Type\ChoiceType::class, [
                'choices' => $members,
                'required' => false, 'placeholder' => 'All executors', 'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('roots', Type\ChoiceType::class, ['choices' => [
                'Roots' => Status::NEW,
            ], 'required' => false, 'placeholder' => 'All levels', 'attr' => ['onchange' => 'this.form.submit()']]);
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
