<?php
declare(strict_types=1);
namespace App\Http\Helpers\Finance;

/**
 * Class TDContext
 * @package TwelveData
 */
class TDContext {

    /** @var string */
    protected $_apiKey;

    /** @var string */
    protected $_apiUrl;

    /**
     * TDContext constructor.
     * @param TDContext $context
     */
    public function __construct(string $apiKey = '', string $apiUrl = 'https://api.twelvedata.com') {
        $this->_apiKey = $apiKey;
        $this->_apiUrl = $apiUrl;
    }

    /**
     * @return string
     */
    public function getApiKey() : string {
        return $this->_apiKey;
    }

    /**
     * @param string $apiKey
     * 
     * @return self
     */
    public function setApiKey(string $apiKey) {
        $this->_apiKey = $apiKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiUrl() : string {
        return $this->_apiUrl;
    }

    /**
     * @param string $apiUrl
     * 
     * @return self
     */
    public function setApiUrl(string $apiUrl) {
        $this->_apiUrl = $apiKey;
        return $this;
    }
}
?>