<?php
declare(strict_types=1);
namespace App\Http\Helpers\Finance\Api;

/**
 * Class References
 * @package TwelveData\Api
 */
class References extends TDApi {

    /**
     * @param array $params
     *
     * @return array
     */
    public function stocks(
        array $params = []
    ) {

        return $this->get(
            'stocks', 
            $params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function forexPairs(
        array $params = []
    ) {

        return $this->get(
            'forex_pairs', 
            $params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function cryptoCurrencies(
        array $params = []
    ) {

        return $this->get(
            'cryptocurrencies', 
            $params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function etfs(
        array $params = []
    ) {

        return $this->get(
            'etf', 
            $params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function indices(
        array $params = []
    ) {

        return $this->get(
            'indices', 
            $params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function exchanges(
        array $params = []
    ) {

        return $this->get(
            'exchanges', 
            $params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function cryptoCurrenciesExchanges(
        array $params = []
    ) {

        return $this->get(
            'cryptocurrency_exchanges', 
            $params);
    }

    /**
     * @return array
     */
    public function indicators() {

        return $this->get(
            'technical_indicators');
    }
}
?>