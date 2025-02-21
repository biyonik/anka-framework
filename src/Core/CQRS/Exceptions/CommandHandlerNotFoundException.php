<?php

declare(strict_types=1);

namespace Framework\Core\CQRS\Exceptions;

use RuntimeException;

/**
 * Command Handler bulunamadığında fırlatılan istisna.
 *
 * @package Framework\Core\CQRS
 * @subpackage Exceptions
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class CommandHandlerNotFoundException extends RuntimeException
{
    // İstisna için özel metodlar buraya eklenebilir
}