<?php
declare(strict_types=1);

namespace EzPlatformAdminUi\Form\Type\Role;

use eZ\Publish\API\Repository\RoleService;
use EzPlatformAdminUi\Form\DataTransformer\RoleTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class RoleType extends AbstractType
{
    /** @var RoleService */
    protected $roleService;

    /**
     * @param RoleService $roleService
     */
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new RoleTransformer($this->roleService));
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
