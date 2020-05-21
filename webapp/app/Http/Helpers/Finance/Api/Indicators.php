<?php
declare(strict_types=1);
namespace App\Http\Helpers\Finance\Api;

/**
 * Class Indicators
 * @package TwelveData\Api
 */
class Indicators extends TDApi {

    protected const INDICATORS = array("ad", "add", "adosc", "adx", "adxr", "apo", "aroon",
                          "aroonosc", "atr", "avg", "avgprice", "bbands", "beta",
                          "bop", "cci", "ceil", "cmo", "coppock", "correl", "crsi",
                          "dema", "div", "dpo", "dx", "ema", "exp", "floor",
                          "heikinashicandles", "hlc3", "ht_dcperiod", "ht_dcphase",
                          "ht_phasor", "ht_sine", "ht_trendline", "ht_trendmode",
                          "kama", "linearreg", "linearregangle", "linearregintercept",
                          "linearregslope", "ln", "log10", "ma", "macd", "macdext",
                          "mama", "max", "maxindex", "medprice", "mfi", "midpoint",
                          "midprice", "min", "minindex", "minmax", "minmaxindex",
                          "minus_di", "minus_dm", "mom", "mult", "natr", "obv",
                          "percent_b", "plus_di", "plus_dm", "ppo", "roc", "rocp",
                          "rocr", "rocr100", "rsi", "sar", "sarext", "sma", "sqrt",
                          "stddev", "stoch", "stochf", "stochrsi", "sub", "sum",
                          "t3ma", "tema", "trange", "trima", "tsf", "typprice",
                          "ultosc", "var", "wclprice", "willr", "wma");

    /**
     * @param string $indicatorName
     * @param string $symbol
     * @param string $interval
     * @param array $params
     *
     * @return array
     */
    public function indicator(
        string $indicatorName, 
        string $symbol, 
        string $interval = self::INTERVAL_1h,
        array $params = []) {

        $name = $this->getIndicatorName($indicatorName);

        if(!in_array($name, self::INDICATORS)) {
            throw new InvalidArgumentException(sprintf('Indicator name not available: "%s"', $name));
        }

        $requiredParams = [
            'symbol' => $symbol,
            'interval' => $interval
        ];

        return $this->get(
            $name, 
            array_merge(
                $requiredParams,
                $params
            ));       
    }

    /**
     * @param string $name
     * 
     * @return string
     */
    protected function getIndicatorName(string $name): string
    {
        return strtolower($name);
    }
}
?>