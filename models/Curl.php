<?

namespace app\models;

Class Curl
{
    /**
     * @var resource CURL
     */
    protected $ch;

    /**
     * @var string адрес запроса
     */
    protected $url;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var bool
     */
    protected $returnData = true;

    /**
     * Curl constructor.
     */
    public function __construct()
    {
        $this->ch = curl_init();
    }

    protected $outHeader = [];

    /**
     * @throws \Exception
     */
    public function request()
    {
        if (!$this->url) throw new \Exception('Не задан URL запроса');

        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, $this->returnData);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, TRUE);

        $body = curl_exec($this->ch);

        // extract header
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $header = substr($body, 0, $headerSize);
        $this->outHeader = $this->extractHeaders($header);

        // extract body
        $body = substr($body, $headerSize);

        return $body;
    }

    public function getHeaders()
    {
        return $this->outHeader;
    }

    public function getNextHash()
    {
        $re = '/(.+?); rel="(.+?)"/m';
        $str = str_replace(["<",">"],"", $this->getHeaders()["Link"]);
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        $matches = current($matches);

        if($matches[2] !== 'next'){
            return false;
        }

        $nextPageURLparam = parse_url($matches[1]);

        parse_str($nextPageURLparam['query'], $value);
        return strlen($value['page_info']) > 0 ? $value['page_info'] : false;
    }

    public function getPrevHash()
    {
        $re = '/(.+?); rel="(.+?)"/m';
        $str = str_replace(["<",">"],"", $this->getHeaders()["Link"]);
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        $matches = current($matches);

        if($matches[2] !== 'previous'){
            return false;
        }

        $nextPageURLparam = parse_url($matches[1]);

        parse_str($nextPageURLparam['query'], $value);
        return strlen($value['page_info']) > 0 ? $value['page_info'] : false;
    }

    private function extractHeaders($respHeaders)
    {
        $headers = array();

        $headerText = substr($respHeaders, 0, strpos($respHeaders, "\r\n\r\n"));

        foreach (explode("\r\n", $headerText) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * @param $option
     * @param $value
     * @return $this
     */
    public function setopt($option, $value)
    {
        curl_setopt($this->ch, $option, $value);
        return $this;
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * @param string $url
     * @return Curl
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param array $headers
     * @return Curl
     */
    public function setHeaders($headers = [])
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param bool $returnData
     * @return Curl
     */
    public function setReturnData($returnData)
    {
        $this->returnData = $returnData;
        return $this;
    }
}
