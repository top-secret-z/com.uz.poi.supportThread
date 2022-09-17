<?php
namespace poi\system\event\listener;
use poi\data\poi\Poi;
use poi\data\poi\PoiEditor;
use wbb\data\board\BoardCache;
use wbb\data\post\PostAction;
use wbb\data\thread\Thread;
use wbb\data\thread\ThreadAction;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\tagging\TagEngine;

/**
 * Creates the support thread.
 *
 * @author		Udo Zaydowicz
 * @copyright	2020-2022 Zaydowicz.de
 * @license		Zaydowicz Commercial License <https://zaydowicz.de>
 * @package		com.uz.poi.supportThread
 */
class PoiActionSupportThreadListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if ($eventObj->getActionName() == 'triggerPublication') {
			// check board ids
			$board = null;
			$categoryIDs = [];
			
			if (POI_SUPPORT_THREAD_SINGLE_BOARD) {
				$board = BoardCache::getInstance()->getBoard(POI_SUPPORT_THREAD_BOARD_ID);
				if ($board === null || !$board->isBoard()) return;
				
				if (POI_SUPPORT_THREAD_CATEGORIES) {
					$categoryIDs = explode("\n", POI_SUPPORT_THREAD_CATEGORIES);
				}
			}
			
			foreach ($eventObj->getObjects() as $poi) {
				$poi = new PoiEditor(new Poi($poi->poiID));
				
				// check categories
				if (!empty($categoryIDs) || !POI_SUPPORT_THREAD_SINGLE_BOARD) {
					$result = false;
					
					if (POI_SUPPORT_THREAD_SINGLE_BOARD) {
						if (in_array($poi->categoryID, $categoryIDs)) {
							$result = true;
						}
					}
					else {
						if ($poi->getCategory()->supportThreadBoardID) {
							$board = BoardCache::getInstance()->getBoard($poi->getCategory()->supportThreadBoardID);
							if ($board === null || !$board->isBoard()) {
								$board = null;
							}
							else {
								$result = true;
							}
						}
					}
					
					if (!$result) continue;
				}
				
				// Poi thread
				if ($poi->supportThreadID) continue;
				
				// language
				if ($poi->languageID) {
					$language = LanguageFactory::getInstance()->getLanguage($poi->languageID);
				}
				else {
					$language = LanguageFactory::getInstance()->getDefaultLanguage();
				}
				
				// tags
				$tags = [];
				if (MODULE_TAGGING) {
					$tagObjects = TagEngine::getInstance()->getObjectTags(
						'com.uz.poi.poi',
						$poi->poiID,
						[$poi->languageID === null ? LanguageFactory::getInstance()->getDefaultLanguageID() : ""]
					);
					
					foreach ($tagObjects as $tagObject) {
						$tags[] = $tagObject->getTitle();
					}
				}
				
				// thread
				$htmlInputProcessor = new HtmlInputProcessor();
				$htmlInputProcessor->process($language->getDynamicVariable('poi.poi.supportThread.message', ['poi' => $poi->getDecoratedObject()]), 'com.woltlab.wbb.post');
				$threadData = [
						'data' => [
								'boardID' => $board->boardID,
								'languageID' => (count(LanguageFactory::getInstance()->getContentLanguages()) ? $poi->languageID : null),
								'topic' => $language->getDynamicVariable('poi.poi.supportThread.subject', ['poi' => $poi->getDecoratedObject()]),
								'time' => $poi->time,
								'userID' => $poi->userID,
								'username' => $poi->username
						],
						'postData' => [],
						'board' => $board,
						'tags' => $tags,
						'htmlInputProcessor' => $htmlInputProcessor
				];
				$objectAction = new ThreadAction([], 'create', $threadData);
				$resultValues = $objectAction->executeAction();
				
				// update support thread id
				$poiEditor = $poi;
				$poiEditor->update(['supportThreadID' => $resultValues['returnValues']->threadID]);
				
				// mark thread as read
				$threadAction = new ThreadAction([$resultValues['returnValues']], 'markAsRead');
				$threadAction->executeAction();
			}
		}
		
		// update
		if ($eventObj->getActionName() == 'update') {
			foreach ($eventObj->getObjects() as $poi) {
				if ($poi->supportThreadID) {
					$thread = new Thread($poi->supportThreadID);
					$post = $thread->getFirstPost();
					$poi = new PoiEditor(new Poi($poi->poiID));
					
					// get language
					if ($poi->languageID) {
						$language = LanguageFactory::getInstance()->getLanguage($poi->languageID);
					}
					else {
						$language = LanguageFactory::getInstance()->getDefaultLanguage();
					}
					
					// get tags
					$tags = [];
					if (MODULE_TAGGING) {
						$tagObjects = TagEngine::getInstance()->getObjectTags(
							'com.uz.poi.poi',
							$poi->poiID,
							[$poi->languageID === null ? LanguageFactory::getInstance()->getDefaultLanguageID() : ""]
						);
						foreach ($tagObjects as $tagObject) {
							$tags[] = $tagObject->getTitle();
						}
					}
					$threadAction = new ThreadAction([$thread], 'update', [
						'data' => [
								'tags' => $tags,
								'languageID' => (count(LanguageFactory::getInstance()->getContentLanguages()) ? $poi->languageID : null),
								'topic' => $language->getDynamicVariable('poi.poi.supportThread.subject', ['poi' => $poi->getDecoratedObject()])
						]
					]);
					$threadAction->executeAction();
					$htmlInputProcessor = new HtmlInputProcessor();
					$htmlInputProcessor->process($language->getDynamicVariable('poi.poi.supportThread.message', ['poi' => $poi->getDecoratedObject()]), 'com.woltlab.wbb.post');
					$postAction = new PostAction([$post], 'update', ['htmlInputProcessor' => $htmlInputProcessor]);
					$postAction->executeAction();
				}
			}
		}
		
		// disable
		if ($eventObj->getActionName() == 'disable') {
			foreach ($eventObj->getObjects() as $poi) {
				if ($poi->supportThreadID) {
					$thread = new Thread($poi->supportThreadID);
					$threadAction = new ThreadAction([$thread], 'disable');
					$threadAction->executeAction();
				}
			}
		}
		
		// enable
		if ($eventObj->getActionName() == 'enable') {
			foreach ($eventObj->getObjects() as $poi) {
				if ($poi->supportThreadID) {
					$thread = new Thread($poi->supportThreadID);
					$threadAction = new ThreadAction([$thread], 'enable');
					$threadAction->executeAction();
				}
			}
		}
		
		// trash
		if ($eventObj->getActionName() == 'trash') {
			foreach ($eventObj->getObjects() as $poi) {
				if ($poi->supportThreadID) {
					$thread = new Thread($poi->supportThreadID);
					$threadAction = new ThreadAction([$thread], 'trash');
					$threadAction->executeAction();
				}
			}
		}
		
		// restore
		if ($eventObj->getActionName() == 'restore') {
			foreach ($eventObj->getObjects() as $poi) {
				if ($poi->supportThreadID) {
					$thread = new Thread($poi->supportThreadID);
					$threadAction = new ThreadAction([$thread], 'restore');
					$threadAction->executeAction();
				}
			}
		}
		
		//delete
		if ($eventObj->getActionName() == 'delete') {
			foreach ($eventObj->getObjects() as $poi) {
				if ($poi->supportThreadID) {
					$thread = new Thread($poi->supportThreadID);
					$threadAction = new ThreadAction([$thread], 'delete');
					$threadAction->executeAction();
				}
			}
		}
	}
}
