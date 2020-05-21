<?php
declare(strict_types=1);
namespace App\Http\Helpers\Finance\Api;

use App\Http\Helpers\Finance\TDContext;
use GuzzleHttp\Client;

use function GuzzleHttp\json_decode;
use function http_build_query;
use function array_merge;

/**
 * Class TDApi
 * @package TwelveData\Api
 */
class TDApi {

    public const FORMAT_JSON = 'JSON';
    public const FORMAT_CSV = 'CSV';

    public const INTERVAL_1m = '1min';
    public const INTERVAL_5m = '5min';
    public const INTERVAL_15m = '15min';
    public const INTERVAL_30m = '30min';
    public const INTERVAL_45m = '45min';
    public const INTERVAL_1h = '1h';
    public const INTERVAL_2h = '2h';
    public const INTERVAL_4h = '4h';
    public const INTERVAL_1d = '1day';
    public const INTERVAL_1w = '1week';
    public const INTERVAL_1mo = '1month';
    
    public const TYPE_stock = 'Stock';
    public const TYPE_index = 'Index';
    public const TYPE_etf = 'ETF';
    public const TYPE_reit = 'REIT';

    /** @var  TDontext */
    protected $_context;

    /** @var  Client */
    protected $_client;

    /**
     * TDApi constructor.
     * @param TDContext $context
     */
    public function __construct(TDContext $context)
    {
        $this->_context = $context;
        $this->_client = new Client();
    }

    /**
     * @param array $params
     * 
     * @return array
     */
    protected function get(string $functionName, array $params = [])
    {
        unset($params['apikey']);

        $httpQuery = http_build_query(
            array_merge(
                $params,
                [
                    'apikey' => $this->_context->getApiKey(),
                ]
            )
        );

        $response = $this->_client->get($this->getApiUri($functionName) . $httpQuery);

        $result = json_decode($response->getBody()->getContents(), true);
        return $result;
    }

    /**
     * @param string $functionName
     * 
     * @return string
     */
    protected function getApiUri(string $functionName): string
    {
        return $this->_context->getApiUrl() . '/' . $functionName . '?';
    }
}
?>