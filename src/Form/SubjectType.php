<?php

namespace App\Form;

use App\Entity\Subject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// Classe formulaire pour l'entité Subject, ce formulaire a vocation à hydrater un objet Subject
class SubjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Cette méthode crée notre formualire, c'est ici qu'on ajoute nos différents champs et aussi leurs styles
        // De nous ne sommes pas obligés de spécifier le type car les champs correspondent à une entité
        // Par contre pour submit cela est nécessaire
        $builder
            ->add('title', null, [
                "label" => "Votre titre :",
            ])
            ->add('content', null, [
                "label" => "Posez votre question :"
            ])
            ->add('enregistrer', SubmitType::class, [
                "attr" => ["class" => "bg-danger text-white"],
                'row_attr' => ['class' => 'text-center']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // ici on associe le formulaire à la classe Subject
            'data_class' => Subject::class,
        ]);
    }
}
