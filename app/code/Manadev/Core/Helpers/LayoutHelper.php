<?php

namespace Manadev\Core\Helpers;

class LayoutHelper
{
    /**
     * @var callable[]
     */
    protected $afterLayoutLoadedCallbacks = [];

    public function afterLayoutIsLoaded(callable $callback) {
        $this->afterLayoutLoadedCallbacks[] = $callback;
    }

    public function executeAfterLayoutLoadedCallbacks() {
        foreach ($this->afterLayoutLoadedCallbacks as $callback) {
            $callback();
        }

        $this->afterLayoutLoadedCallbacks = [];
    }
}