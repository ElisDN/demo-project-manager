<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Task;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class TypeType extends StringType
{
    public const NAME = 'work_projects_task_type';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof Type ? $value->getName() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return !empty($value) ? new Type($value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform) : bool
    {
        return true;
    }
}