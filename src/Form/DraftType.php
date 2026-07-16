<?php

namespace App\Form;

use App\Entity\Champion;
use App\Entity\Draft;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use function Symfony\Component\Translation\t;

final class DraftType extends AbstractType
{
    private const array DRAFT_NAME_PLACEHOLDERS = [
        'Worlds 2026 finals',
        'Mid-Season Invitationals 2026 semi-finals #1',
    ];

    private const array DRAFT_TEAM_NAME_PLACEHOLDERS = [
        'G2 Esports',
        'KT Rolster',
        'Team Secret Wales',
        'Team Solo Mid',
        'Dignitas',
        'Fnatic',
        'Karmine Corp',
        'FanPlus Phoenix'
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'required' => true,
            'label' => t('draft.create.form.name'),
            'constraints' => [
                new Length(max: 32),
            ],
            'attr' => [
                'placeholder' => self::DRAFT_NAME_PLACEHOLDERS[\random_int(0, \count(self::DRAFT_NAME_PLACEHOLDERS) - 1)],
                'autofocus' => true,
            ],
        ]);

        $builder->add('blueTeamName', TextType::class, [
            'required' => true,
            'label' => t('draft.create.form.blue_team_name'),
            'constraints' => [
                new Length(max: 32),
            ],
            'attr' => [
                'placeholder' => self::DRAFT_TEAM_NAME_PLACEHOLDERS[\random_int(0, \count(self::DRAFT_TEAM_NAME_PLACEHOLDERS) - 1)],
            ],
        ]);

        $builder->add('redTeamName', TextType::class, [
            'required' => true,
            'label' => t('draft.create.form.red_team_name'),
            'constraints' => [
                new Length(max: 32),
            ],
            'attr' => [
                'placeholder' => self::DRAFT_TEAM_NAME_PLACEHOLDERS[\random_int(0, \count(self::DRAFT_TEAM_NAME_PLACEHOLDERS) - 1)],
            ],
        ]);

        $builder->add('maxTimer', IntegerType::class, [
            'required' => true,
            'label' => t('draft.create.form.max_timer'),
            'data' => 60,
        ]);

        $builder->add('disableTimer', CheckboxType::class, [
            'label' => t('draft.create.form.disable_timer'),
            'mapped' => false,
            'required' => false,
        ]);

        $builder->add('bannedLolIds', EntityType::class, [
            'required' => false,
            'label' => t('draft.create.form.unavailable_champions'),
            'class' => Champion::class,
            'choice_value' => 'lolKey',
            'choice_label' => 'lolId',
            'multiple' => true,
        ]);

        $builder->add('isSandbox', CheckboxType::class, [
            'required' => false,
            'label' => t('draft.create.form.sandbox'),
            'attr' => [
                'class' => 'toggle-checkbox',
            ],
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => t('draft.create.form.submit'),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Draft::class,
        ]);
    }
}
