<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Registries;

use Manadev\Core\Contracts\PostInstallScript;
use Manadev\Core\Exceptions\InterfaceNotImplemented;

class PostInstallScripts
{

    /**
     * @var array
     */
    protected $postInstallScripts = [];

    public function __construct(array $postInstallScripts)
    {
        foreach ($postInstallScripts as $module => $postInstallScript) {
            if (!($postInstallScript instanceof PostInstallScript)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($postInstallScript), PostInstallScript::class));
            }

            $this->postInstallScripts[$module] = $postInstallScript;
        }
    }

    public function getList() {
        return $this->postInstallScripts;
    }
}