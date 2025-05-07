<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('slug', TextType::class)
            ->add('kind', TextType::class)
            ->add('surname', TextType::class)
            ->add('knowledge', TextType::class)
            ->add('intelligence', TextType::class)
            ->add('caste', TextType::class)
            ->add('strength', IntegerType::class)
            ->add('image', TextType::class)
            ->add('identifier', TextType::class)
            ->add('creation',DateTimeType::class, [
            'widget' => 'single_text',
            ])
            ->add('updated_at', DateTimeType::class, [
            'widget' => 'single_text',
            ])
            ->add("user")
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
