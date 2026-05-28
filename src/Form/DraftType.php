<?php

namespace App\Form;

use App\Entity\Draft;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

final class DraftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'required' => true,
            'label' => 'Name',
            'constraints' => [
                new Length(
                    max: 32,
                ),
            ]
        ]);

        $builder->add('blueTeamName', TextType::class, [
            'required' => true,
            'label' => 'Blue team name',
            'constraints' => [
                new Length(
                    max: 32,
                ),
            ]
        ]);

        $builder->add('redTeamName', TextType::class, [
            'required' => true,
            'label' => 'Red team name',
            'constraints' => [
                new Length(
                    max: 32,
                ),
            ]
        ]);

        $builder->add('maxTimer', IntegerType::class, [
            'required' => true,
            'label' => 'Max timer',
            'data' => 60,
        ]);

        $builder->add('isSandbox', CheckboxType::class, [
            'required' => false,
            'label' => 'Sandbox mode ?',
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Create lobby',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Draft::class,
        ]);
    }
}
