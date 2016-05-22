<?php

/**
 * 二次开发蚁群算法
 * 
 * ACO(ant colony optimization, ACO)类
 * @author chloroplast1983
 * @version 1.0.20150826
 * @link http://baike.baidu.com/link?url=zUtQ0pQayBjGI6j3k8i1jJ4tWduoTl0c7iyqmH8VV5irTvcmMkeckBBVS-adubim3dYg4zD0YtQlCDTmpNc_T_
 */

include_once 'Ant.class.php';

class Aco {

	private $ants; //蚂蚁
	private $antNum;//蚂蚁数量
	private $cityNum;//城市数量
	private $MAX_GEN;//运行代数
	private $pheromone;//信息素矩阵
	private $distance;//距离矩阵
	private $bestLength;//最佳长度
	private $bestTour;//最佳路径
	private $firstCity;
	private $lastCity;

	//三个参数
	private $alpha;
	private $beta;
	private $rho;

	public function __construct($antNum,$maxgen,$alpha,$beta,$rho){
		$this -> antNum = $antNum;
		$this -> MAX_GEN = $maxgen;
		$this -> alpha = $alpha;
		$this -> beta = $beta;
		$this -> rho = $rho;
	}	

	/**
	 * 初始化
	 * $firstCity -1 无起始城市
	 * $lastCity -1 无终点城市
	 */
	public function init($distance,$firstCity = -1, $lastCity = -1){

		$this -> distance = $distance;
		$this -> cityNum = sizeof($distance);
		$this -> firstCity = $firstCity;
		$this -> lastCity = $lastCity;
		//初始化信息素矩阵
		for($i = 0; $i < $this -> cityNum; $i++){
			for($j = 0; $j < $this -> cityNum; $j++){
				$this -> pheromone[$i][$j] = 0.1;
			}
		}
		
		$this -> bestLength = PHP_INT_MAX;

		//随机放置蚂蚁
		for($i = 0; $i < $this -> antNum; $i++){
			$this -> ants[$i] = new Ant();
			$this -> ants[$i] -> init($distance,$this -> alpha, $this -> beta,$this -> firstCity, $this -> lastCity);
		}
	}

	public function slove(){
		$notSelectCityCount = 1;//默认起点城市不选择

		if($this -> lastCity != $this -> firstCity && $this -> lastCity >=0){
			$notSelectCityCount++;//起点,终点城市不同 2个城市不选择
		}

		for($g = 0; $g < $this -> MAX_GEN; $g ++){
			for($i = 0; $i < $this -> antNum; $i++){

				for($j = 0; $j < $this -> cityNum - $notSelectCityCount; $j++){
					$this -> ants[$i] -> selectNextCity($this -> pheromone);
				}

				$this -> ants[$i] -> getTabu()[] = $this -> ants[$i] -> getFirstCity();

				if($this -> ants[$i] -> getTourLength() < $this -> bestLength){
					$this -> bestLength = $this -> ants[$i] -> getTourLength();
					for($k = 0; $k < $this -> cityNum; $k ++){
						$this -> bestTour[$k] = $this -> ants[$i] -> getTabu()[$k];
					}
					//if($this -> lastCity != $this -> firstCity && $this -> lastCity >=0){
						//如果起点终点城市不同则 A -> B -> C 3条路径
						//for($k = 0; $k < $this -> cityNum; $k ++){
							//$this -> bestTour[$k] = $this -> ants[$i] -> getTabu()[$k];
						//}
					//}else{
						//如果起点终点城市相同则 A -> B -> C -> A 4条路径
						//for($k = 0; $k < $this -> cityNum; $k ++){
							//$this -> bestTour[$k] = $this -> ants[$i] -> getTabu()[$k];
						//}
					//}
				}
				for($j = 0; $j < $this -> cityNum-1; $j++){
					$this -> ants[$i] -> getDelta()[$this -> ants[$i] -> getTabu()[$j]][$this -> ants[$i] -> getTabu()[$j+1]] =  floatval(1.0/$this -> ants[$i] -> getTourLength()); 
					$this -> ants[$i] -> getDelta()[$this -> ants[$i] -> getTabu()[$j+1]][$this -> ants[$i] -> getTabu()[$j]] =  floatval(1.0/$this -> ants[$i] -> getTourLength()); 
				}

			}

			//更新信息素
			$this -> updatePheromone();

			//重新初始化蚂蚁
			for($i = 0; $i < $this -> antNum; $i++){
				$this -> ants[$i] -> init($this -> distance, $this -> alpha, $this -> beta,$this -> firstCity, $this -> lastCity);
			}
		}

		//打印最佳结果
		// $this -> printOptimal();
	}

	//更新信息素
	private function updatePheromone(){

		for($i = 0; $i < $this -> cityNum; $i++){
			for($j = 0; $j < $this -> cityNum; $j++){
				$this -> pheromone[$i][$j] = $this -> pheromone[$j][$j]*(1 - $this -> rho);  
			}
		}
		//信息素更新 
		for($i = 0; $i < $this -> cityNum; $i++){
			for($j = 0; $j < $this -> cityNum; $j++){
				for($k = 0; $k < $this -> antNum; $k++){
					$this -> pheromone[$i][$j] += $this -> ants[$k] -> getDelta()[$i][$j];
				}
			}
		} 
	}

	// private function printOptimal(){
		// echo "The optimal length is: ". $this -> bestLength;
		// echo "The optimal tour is: ";
		// for($i = 0; $i < $this -> cityNum; $i++){
		// 	echo $this -> bestTour[$i];
		// }
	// }
	public function getBestLength(){
		return $this -> bestLength;
	}

	public function getBestTour(){
		return $this -> bestTour;
	}
}
?>
