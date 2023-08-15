<?php namespace WSUWP\Plugin\Events_API;

class Event {

	private $id;
	private $title;
	private $summary;
	private $content;
	private $url;
	private $start_date;
	private $end_date;
	private $start_time;
	private $end_time;
	private $is_all_day;
	private $venue;

	public function __construct(
		$id,
		$title,
		$summary,
		$content,
		$url,
		$start_date,
		$end_date,
		$start_time,
		$end_time,
		$is_all_day,
		$venue
	) {

		$this->id         = $id;
		$this->title      = $title;
		$this->summary    = $summary;
		$this->content    = $content;
		$this->url        = $url;
		$this->start_date = $start_date;
		$this->end_date   = $end_date;
		$this->start_time = $start_time;
		$this->end_time   = $end_time;
		$this->is_all_day = $is_all_day;
		$this->venue      = $venue;

	}

	public function serialize() {

		return array(
			'id'         => $this->id,
			'title'      => $this->title,
			'summary'    => $this->summary,
			'content'    => $this->content,
			'url'        => $this->url,
			'start_date' => $this->start_date,
			'end_date'   => $this->end_date,
			'start_time' => $this->start_time,
			'end_time'   => $this->end_time,
			'is_all_day' => $this->is_all_day,
			'venue'      => $this->venue,
		);

	}

}
