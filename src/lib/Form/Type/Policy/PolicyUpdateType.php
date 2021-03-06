<?php

declare(strict_types=1);

namespace EzPlatformAdminUi\Form\Type\Policy;

use eZ\Publish\API\Repository\RoleService;
use EzPlatformAdminUi\Form\Data\Policy\PolicyUpdateData;
use EzSystems\RepositoryForms\Form\Type\Role\LimitationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PolicyUpdateType extends AbstractType
{
    /** @var RoleService */
    private $roleService;

    /**
     * PolicyLimitationsType constructor.
     *
     * @param RoleService $roleService
     */
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'policy',
                PolicyChoiceType::class, [
                    'label' => /** @Desc("Type") */ 'role.policy.type',
                    'placeholder' => 'role.policy.type.choose',
                    'disabled' => true,
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                ['label' => /** @Desc("Create") */ 'policy_create.save']
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data instanceof PolicyUpdateData) {
                $availableLimitationTypes = $this->roleService->getLimitationTypesByModuleFunction(
                    $data->getModule(),
                    $data->getFunction()
                );

                $form->add('limitations', CollectionType::class, [
                    'label' => 'role.policy.available_limitations',
                    'translation_domain' => 'ezrepoforms_role',
                    'entry_type' => LimitationType::class,
                    'data' => $this->generateLimitationList(
                        $data->getLimitations(),
                        $availableLimitationTypes
                    )
                ]);
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ezrepoforms_role',
            'data_class' => PolicyUpdateData::class
        ]);
    }

    /**
     * Generates the limitation list from existing limitations (already configured for current policy) and
     * available limitation types available for current policy (i.e. current module/function combination).
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $existingLimitations
     * @param \eZ\Publish\SPI\Limitation\Type[] $availableLimitationTypes
     *
     * @return array|\eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    private function generateLimitationList(array $existingLimitations, array $availableLimitationTypes)
    {
        $limitations = [];
        foreach ($existingLimitations as $limitation) {
            $limitations[$limitation->getIdentifier()] = $limitation;
        }

        foreach ($availableLimitationTypes as $identifier => $limitationType) {
            if (isset($limitations[$identifier])) {
                continue;
            }

            $limitations[$identifier] = $limitationType->buildValue([]);
        }

        ksort($limitations);

        return $limitations;
    }
}
