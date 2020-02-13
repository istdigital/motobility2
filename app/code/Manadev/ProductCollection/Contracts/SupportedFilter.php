<?php

namespace Manadev\ProductCollection\Contracts;

interface SupportedFilter
{
    public function supports(Filter $filter);
}