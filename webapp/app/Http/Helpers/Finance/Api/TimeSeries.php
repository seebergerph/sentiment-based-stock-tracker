<?php
declare(strict_types=1);
namespace App\Http\Helpers\Finance\Api;

use function array_merge;

/**
 * Class TimeSeries
 * @package TwelveData\Api
 */
class TimeSeries extends TDApi {

    /**
     * @param string $symbol
     * @param string $interval
     * @param array $params
     *
     * @return array
     */
    public function series(
        string $symbol,
        string $interval = self::INTERVAL_1h,
        array $params = []
    ) {

        $requiredParams = [
            'symbol' => $symbol,
            'interval' => $interval
        ];

        return $this->get(
            'time_series', 
            array_merge(
                $requiredParams,
                $params
            ));
    }

    /**
     * @param string $symbol
     * @param array $params
     *
     * @return array
     */
    public function quote(
        string $symbol,
        array $params = []
    ) {

        $requiredParams = [
            'symbol' => $symbol
        ];

        return $this->get(
            'quote', 
            array_merge(
                $requiredParams,
                $params
            ));
    }

    /**
     * @param string $symbol
     * @param array $params
     *
     * @return array
     */
    public function realTimePrice(
        string $symbol,
        array $params = []
    ) {

        $requiredParams = [
            'symbol' => $symbol
        ];

        return $this->get(
            'price', 
            array_merge(
                $requiredParams,
                $params
            ));
    }
}
?>