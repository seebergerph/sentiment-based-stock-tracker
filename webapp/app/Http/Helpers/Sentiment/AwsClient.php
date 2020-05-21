<?php
namespace App\Http\Helpers\Sentiment;

use App;

class AWSClient {

    /** @var  \Aws\Athena\AthenaClient */
    protected $_athena;

    /** @var  string */
    protected $_databaseName;

    /**
     * AwsClient constructor.
     * @param Aws\Credentials\Credentials $credentials
     * @param string $databaseName
     */
    public function __construct($credentials, $databaseName) {
        $this->_databaseName = $databaseName;
        $this->_athena = \Aws\Athena\AthenaClient::factory(array(
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => $credentials)
        );
    }


    /**
     * Gets the averaged sentiment of a stock.
     * @param string $symbol
     * @param string $topics
     * @param string $period
     * @param string $limit
     * 
     * @return Array
     */
    public function getAverageSentiment($symbol, $topics, $period, $limit)
    {
        $queryExecutionId = $this->submitAthenaQuery($this->buildAverageSentimentQuery($symbol, $topics, $period, $limit));

        if($this->waitForQueryToComplete($queryExecutionId))
        {
            $results = $this->getQueryResults($queryExecutionId);
            return $this->processAverageResults($results);
        }
        else
        {
            // Handle error case.
            return array(
                'average' => (float)0, 
                'countTweets' => (integer)0
            );
        }
    }


    /**
     * Gets the detailed sentiment of a stock.
     * @param string $symbol
     * @param string $topics
     * @param string $period
     * @param string $limit
     * 
     * @return Array
     */
    public function getDetailedSentiment($symbol, $topics, $period, $limit)
    {
        $queryExecutionId = $this->submitAthenaQuery($this->buildDetailedSentimentQuery($symbol, $topics, $period, $limit));

        if($this->waitForQueryToComplete($queryExecutionId))
        {
            $results = $this->getQueryResults($queryExecutionId);
            return $this->processDetailedResults($results);
        }
        else
        {
            // Handle error case.
            return array(
                'averagePos' => (float)0,
                'averageNeg' => (float)0,
                'averageNeu' => (float)0, 
                'averageMix' => (float)0, 
                'countTweets' => (integer)0
            );
        }
    }

    /**
     * Gets the last tweets of a stock.
     * @param string $symbol
     * @param string $topics
     * @param string $period
     * @param string $limit
     * 
     * @return Array
     */
    public function getLastTweets($symbol, $topics, $period, $limit=5)
    {
        $queryExecutionId = $this->submitAthenaQuery($this->buildLastTweetsQuery($symbol, $topics, $period, $limit));

        if($this->waitForQueryToComplete($queryExecutionId))
        {
            $results = $this->getQueryResults($queryExecutionId);
            return $this->processLastTweetsResults($results);
        }
        else
        {
            // Handle error case.
            return array();
        }
    }

    protected function submitAthenaQuery($query)
    {
        $queryContext = $this->_athena->StartQueryExecution(array(
            'QueryExecutionContext' => array('Database' => $this->_databaseName),
            'QueryString' => $query,
            'ResultConfiguration' => array(
                'OutputLocation' => 's3://sentiment-based-stock-tracker-us-east-1-athena')
            )
        );

        return $queryContext->get('QueryExecutionId');
    }

    protected function waitForQueryToComplete($queryExecutionId)
    {
        while(1)
        {
            $sleepTime = 100000; // 100ms in us.
            $result = $this->_athena->getQueryExecution(array('QueryExecutionId' => $queryExecutionId));
            $res = $result->toArray();
    
            if($res['QueryExecution']['Status']['State']=='FAILED')
            {
                return false;
            }
            else if($res['QueryExecution']['Status']['State']=='CANCELED')
            {
                return false;
            }
            else if($res['QueryExecution']['Status']['State']=='SUCCEEDED')
            {
                return true;
            }

            // Wait increasing sleep time for preventing throttling limit by long queries.
            usleep($sleepTime);
            $sleepTime = $sleepTime * 2;
        }
    }

    protected function getQueryResults($queryExecutionId)
    {
        $result = $this->_athena->GetQueryResults(array(
            'QueryExecutionId' => $queryExecutionId)
        );
            
        return $result->get('ResultSet');
    }

    protected function processAverageResults($result)
    {
        $rows = $result['Rows'];
        $countTweets = $rows[1]['Data'][0]['VarCharValue'];
        $average = $rows[1]['Data'][1]['VarCharValue'];
        return array(
            'average' => (float)$average * 100, 
            'countTweets' => (integer)$countTweets
        );
    }

    protected function processDetailedResults($result)
    {
        $rows = $result['Rows'];
        $countTweets = $rows[1]['Data'][0]['VarCharValue'];
        $averagePos = $rows[1]['Data'][1]['VarCharValue'];
        $averageNeg = $rows[1]['Data'][2]['VarCharValue'];
        $averageNeu = $rows[1]['Data'][3]['VarCharValue'];
        $averageMix = $rows[1]['Data'][4]['VarCharValue'];
        return array(
            'averagePos' => (float)$averagePos * 100,
            'averageNeg' => (float)$averageNeg * 100, 
            'averageNeu' => (float)$averageNeu * 100, 
            'averageMix' => (float)$averageMix * 100, 
            'countTweets' => (integer)$countTweets
        );
    }

    protected function processLastTweetsResults($result)
    {
        $tweets = array();
        $rows = $result['Rows'];
        for($i=0; $i < count($rows); $i++)
        {
            if($i != 0)
            {
                array_push($tweets, array(
                    'sentiment' => $rows[$i]['Data'][0]['VarCharValue'],
                    'text' => $rows[$i]['Data'][1]['VarCharValue'])
                );
            }
        }

        return $tweets;
    }

    protected function buildAverageSentimentQuery($symbol, $topics, $period, $limit)
    {
        $query = "SELECT COUNT(*), CAST((SELECT COUNT(*) FROM (SELECT * FROM tweet_sentiments WHERE stocksymbol='" . $symbol . "'". $this->buildTopicsCondition($topics) . " AND sentiment='POSITIVE'" . $this->buildPeriodCondition($period) . " LIMIT " . $limit . ")) AS decimal(10,2)) / (SELECT COUNT(*) FROM (SELECT * FROM tweet_sentiments WHERE stocksymbol='" . $symbol . "'" . $this->buildTopicsCondition($topics) . " AND (sentiment='POSITIVE' OR sentiment='NEGATIVE')" . $this->buildPeriodCondition($period) . " LIMIT " . $limit . ")) FROM (SELECT * FROM tweet_sentiments WHERE stocksymbol='" . $symbol . "'" . $this->buildTopicsCondition($topics) . $this->buildPeriodCondition($period) . " LIMIT " . $limit . ")";
        return $query;
    }

    protected function buildDetailedSentimentQuery($symbol, $topics ,$period, $limit)
    {
        $query = "SELECT COUNT(*), CAST((SELECT COUNT(*) FROM (SELECT * FROM tweet_sentiments WHERE stocksymbol='" . $symbol . "'". $this->buildTopicsCondition($topics) . " AND sentiment='POSITIVE'" . $this->buildPeriodCondition($period) . " LIMIT " . $limit . "))  AS decimal(10,2)) / COUNT(*), CAST((SELECT COUNT(*) FROM (SELECT * FROM tweet_sentiments WHERE stocksymbol='" . $symbol . "'" . $this->buildTopicsCondition($topics) . " AND sentiment='NEGATIVE'" . $this->buildPeriodCondition($period) . " LIMIT " . $limit . "))  AS decimal(10,2)) / COUNT(*), CAST((SELECT COUNT(*) FROM (SELECT * FROM tweet_sentiments WHERE stocksymbol='" . $symbol . "'" . $this->buildTopicsCondition($topics) . " AND sentiment='NEUTRAL'" . $this->buildPeriodCondition($period) . " LIMIT " . $limit . "))  AS decimal(10,2)) / COUNT(*), CAST((SELECT COUNT(*) FROM (SELECT * FROM tweet_sentiments WHERE stocksymbol='" . $symbol . "'" . $this->buildTopicsCondition($topics) . " AND sentiment='MIXED'" . $this->buildPeriodCondition($period) . " LIMIT " . $limit . ")) AS decimal(10,2)) / COUNT(*) FROM (SELECT * FROM tweet_sentiments WHERE stocksymbol='" . $symbol . "'" . $this->buildTopicsCondition($topics) . $this->buildPeriodCondition($period) . " LIMIT " . $limit . ")";
        return $query;
    }

    protected function buildLastTweetsQuery($symbol, $topics, $period, $limit)
    {
        $query = "SELECT sentiment, text FROM (SELECT * FROM tweet_sentiments WHERE stocksymbol='" . $symbol . "'" . $this->buildTopicsCondition($topics) . " AND (sentiment='POSITIVE' OR sentiment='NEGATIVE') " . $this->buildPeriodCondition($period) . " LIMIT " . $limit . ")";
        return $query;
    }

    protected function buildTopicsCondition($topics)
    {
        if(count($topics) > 0) {
            $condition = " AND (";
            for ($i = 0; $i < count($topics); $i++) {
                $condition = $condition . "stocktopic='" . $topics[$i] . "'";
                if($i < count($topics)-1) {
                    $condition = $condition . " OR ";
                }
            }
            return $condition . ")";
        }
        return "";
    }

    protected function buildPeriodCondition($period) 
    {
        $condition = " AND (CAST(from_unixtime(CAST(timestamp_ms/1000 AS bigint)) AS date) BETWEEN current_date - interval '";
        $condition = $condition . $period . "' day AND current_date)";
        return $condition;
    }
}
?>