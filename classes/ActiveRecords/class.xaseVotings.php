<?php
/**
 * Class xaseVotings
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */

class xaseVotings {

	/**
	 * @param int $assisted_exercise_object_id
	 * @param int $user_id
	 *
	 * @return ilObjAssistedExercise[]
	 */
	public static function getUnvotedAnswersOfUser($assisted_exercise_object_id, $user_id, $item_id) {
		global $ilDB;

		$sql = "SELECT answer.* FROM ilias.rep_robj_xase_answer as answer
				inner join rep_robj_xase_item as item on item.id = answer.item_id
				where 
				answer.id not in (SELECT answer_id from rep_robj_xase_voting as voting where user_id = ".$ilDB->quote($user_id,'integer').") 
				and item.assisted_exercise_id = ".$ilDB->quote($assisted_exercise_object_id,'integer') . " and answer.item_id = " .$ilDB->quote($item_id, 'integer');


		$set = $ilDB->query($sql);

		$arr_answers = array();
		while($row = $ilDB->fetchAssoc($set)) {
			$arr_answers[] = new xaseAnswer($row['id']);
		}

		return $arr_answers;
	}


	/**
	 * @param int $assisted_exercise_object_id
	 * @param int $user_id
	 *
	 * @return bool|xaseAnswer
	 */
	public static function getBestVotedAnswerOfUser($assisted_exercise_object_id, $user_id, $item_id) {
		global $ilDB;

		$sql = "SELECT answer.* FROM ilias.rep_robj_xase_answer as answer
				inner join rep_robj_xase_item as item on item.id = answer.item_id
				inner join rep_robj_xase_voting as voting on voting.user_id = ".$ilDB->quote($user_id,'integer')." and voting.item_id = item.id and voting.voting_type = ".xaseVoting::VOTING_TYPE_UP."
				where 
			    item.assisted_exercise_id = ".$ilDB->quote($assisted_exercise_object_id,'integer') . " and answer.item_id = " .$ilDB->quote($item_id, 'integer');

		$set = $ilDB->query($sql);

		//return the first result. should be only one with this state!
		while($row = $ilDB->fetchAssoc($set)) {
			return new xaseAnswer($row['id']);
		}

		return false;
	}
}