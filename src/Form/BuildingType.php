<?php

namespace App\Form;

use App\Entity\Building;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuildingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('slug', TextType::class)
            ->add('caste', TextType::class)
            ->add('strength', IntegerType::class)
            ->add('image', TextType::class)
            ->add('identifier', TextType::class)
            ->add('creation',DateTimeType::class, [
            'widget' => 'single_text',
            ])
            ->add('modification', DateTimeType::class, [
            'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Building::class,
        ]);
    }
}
