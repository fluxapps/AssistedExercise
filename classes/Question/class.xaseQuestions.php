<?php
/**
 * Class xaseQuestions
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class xaseQuestions {

	/**
	 * @param array $arr_usr_ids
	 * @param array $options
	 *
	 * @return array|bool|int
	 */
	public static function getData(array $options = array(),$usr_id = 0,$obj_id) {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();
		//Permissions
		/*if (count($arr_usr_ids) == 0) {
			return false;
		}*/
		$_options = array(
			'filters' => array(),
			'sort'    => array(),
			'limit'   => array(),
			'count'   => false,
		);
		$options = array_merge($_options, $options);

		$select = 'SELECT 
					question.id as question_id,
					question.title as question_title,
					answer.id as answer_id,
					answer.answer_status as answer_status,
					answer.submission_date as submission_date,
					answer.is_assessed as is_assessed,					
					(select ROUND(AVG(question_severity_rating),1) as question_severity_rating
					from xase_answer where question_severity_rating > 0 AND question_id = question.id) as severity,
					question.created_by as created_by,
					question.max_points as max_points,
					(SELECT count(used_level.id) FROM 
					ilias.xase_used_hint_level as used_level
					inner join xase_hint_level as hint_level on hint_level.id = used_level.hint_level_id
					inner join xase_hint as xase_hint on xase_hint.id = hint_level.hint_id
					inner join xase_question as hint_question on hint_question.id = xase_hint.question_id
					where hint_question.id = question.id and used_level.user_id = answer.user_id) as number_of_used_hints,
					assessm.points_teacher as points_teacher,
					assessm.minus_points as minus_points,
					assessm.additional_points as additional_points_voting,
					assessm.total_points as total_points,
					(select count(*) from xase_voting as voting where voting.answer_id = answer.id and voting_type = 1) as number_of_upvotings,
					(SELECT vote_answer.id
					FROM xase_answer as vote_answer
					inner join xase_question as vote_quest on vote_quest.id = vote_answer.question_id
					inner join xase_voting as voting on voting.question_id = vote_quest.id and voting.voting_type = 1 and voting.answer_id =  vote_answer.id
					where vote_answer.question_id = question.id 
					group by vote_answer.id
					ORDER BY  count(vote_answer.id) DESC LIMIT 1) as highest_ratet_answer
				    from xase_question as question
				     left join xase_answer as answer on answer.question_id = question.id and answer.user_id = '.$ilDB->quote($usr_id,'int').'
                     left join xase_assessm as assessm on assessm.answer_id = answer.id and assessm.user_id = answer.user_id';


		$options['filters']['obj_id'] = $obj_id;

		$select .= static::createWhereStatement(array(), $options['filters']);
		if ($options['count']) {
			$result = $ilDB->query($select);
			return $ilDB->numRows($result);
		}
		if ($options['sort']) {
			$select .= " ORDER BY " . $options['sort']['field'] . " "
				. $options['sort']['direction'];
		}
		if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
			$select .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
		}
		$result = $ilDB->query($select);
		$arr_data = array();
		while ($row = $ilDB->fetchAssoc($result)) {
			$arr_data[] = $row;
		}
		return $arr_data;
	}

	/**
	 * Returns the WHERE Part for the Queries using parameter $user_ids and local variable $filters
	 *
	 * @param array $arr_usr_ids
	 * @param array $arr_filter
	 *
	 * @return bool|string
	 */
	public static function createWhereStatement($arr_usr_ids, $arr_filter) {

		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();
		$where = array();

		$where[] = '(question.assisted_exercise_id = ' . $ilDB->quote($arr_filter['obj_id'], 'integer') . ')';





		//$where[] = $ilDB->in('usr_data.usr_id', $arr_usr_ids, false, 'integer');
		if (!empty($arr_filter['answer_status'])) {
			//todo!
			if($arr_filter['answer_status'] == 1) {
				$where[] = '(answer.answer_status is NULL)';
			} else {
				$where[] = '(answer.answer_status = ' . $ilDB->quote(($arr_filter['answer_status']-1), 'integer') . ')';
			}
		}

		if (!empty($arr_filter['question_title'])) {
				$where[] = $ilDB->like("question.title", "text", "%"
						. $arr_filter['question_title']	. "%");
		}

		/*
		if ($arr_filter['course'] > 0) {
			$where[] = '(crs_ref.ref_id = ' . $ilDB->quote($arr_filter['course'], 'integer') . ')';
		}
		if (!empty($arr_filter['lp_status']) or $arr_filter['lp_status'] === 0) {
			$where[] = '(lp_status = ' . $ilDB->quote($arr_filter['lp_status'], 'integer') . ')';
		}
		if (!empty($arr_filter['memb_status'])) {
			$where[] = '(reg_status = ' . $ilDB->quote($arr_filter['memb_status'], 'integer') . ')';
		}
		if (!empty($arr_filter['user'])) {
			$where[] = "(" . $ilDB->like("usr_data.login", "text", "%" . $arr_filter['user'] . "%")
				. " " . "OR " . $ilDB->like("usr_data.firstname", "text", "%"
					. $arr_filter['user']
					. "%") . " "
				. "OR " . $ilDB->like("usr_data.lastname", "text", "%" . $arr_filter['user']
					. "%") . " " . "OR "
				. $ilDB->like("usr_data.email", "text", "%" . $arr_filter['user'] . "%")
				. ") ";
		}
		if (!empty($arr_filter['org_unit'])) {
			$where[] = 'usr_data.usr_id in (SELECT user_id from il_orgu_ua where orgu_id = '
				. $ilDB->quote($arr_filter['org_unit'], 'integer') . ')';
		}*/
		if (!empty($where)) {
			return ' WHERE ' . implode(' AND ', $where) . ' ';
		} else {
			return '';
		}
	}


	/**
	 * @param int $assisted_exercise_object_id
	 * @param int $user_id
	 *
	 * @return xaseQuestion[]
	 */
	public static function getUnansweredQuestionsOfUser($assisted_exercise_object_id, $user_id) {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$sql = "SELECT question.id FROM xase_question as question
				where question.id not in (SELECT question_id from xase_answer as answer where answer.user_id =  " . $ilDB->quote($user_id, 'integer') . ")
				and question.assisted_exercise_id = " . $ilDB->quote($assisted_exercise_object_id, 'integer');

		$set = $ilDB->query($sql);

		$arr_questions = array();
		while($row = $ilDB->fetchAssoc($set)) {
			$arr_answers[] = new xaseQuestion($row['id']);
		}

		return $arr_answers;
	}
}