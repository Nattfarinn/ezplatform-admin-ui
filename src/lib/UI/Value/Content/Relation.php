<?php

declare(strict_types=1);

namespace EzPlatformAdminUi\UI\Value\Content;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\Relation as CoreRelation;
use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;

/**
 * Extends original value object in order to provide additional fields.
 */
class Relation extends CoreRelation
{
    /**
     * Field definition name for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     *
     * @var string
     */
    protected $relationFieldDefinitionName;

    /**
     * The content type name for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     *
     * @var string
     */
    protected $relationContentTypeName;

    /**
     * Main location for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     *
     * @var Location
     */
    protected $relationLocation;

    /**
     * The name for the relation.
     * This will either come from destinationContentInfo OR sourceContentInfo depending upon if reverse relation or normal relation.
     *
     * @var string
     */
    protected $relationName;

    /**
     * @param APIRelation $relation
     * @param array $properties
     */
    public function __construct(APIRelation $relation, array $properties = [])
    {
        parent::__construct(get_object_vars($relation) + $properties);
    }
}
