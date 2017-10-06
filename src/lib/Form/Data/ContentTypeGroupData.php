<?php

declare(strict_types=1);

namespace EzPlatformAdminUi\Form\Data;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

class ContentTypeGroupData
{
    /** @var string */
    private $identifier;

    /**
     * ContentTypeGroupData constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier = null)
    {
        $this->identifier = $identifier;
    }


    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function factory(ContentTypeGroup $group): ContentTypeGroupData
    {
        $data = new self();
        $data->identifier = $group->identifier;

        return $data;
    }
}
