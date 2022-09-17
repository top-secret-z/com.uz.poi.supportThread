<?php
namespace poi\system\event\listener;
use wbb\data\thread\Thread;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Gets the support thread of a poi.
 *
 * @author		Udo Zaydowicz
 * @copyright	2020-2022 Zaydowicz.de
 * @license		Zaydowicz Commercial License <https://zaydowicz.de>
 * @package		com.uz.poi.supportThread
 */
class PoiPageSupportThreadListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if ($eventObj->poi->supportThreadID) {
			WCF::getTPL()->assign([
					'supportThread' => new Thread($eventObj->poi->supportThreadID)
			]);
		}
	}
}
