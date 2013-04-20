<?php
class IZ_Custom_Model_System_Config_StaticBlocks
{
	public function toOptionArray()
	{
		$groupsArray = array();

		$staticblocks = Mage::getModel('cms/block')->getCollection();

		foreach ($staticblocks as $block) {
			$groupsArray[$block->getIdentifier()] = $block->getTitle();
		}

		return $groupsArray;
	}
}


