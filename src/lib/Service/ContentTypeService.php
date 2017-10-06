<?php

declare(strict_types=1);

namespace EzPlatformAdminUi\Service;

use eZ\Publish\API\Repository as Repository;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use Symfony\Component\Form\FormFactoryInterface;

class ContentTypeService
{
    /** @var Repository\ContentTypeService */
    private $contentTypeService;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var array */
    private $prioritizedLanguages;

    /**
     * ContentTypeGroupService constructor.
     *
     * @param Repository\ContentTypeService $contentTypeService
     * @param FormFactoryInterface $formFactory
     * @param array $prioritizedLanguages
     */
    public function __construct(
        Repository\ContentTypeService $contentTypeService,
        FormFactoryInterface $formFactory,
        array $prioritizedLanguages)
    {
        $this->contentTypeService = $contentTypeService;
        $this->formFactory = $formFactory;
        $this->prioritizedLanguages = $prioritizedLanguages;
    }

    public function getContentType(int $id): ContentType
    {
        try {
            return $this->contentTypeService->loadContentTypeDraft($id);
        } catch (NotFoundException $ex) {
            return $this->contentTypeService->loadContentType($id, $this->prioritizedLanguages);
        }
    }

    public function getContentTypeDraft(int $id): ContentTypeDraft
    {
        try {
            return $this->contentTypeService->loadContentTypeDraft($id);
        } catch (NotFoundException $ex) {
            return $this->contentTypeService->createContentTypeDraft(
                $this->contentTypeService->loadContentType($id, $this->prioritizedLanguages)
            );
        }
    }

    public function getContentTypes(ContentTypeGroup $group): array
    {
        return $this->contentTypeService->loadContentTypes($group, $this->prioritizedLanguages);
    }

    public function createContentType(ContentTypeGroup $group): ContentType
    {
        $identifier = '__new__' . md5((string)microtime(true));
        $languageCode = $this->prioritizedLanguages[0];

        $createStruct = $this->contentTypeService->newContentTypeCreateStruct($identifier);
        $createStruct->mainLanguageCode = $languageCode;
        $createStruct->names = [
            $languageCode => 'New ContentType'
        ];

        return $this->contentTypeService->createContentType($createStruct, [$group]);
    }

    public function deleteContentType(ContentType $contentType)
    {
        $this->contentTypeService->deleteContentType($contentType);
    }

    /**
     * Return the highest prioritized language that $contentType is translated to.
     * If there is no translation for a prioritized language, return $contentType's main language.
     *
     * @param ContentType $contentType Content type (or content type draft)
     * @return string Language code
     */
    public function getPrioritizedLanguage(ContentType $contentType)
    {
        foreach ($this->prioritizedLanguages as $prioritizedLanguage) {
            if (isset($contentType->names[$prioritizedLanguage])) {
                return $prioritizedLanguage;
            }
        }

        return $contentType->mainLanguageCode;
    }
}
