<?php
declare(strict_types=1);
namespace App\Http\Helpers\Finance;

class TDClient {

    /** @var  TDContext */
    protected $_context;

    /**
     * TDClient constructor.
     * @param TDContext $context
     */
    public function __construct(TDContext $context) {
        $this->_context = $context;
    }

    /**
     * @param string $name
     * 
     * @return Api\TDApi
     * @throws InvalidArgumentException
     */
    public function api(string $name): Api\TDApi
    {
        $name = $this->getApiName($name);

        switch ($name) {
            case 'references':
                $api = new Api\References($this->_context);
                break;
            case 'timeseries':
                $api = new Api\TimeSeries($this->_context);
                break;
            case 'indicators':
                $api = new Api\Indicators($this->_context);
                break;
            case 'general':
                $api = new Api\General($this->_context);

            default:
                throw new Exception\InvalidArgumentException(
                    sprintf('Undefined api instance called: "%s"', $name)
                );
        }

        return $api;
    }

    /**
     * @param string $name
     * @param array $arguments
     * 
     * @return Api\BaseApi
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments): Api\TDApi
    {
        try {
            return $this->api($name);
        } catch (Exception\InvalidArgumentException $exception) {
            throw new Exception\BadMethodCallException(
                sprintf('Undefined method called: "%s"', $name)
            );
        }
    }

    /**
     * @param string $name
     * 
     * @return string
     */
    protected function getApiName(string $name): string
    {
        return strtolower(str_replace(['_', ''], '', $name));
    }
}
?>