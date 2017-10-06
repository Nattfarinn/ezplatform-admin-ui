<?php

declare(strict_types=1);

namespace EzPlatformAdminUiBundle\Controller;

use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\RoleAssignment;
use EzPlatformAdminUi\Form\Data\Role\RoleAssignmentCreateData;
use EzPlatformAdminUi\Form\Data\Role\RoleAssignmentDeleteData;
use EzPlatformAdminUi\Form\Type\Role\RoleAssignmentCreateType;
use EzPlatformAdminUi\Form\Type\Role\RoleAssignmentDeleteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleAssignmentController extends Controller
{
    /** @var RoleService */
    private $roleService;

    /** @var SearchService */
    private $searchService;

    /**
     * PolicyController constructor.
     *
     * @param RoleService $roleService
     */
    public function __construct(RoleService $roleService, SearchService $searchService)
    {
        $this->roleService = $roleService;
        $this->searchService = $searchService;
    }

    public function listAction(Role $role): Response
    {
        $assignments = $this->roleService->getRoleAssignments($role);

        $deleteFormsByAssignmentId = [];

        foreach ($assignments as $assignment) {
            $deleteFormsByAssignmentId[$assignment->id] = $this->createForm(
                RoleAssignmentDeleteType::class,
                new RoleAssignmentDeleteData($assignment)
            )->createView();
        }

        return $this->render('@EzPlatformAdminUi/admin/role_assignment/list.html.twig', [
            'role' => $role,
            'deleteFormsByAssignmentId' => $deleteFormsByAssignmentId,
            'assignments' => $assignments,
        ]);
    }

    public function createAction(Request $request, Role $role): Response
    {
        $form = $this->createForm(RoleAssignmentCreateType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RoleAssignmentCreateData $data */
            $data = $form->getData();

            $users = $data->getUsers();
            $groups = $data->getGroups();
            $sections = $data->getSections();
            $locations = $data->getLocations();

            $limitations = [];

            if (empty($sections) && empty($locations)) {
                $limitations[] = null;
            }
            else {
                if (!empty($sections)) {
                    $limitation = new SectionLimitation();

                    foreach ($sections as $section) {
                        $limitation->limitationValues[] = $section->id;
                    }

                    $limitations[] = $limitation;
                }

                if (!empty($locations)) {
                    $limitation = new SubtreeLimitation();

                    foreach ($locations as $location) {
                        $limitation->limitationValues[] = $location->pathString;
                    }

                    $limitations[] = $limitation;
                }
            }

            foreach ($limitations as $limitation) {
                if (!empty($users)) {
                    foreach ($users as $user) {
                        $this->roleService->assignRoleToUser($role, $user, $limitation);
                    }
                }

                if ($groups !== null) {
                    foreach ($groups as $group) {
                        $this->roleService->assignRoleToUserGroup($role, $group, $limitation);
                    }
                }
            }

            $this->addFlash('success', 'role_assignment.created');

            return $this->redirect($this->generateUrl('ezplatform.role.view', ['roleId' => $role->id]));
        }

        foreach ($form->getErrors(true, true) as $formError) {
            $this->addFlash('danger', $formError->getMessage());
        }

        return $this->render('@EzPlatformAdminUi/admin/role_assignment/add.html.twig', [
            'role' => $role,
            'form' => $form->createView()
        ]);
    }

    public function deleteAction(Request $request, Role $role, RoleAssignment $roleAssignment): Response
    {
        $form = $this->createForm(RoleAssignmentDeleteType::class, new RoleAssignmentDeleteData($roleAssignment));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->roleService->removeRoleAssignment($roleAssignment);

            $this->addflash('success','role_assignment.deleted');
        }

        foreach ($form->getErrors(true, true) as $formError) {
            $this->addFlash('danger', $formError->getMessage());
        }

        return $this->redirect($this->generateUrl('ezplatform.role.view', ['roleId' => $role->id]));
    }
}
