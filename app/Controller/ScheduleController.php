<?php
App::uses('AppController', 'Controller');

class ScheduleController extends AppController {
	public $uses = ['Config', 'Schedule'];

	/**
	 * Before Filter Hook
	 *
	 * Set's the active tab to be staff
	 */
	public function beforeFilter() {
		parent::beforeFilter();

		// Require staff
		$this->Auth->protect(env('GROUP_STAFF'));

		// Tell the template we're in the staff dropdown
		$this->set('at_staff', true);
	}

	/**
	 * Overview Page 
	 *
	 * @url /schedule
	 * @url /schedule/index
	 */
	public function index() {
		$bounds = $this->Schedule->getScheduleBounds();

		$this->set('start', $bounds['min']);
		$this->set('end', $bounds['max']);
	}

	/**
	 * Overview API Page
	 *
	 * @url /schedule/api
	 */
	public function api() {
		if (
			$this->request->is('post') &&
			isset($this->request->data['changes']) &&
			is_array($this->request->data['changes'])
		) {
			foreach ( $this->request->data['changes'] AS $c ) {
				$schedule = $this->Schedule->findById($c['id']);
				if ( empty($schedule) ) continue;

				$start = ($schedule['Schedule']['fuzzy'] ? $c['start'] - COMPETITION_START : $c['start']);
				$end = ($schedule['Schedule']['fuzzy'] ? $c['end'] - COMPETITION_START : $c['end']);

				$this->Schedule->id = $c['id'];
				$this->Schedule->save([
					'start' => $start,
					'end'   => $end,
				]);
			}

			return $this->ajaxResponse(true);
		}
		$out = ['data' => []];

		$schedules = $this->Schedule->getAllSchedules();
		$bounds = $this->Schedule->getScheduleBounds();

		foreach ( $schedules AS $s ) {
			$out['data'][] = [
				'id'         => $s->getScheduleId(),
				'inject_id'  => $s->getInjectId(),
				'text'       => $s->getTitle().' ('.$s->getGroupName().')',
				'group'      => $s->getGroupName(),
				'start_date' => date('d-m-Y G:i:s', $s->getStart() > 0 ? $s->getStart() : $bounds['min']),
				'start_ts'   => $s->getStart(),
				'end_date'   => date('d-m-Y G:i:s', $s->getEnd() > 0 ? $s->getEnd() : $bounds['max']),
				'end_ts'     => $s->getEnd(),
			];
		}

		return $this->ajaxResponse($out);
	}

	/**
	 * Manager Page
	 *
	 * @url /schedule/manager
	 */
	public function manager() {
		$this->set('injects', $this->Schedule->getAllSchedules(false));
	}

	/**
	 * Flip the status of a schedule
	 *
	 * @url /schedule/flip/<sid>
	 */
	public function flip($sid=false) {
		$schedule = $this->Schedule->findById($sid);

		if ( !empty($schedule) ) {
			$this->Schedule->id = $sid;
			$this->Schedule->save([
				'active' => !($schedule['Schedule']['active']),
			]);

			$msg = sprintf('%sctivated inject "%s"', ($schedule['Schedule']['active'] ? 'Dea' : 'A'), $schedule['Inject']['title']);

			$this->logMessage(
				'schedule',
				$msg,
				[
					'old_status' => $schedule['Schedule']['active'],
					'new_status' => !$schedule['Schedule']['active'],
				],
				$sid
			);

			$this->Flash->success($msg.'!');
		}

		return $this->redirect('/schedule/manager');
	}

	/**
	 * Delete a schedule. SPOOKY
	 *
	 * @url /schedule/delete/<sid>
	 */
	public function delete($sid) {
		$schedule = $this->Schedule->findById($sid);
		if ( empty($schedule) ) {
			throw new NotFoundException('Unknown Schedule ID.');
		}

		if ( $this->request->is('post') ) {
			$this->Schedule->delete($sid);

			$msg = sprintf('Deleted schedule #%d', $sid);

			$this->logMessage(
				'schedule',
				$msg,
				[
					'schedule' => $schedule['Schedule'],
				],
				$sid
			);

			$this->Flash->success($msg);
			return $this->redirect('/schedule/manager');
		}

		$this->set('schedule', $schedule);
	}

	/**
	 * Edit a schedule.
	 *
	 * @url /schedule/edit/<sid>
	 */
	public function edit($sid) {
		$schedule = $this->Schedule->findById($sid);
		if ( empty($schedule) ) {
			throw new NotFoundException('Unknown Schedule ID.');
		}

		if ( $this->request->is('post') ) {
			$this->Schedule->id = $sid;

			$msg = sprintf('Edited schedule #%d', $sid);

			$this->logMessage(
				'schedule',
				$msg,
				[
					'old_schedule' => $schedule['Schedule'],
					'new_schedule' => $this->data,
				],
				$sid
			);

			$this->Flash->success($msg.'!');
			return $this->redirect('/schedule/manager');
		}

		// Load + setup the InjectStyler helper
		$this->helpers[] = 'InjectStyler';
		$this->helpers['InjectStyler'] = [
			'types'  => $this->Config->getInjectTypes(),
			'inject' => new stdClass(), // Nothing...for now
		];

		$this->set('schedule', $schedule);
	}
}