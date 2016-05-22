<?php

/**
 * 蚂蚁类
 * @author chloroplast1983
 * @version 1.0.20150826
 */
class Ant {

	private $tabu;//禁忌表
	private $allowedCities;//允许搜索的城市
	private $delta;//信息数变化矩阵
	private $distance;//距离矩阵

	private $alpha;
	private $beta;

	private $tourLength;//路径长度
	private $cityNum;//城市数量

	private $firstCity;//起始城市
	private $currentCity;//当前城市
	private $lastCity;//终点城市

	public function __construct(){
		$this -> cityNum = 0;
		$this -> tourLength = 0;
	}

	/**
	 * 初始化蚂蚁, 随机选择起始位置
	 * @param array distance
	 * @param double alpha 
	 * @param double beta
	 */
	public function init($distance, $alpha, $beta, $firstCity, $lastCity){
		$this -> alpha = $alpha;
		$this -> beta = $beta;
		$this -> allowedCities = array();
		$this -> tabu = array();
		$this -> distance = $distance;
		$this -> delta = array();
        
        $this->cityNum = sizeof($distance);
		for($i = 0; $i < $this->cityNum; $i++){
			$this -> allowedCities[] = $i;
			for($j = 0; $j < $this -> cityNum; $j++){
				$this -> delta[$i][$j] = 0.0;
			}
		}
        //var_dump($this->firstCity.'---'.$this->lastCity);exit();
		$this->firstCity = $firstCity >= 0 ? $firstCity : rand(0,$this -> cityNum - 1);
		if($lastCity >= 0){
			$this -> lastCity = $lastCity;
			unset($this -> allowedCities[$this -> lastCity]);
		}
		// $this -> tabu[$this -> cityNum] = $this -> lastCity;
		unset($this -> allowedCities[$firstCity]);
        //去除掉起始和结束城市的城市总数
		$this -> tabu[] = $firstCity;
		$this -> currentCity = $firstCity;
	}

	/**
	 * 选择下一个城市
	 * @param pheromone 信息素矩阵
	 */
	public function selectNextCity($pheromone){
		$p = array();
		$sum = 0.0;


		//计算分母部分
		foreach($this -> allowedCities as $val){
			$sum += pow($pheromone[$this -> currentCity][$val], $this -> alpha) * pow(1.0/$this -> distance[$this -> currentCity][$val], $this -> beta);
		}

		//计算概率矩阵
		for($i = 0; $i < $this -> cityNum; $i++){

			$flag = false;

			foreach($this -> allowedCities as $val){

				if($i == $val){
					$p[$i] = floatval((pow($pheromone[$this -> currentCity][$i], $this -> alpha) * pow(1.0/$this -> distance[$this -> currentCity][$i], $this -> beta))/$sum);
					$flag = true;
					break;
				}
			}

			if($flag == false){
				$p[$i] = 0.0;
			}
		}

		//轮盘赌选择下一个城市
		$sleectP = rand(1,99)/100;
		$selectCity = 0;
		$sum1 = 0.0;
		for($i = 0; $i < $this -> cityNum; $i++){
			// var_dump($sum1."******".$p[$i]);
			$sum1 += $p[$i];
			// var_dump($sum1."******".$sleectP);
			if($sum1 >= $sleectP){
				// var_dump("selectCity:".$i);
				$selectCity = $i;
				break;
			}
		}

		// exit();
		//从允许选择的城市中去除select city
		foreach($this -> allowedCities as $key => $val){
			if($val == $selectCity){
				unset($this -> allowedCities[$key]);
				break;
			}
		}
		// var_dump("allowed city:");
		// var_dump($this -> allowedCities);
		// exit();
		//在禁忌表中添加select city
		$this -> tabu[] = $selectCity;
	    //将当前城市改为选择的城市
		$this -> currentCity = $selectCity;
	}

	/**
	 * 计算路径长度
	 * @return 路径长度
	 */

	private function calculateTourLength(){
        $len = 0;
		if($this -> lastCity >= 0){
            //var_dump($this->tabu);
			if($this -> lastCity >=0 && $this -> lastCity != $this -> firstCity){
				$this -> tabu[$this -> cityNum-1] = $this -> lastCity;//设置终点, 因为 A -> B -> C 3次路径
                //var_dump($this->tabu);
                //var_dump($this->cityNum);exit();
            }
            //else{
				//$this -> tabu[$this -> cityNum] = $this -> lastCity;//因为从 A -> B -> C -> A (A 返回 A) 4次路径 等于多一个点
			//}			
		}
        //var_dump($this->tabu);exit();
		for($i = 1; $i < $this->cityNum; $i++){
			$len += $this -> distance[$this -> tabu[$i]][$this -> tabu[$i-1]];
		}
        
        //如果出发和结束城市为同一个城市,加上从最后返回出发城市的距离
        //则需要再次返回出发城市,否则不需要返回出发城市直接在最后城市终止行程即可
        if($this->lastCity == $this->firstCity){
            $len += $this->distance[$this->tabu[$this->cityNum-1]][$this->firstCity];
        }
		return $len;
	}

    public function getTabu(){
    	return $this -> tabu;
    }

    public function getTourLength(){
    	$this -> tourLength = $this -> calculateTourLength();
    	return $this -> tourLength;
    }

    public function getDelta(){
    	return $this -> delta;
    }

    public function getFirstCity(){
    	return $this -> firstCity;
    }
}

?>
