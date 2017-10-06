<?php

declare(strict_types=1);

namespace EzPlatformAdminUiBundle\ParamConverter;

use eZ\Publish\API\Repository\Values\User\Policy;
use EzPlatformAdminUi\Service\Role\RoleService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PolicyParamConverter implements ParamConverterInterface
{
    const PARAMETER_ROLE_ID = 'roleId';
    const PARAMETER_POLICY_ID = 'policyId';

    /**
     * @var RoleService
     */
    private $roleService;

    /**
     * RoleParamConverter constructor.
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
    public function apply(Request $request, ParamConverter $configuration)
    {
        $roleId = (int)$request->get(self::PARAMETER_ROLE_ID);

        $role = $this->roleService->getRole($roleId);
        if (!$role) {
            throw new NotFoundHttpException("Role $roleId not found!");
        }

        $policyId = (int)$request->get(self::PARAMETER_POLICY_ID);

        $policy = $this->roleService->getPolicy($role, $policyId);
        if (!$policy) {
            throw new NotFoundHttpException("Policy $policyId not found!");
        }

        $request->attributes->set($configuration->getName(), $policy);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function supports(ParamConverter $configuration)
    {
        return Policy::class === $configuration->getClass();
    }
}
