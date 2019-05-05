<?php

/***
 *        __  ___                           __
 *       / / / (_)__  _________ ___________/ /_  __  __
 *      / /_/ / / _ \/ ___/ __ `/ ___/ ___/ __ \/ / / /
 *     / __  / /  __/ /  / /_/ / /  / /__/ / / / /_/ /
 *    /_/ /_/_/\___/_/   \__,_/_/   \___/_/ /_/\__, /
 *                                            /____/
 *
 * Hierarchy - Role-based permission management system
 * Copyright (C) 2019-Present CortexPE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace CortexPE\Hierarchy\cmd\subcommand;


use CortexPE\Hierarchy\cmd\SubCommand;
use CortexPE\Hierarchy\Hierarchy;
use CortexPE\Hierarchy\lang\MessageStore;
use CortexPE\Hierarchy\member\BaseMember;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class GiveRoleCommand extends SubCommand {
    public function __construct(Hierarchy $hierarchy, Command $parent, string $name, array $aliases, string $usageMessage, string $descriptionMessage) {
        parent::__construct($hierarchy, $parent, $name, $aliases, $usageMessage, $descriptionMessage);
        $this->setPermission("hierarchy.role.give");
    }

    public function execute(CommandSender $sender, array $args): void {
		if(count($args) === 2) {
            $role = $this->resolveRole($sender, (int)$args[1]);
			if($role !== null) {
				$target = $args[0];
				$tmp = $sender->getServer()->getPlayer($target);
				if($tmp instanceof Player) {
					$target = $tmp;
				}

				$memberFactory = $this->plugin->getMemberFactory();

				$memberFactory
                        ->getMember($target, true, function (BaseMember $member) use ($memberFactory, $role, $sender): void {
						  if($sender instanceof Player) {
							  if(!$memberFactory
										->getMember($sender)
										->hasHigherPermissionHierarchy($this->getPermission(), $member)) {
								  $sender->sendMessage(MessageStore::getMessage("err.target_higher_hrk", [
									  "target" => $member->getName()
								  ]));

								  return;
							  }
						  }
						  if(!$role->isDefault()) {
							  if(!$member->hasRole($role)) {
								  $member->addRole($role);
								  $sender->sendMessage(MessageStore::getMessage("cmd.give.success", [
									  "role" => $role->getName()
								  ]));
							  } else {
								  $sender->sendMessage(MessageStore::getMessage("cmd.give.has_role", [
									  "role" => $role->getName()
								  ]));
							  }
						  } else {
							  $sender->sendMessage(MessageStore::getMessage("cmd.give.default"));
						  }
					  });
			}
		} else {
			$this->sendUsage($sender);
		}
	}
}