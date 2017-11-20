<?php

class xaseQuestionAccess {

	public static function hasReadAccess(xaseSetting $xase_settings = NULL, xaseQuestion $xaseQuestion = NULL) {
		$access = new ilObjAssistedExerciseAccess();

		return $access->hasReadAccess() || self::isOwnerOfItem($xase_settings, $xaseQuestion) || self::hasNoOwner($xase_settings, $xaseQuestion);
	}


	public static function hasWriteAccess(xaseSetting $xase_settings = NULL, xaseQuestion $xaseQuestion = NULL) {
		$access = new ilObjAssistedExerciseAccess();

		return $access->hasWriteAccess() || self::isOwnerOfItem($xase_settings, $xaseQuestion) || self::hasNoOwner($xase_settings, $xaseQuestion);
	}


	public static function hasDeleteAccess(xaseSetting $xase_settings = NULL, xaseQuestion $xaseQuestion = NULL) {
		$access = new ilObjAssistedExerciseAccess();

		return $access->hasDeleteAccess() || self::isOwnerOfItem($xase_settings, $xaseQuestion) || self::hasNoOwner($xase_settings, $xaseQuestion);
	}


	private static function hasNoOwner(xaseSetting $xase_settings, xaseQuestion $xase_question) {
		if ($xase_settings === NULL || $xase_question === NULL) {
			return false;
		}

		if ($xase_settings->getModus() !== xaseQuestionTableGUIMODUS2) {
			return false;
		}

		return empty($xase_question->getUserId());
	}


	private static function isOwnerOfItem(xaseSetting $xase_settings, xaseQuestion $xase_question) {
		global $DIC;

		if ($xase_settings === NULL || $xase_question === NULL) {
			return false;
		}

		if ($xase_settings->getModus() !== xaseQuestionTableGUIMODUS2) {
			return false;
		}

		return $xase_question->getUserId() === $DIC->user()->getId();
	}
}

