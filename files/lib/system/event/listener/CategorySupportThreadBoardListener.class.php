<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace poi\system\event\listener;

use wbb\data\board\BoardCache;
use wbb\data\board\BoardNodeList;
use wcf\acp\form\AbstractCategoryEditForm;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Handles board selection for POI category.
 */
class CategorySupportThreadBoardListener implements IParameterizedEventListener
{
    /**
     * data
     */
    protected $boardID = 0;

    protected $boardNodeList;

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        // skip if one board for all
        if (POI_SUPPORT_THREAD_SINGLE_BOARD) {
            return;
        }

        // get boards
        if ($this->boardNodeList === null) {
            $this->boardNodeList = new BoardNodeList();
            $this->boardNodeList->readNodeTree();
        }

        switch ($eventName) {
            case 'assignVariables':
                WCF::getTPL()->assign([
                    'supportThreadBoardNodeList' => $this->boardNodeList->getNodeList(),
                    'supportThreadBoardID' => $this->boardID,
                ]);
                break;

            case 'readData':
                if (empty($_POST) && $eventObj instanceof AbstractCategoryEditForm) {
                    $this->boardID = ($eventObj->category->supportThreadBoardID ?: 0);
                }
                break;

            case 'save':
                $eventObj->additionalData['supportThreadBoardID'] = $this->boardID;
                break;

            case 'validate':
                $this->boardID = (!empty($_POST['supportThreadBoardID'])) ? \intval($_POST['supportThreadBoardID']) : 0;
                if ($this->boardID) {
                    $board = BoardCache::getInstance()->getBoard($this->boardID);
                    if ($board === null || !$board->isBoard()) {
                        throw new UserInputException('supportThreadBoardID', 'invalid');
                    }
                }
                break;
        }
    }
}
