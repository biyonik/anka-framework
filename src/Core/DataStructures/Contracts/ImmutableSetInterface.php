<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures\Contracts;

/**
 * Değiştirilemez Set arayüzü.
 *
 * Değiştirilemez eşsiz değerleri tutan bir veri yapısını tanımlar.
 *
 * @package Framework\Core\DataStructures
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends SetInterface<T>
 * @extends ImmutableCollectionInterface<T>
 */
interface ImmutableSetInterface extends SetInterface, ImmutableCollectionInterface
{
}