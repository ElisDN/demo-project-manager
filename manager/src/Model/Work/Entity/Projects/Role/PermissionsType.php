<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Role;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

class PermissionsType extends JsonType
{
    public const NAME = 'work_projects_role_permissions';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof ArrayCollection) {
            $data = array_map([self::class, 'deserialize'], $value->toArray());
        } else {
            $data = $value;
        }

        return parent::convertToDatabaseValue($data, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!is_array($data = parent::convertToPHPValue($value, $platform))) {
            return $data;
        }

        return new ArrayCollection(array_filter(array_map([self::class, 'serialize'], $data)));
    }

    public function getName(): string
    {
        return self::NAME;
    }

    private static function deserialize(Permission $permission): string
    {
        return $permission->getName();
    }

    private static function serialize(string $name): ?Permission
    {
        return in_array($name, Permission::names(), true) ? new Permission($name) : null;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform) : bool
    {
        return true;
    }
}
