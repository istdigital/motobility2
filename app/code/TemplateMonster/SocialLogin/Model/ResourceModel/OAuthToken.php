<?php

namespace TemplateMonster\SocialLogin\Model\ResourceModel;

use TemplateMonster\SocialLogin\Model\OAuthToken as Token;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * OAuthToken model.
 */
class OAuthToken extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('social_login_provider', 'id');
    }

    /**
     * Get OAuth token by provider.
     *
     * @param Token  $token
     * @param string $code
     * @param string $id
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByProvider(Token $token, $code, $id)
    {
        $select = $this
            ->getConnection()
            ->select()
            ->from($this->getMainTable())
            ->where('provider_code = ?', $code)
            ->where('provider_id = ?', $id)
            ->limit(1);
        $data = $this->getConnection()->fetchRow($select);

        if (false !== $data) {
            $token->setData($data);
        }

        return $this;
    }
}
