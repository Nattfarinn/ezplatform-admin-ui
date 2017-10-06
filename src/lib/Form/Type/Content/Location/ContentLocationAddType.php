<?php
declare(strict_types=1);

namespace EzPlatformAdminUi\Form\Type\Content\Location;


use EzPlatformAdminUi\Form\Data\Content\Location\ContentLocationAddData;
use EzPlatformAdminUi\Form\Type\Content\ContentInfoType;
use EzPlatformAdminUi\Form\Type\Content\LocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentLocationAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'content_info',
                ContentInfoType::class,
                ['label' => false, 'attr' => ['hidden' => true]]
            )
            ->add(
                'new_locations',
                LocationType::class,
                ['multiple' => true, 'label' => false]
            )
            ->add(
                'add',
                SubmitType::class,
                [
                    'attr' => ['hidden' => true],
                    'label' => /** @Desc("Add location") */
                        'content_location_add_type.add',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContentLocationAddData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
