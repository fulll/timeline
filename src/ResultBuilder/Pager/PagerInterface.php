<?php

namespace Spy\Timeline\ResultBuilder\Pager;

interface PagerInterface
{
    public function paginate(mixed $target, int $page = 1, int $limit = 10);

    public function getPage(): int;

    public function getLastPage(): int;

    public function haveToPaginate(): bool;

    public function getNbResults(): int;

    public function setItems(array $items): void;
}
