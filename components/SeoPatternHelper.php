<?php

namespace romi45\seoContent\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class SeoPatternHelper {
	const MODEL_ATTRIBUTE_PATTERN_PREFIX = 'model_';
	const PATTERN_DELIMETER = '%%';

	protected static function sanitizePatternString($patternString) {
		$patternString = strip_tags($patternString);

		return $patternString;
	}

	protected static function sanitizeReplacedString($replacedString) {
		$replacedString = trim($replacedString);

		return $replacedString;
	}

	protected static function addPatternDelimeter($patternKey) {
		return self::PATTERN_DELIMETER . $patternKey . self::PATTERN_DELIMETER;
	}

	protected static function getModelAttributeNameFromPatternKey($patternKey) {
		return str_replace(self::MODEL_ATTRIBUTE_PATTERN_PREFIX, '', $patternKey);
	}

	protected static function getPatternRegExp() {
		$patternDelimeter = self::PATTERN_DELIMETER;
		return '/'.$patternDelimeter.'([^'.$patternDelimeter[0].']+)'.$patternDelimeter.'?/iu';
	}

	public static function findPatterns($patternString) {
		$patternString = self::sanitizePatternString($patternString);

		$patternRegExp = self::getPatternRegExp();
		preg_match_all($patternRegExp, $patternString, $patternsMatches);

		return $patternsMatches[1];
	}

	public static function retrieveModelAttribute($model, $patternKey) {
		$modelAttributeName = self::getModelAttributeNameFromPatternKey($patternKey);

		return $model->{$modelAttributeName};
	}

	public static function replace($patternString, Component $model) {
		$patternString = '%%model_title%%';
		$replacedString = '';
		$patterns = self::findPatterns($patternString);

		$replacements = [];
		foreach ($patterns as $patternKey) {
			// Deal with variable variable names first.
			if (strpos($patternKey, self::MODEL_ATTRIBUTE_PATTERN_PREFIX) === 0) {
				$replacement = self::retrieveModelAttribute($model, $patternKey);
			}

			// Replacement retrievals can return null if no replacement can be determined, root those outs.
			if (isset($replacement)) {
				$patternKey = self::addPatternDelimeter($patternKey);
				$replacements[$patternKey] = $replacement;
			}
			unset($replacement);
		}

		// Do the actual replacements.
		if (is_array($replacements) && $replacements !== []) {
			$replacedString = str_replace(array_keys($replacements), array_values($replacements), $patternString);
		}

		$replacedString = self::sanitizeReplacedString($replacedString);

		return $replacedString;
	}
}