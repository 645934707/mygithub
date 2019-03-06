<?php

/**
 * Class RedisSentinel
 */

class RedisSentinel {
    private $masterName = null;
    static $redisInstance = array();
    private $config = array(
        'servers' => array(
            0=>array(
                'host' => '10.1.1.1',
                'port' => '26380',
            ),
            1=>array(
                'host' => '10.1.1.2',
                'port' => '26380',
            ),
            2=>array(
                'host' => '10.1.1.3',
                'port' => '26380',
            ),
            3=>array(
                'host' => '10.1.1.4',
                'port' => '26380',
            ),
            4=>array(
                'host' => '10.1.1.5',
                'port' => '26380',
            ),
            5=>array(
                'host' => '10.1.1.6',
                'port' => '26380',
            )
        ),
        'password' => '123456'
    );


	public function actionTestSentinel()
    {
        $master = $this->setShared(0)->getRedisMaster();
        var_dump($master->lPush('test_sentinel', 'svalue1', 'svalue2'));
    }



    /**
     * 哈希表添加文章信息，存储文章信息，通过id关联有序列表
     */
	public function actionAddContent()
    {
        $allarr = array();
        for($i=1; $i<=50; $i++){
            $arr = array(
                'id'=>'test_id'.$i,
                'author'=>'sdfsdsadsaddq121',
                'authorid'=>21321312321,
                'content'=>'你好啊',
                'ip'=>'127.0.0.1',
            );
            $data = serialize($arr);
            $allarr['test_id'.$i] = $data;
        }

        $master = $this->setShared(0)->getRedisMaster();
        var_dump($master->hMSet('test_content', $allarr));
//        $res = $master->hMGet('test_content',array('test_id4'));
//        var_dump($res);
//        var_dump(unserialize($res['test_id4']));
    }

    /**
     * 添加文章有序列表，用来计算分页
     */
	public function actionAddContentSet()
    {
        $master = $this->setShared(0)->getRedisMaster();

        for($i=1; $i<=50; $i++){
            // zAdd(key, score, value) score用字增id还是用时间
            var_dump($master->zAdd('test_set', $i, 'test_id'.$i));
        }


    }

    /**
     * 通过id获取最新n条的z内容
     */
    public function actionGetContentByLastId()
    {
        $id = Yii::app()->request->getParam('last');
        $num = Yii::app()->request->getParam('n',20);//需过滤整数

        $master = $this->setShared(0)->getRedisMaster();
        $key_num = $master->zscore('test_set', $id);
        $data = $master->zrange('test_set', $key_num, $key_num+$num-1);
        var_dump($data);
    }

    /**
     * 设置master名称
     * @param $masterName
     * @return $this
     */
    public function setMasterName($masterName)
    {
        $this->masterName = $masterName;
        return $this;
    }

    /**
     *
     * @param $val 分库值 0,1,2,3,4,5
     * @return object this
     */
    public function setShared($val)
    {
        //master名称，多个主redis
        $cacheNames = array('Cache1', 'Cache2', 'Cache3', 'Cache4','Cache5','Cache6');
        if(array_key_exists($val, $cacheNames)){
            $this->setMasterName($cacheNames[$val]);
        }else{
            return false;
        }
        return $this;
    }

    /**
     * @param $startTime 开始记录的时间
     * @return mixed 花费时间
     */
    public function returnTime($startTime){
        $finalTime = microtime(true);
        return $finalTime - $startTime;
    }

    public function getRedisMaster()
    {
        //自己实现轮询多个哨兵
        $sentinelservers = $this->config['servers'];
        shuffle($sentinelservers);
        $redis = new redis();
        $servernum = count($sentinelservers);
        foreach ($sentinelservers as $key=>$server)
        {
            try{
                $redis->connect($server['host'], $server['port'],1);
                break;
            }catch (Exception $e){
                if($servernum == $key+1){
                    throw new CRedisException("Connect All sentinel failed");
                }
            }
        }
        $masterinfo = $redis->rawCommand('SENTINEL', 'master', $this->masterName);
        $redis->close();
        if(!self::$redisInstance[$this->masterName]){
            $redis->connect($masterinfo['3'],$masterinfo['5']);
            $redis->auth($this->config['password']);
            self::$redisInstance[$this->masterName] = $redis;
        }
        return self::$redisInstance[$this->masterName];
    }
}
