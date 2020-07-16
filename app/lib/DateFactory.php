<?php
namespace WpOtoPoster;


class DateFactory {

	private $now;

	public function __construct(){
		$this->year = date('Y');

	}

	public function getYears(){
		$years = array();
		for($x = 0 ; $x <= 20 ; $x++){
			$years[] = $this->year + $x;
		}
		return $years;
	}

	public function getMonths(){
		return array(1,2,3,4,5,6,7,8,9,10,11,12);
	}

	public function getDays(){
		return array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
	}

	public function getDaysOfTheWeek(){
		return array('monday' => __('Monday',WP_OTO_POSTER),
			         'tuesday' => __('Tuesday',WP_OTO_POSTER),
			         'wednesday' => __('Wednesday',WP_OTO_POSTER),
			         'thursday' => __('Thursday',WP_OTO_POSTER),
			         'friday' => __('Friday',WP_OTO_POSTER),
			         'saturday' => __('Saturday',WP_OTO_POSTER),
			         'sunday' => __('Sunday',WP_OTO_POSTER)
			    );
	}

	public function getThs(){
		return array(1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth', 5 => 'fifth');
	}

	public function getHours(){
		return array(0 => '00',
					 1 => '01',
					 2 => '02',
					 3 => '03',
					 4 => '04',
					 5 => '05',
					 6 => '06',
					 7 => '07',
					 8 => '08',
					 9 => '09',
					 10 => '10',
					 11 => '11',
					 12 => '12',
					 13 => '13',
					 14 => '14',
					 15 => '15',
					 16 => '16',
					 17 => '17',
					 18 => '18',
					 19 => '19',
					 20 => '20',
					 21 => '21',
					 22 => '22',
					 23 => '23');
	}

	public function getMins(){
		return array(0 => '00', 30 => '30');
	}

	public function getTimezones(){
		$timezones = array();
		$timestamp = time();
		foreach(timezone_identifiers_list() as $key => $zone) {
			date_default_timezone_set($zone);
			$timezones[$key]['zone'] = $zone;
			$timezones[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
		}
		return $timezones;
	}

}