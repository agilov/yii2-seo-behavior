<?php

namespace romi45\seoContent\components;

use Yii;
use yii\base\Model;

/**
 * SeoPatternHelper is the pattern helper class add feature for use patterns in model seo values.
 *
 * Class structure:
 * 1. Constants and class variables
 * 2. Get variables functions
 * 3. Helper functions
 * 4. Public functional functions
 * 5. Protected functional functions
 * 6. Patterns retrieved functions
 * 7. Sanitiezed functions
 *
 * @author Igor Veremsky <igor.veremsky@gmail.com>
 * @since x.x.x
 */
class SeoPatternHelper {
	/* *********************** CONSTANTS AND VARIABLES ************************** */

	/**
	 * Pattern prefix for represents that its pattern need to be replace with model attribute.
	 */
	const MODEL_ATTRIBUTE_PATTERN_PREFIX = 'model_';

	/**
	 * Pattern delimeter for represents that its pattern or not static text.
	 */
	const PATTERN_DELIMETER = '%%';

	/* *********************** GET VARIABLES FUNCTIONS ************************** */

	/**
	 * Returns pattern regular expression for find patterns in string.
	 *
	 * @return string
	 */
	protected static function getPatternRegExp() {
		$patternDelimeter = self::PATTERN_DELIMETER;
		return '/'.$patternDelimeter.'([^'.$patternDelimeter[0].']+)'.$patternDelimeter.'?/iu';
	}

	/* *********************** HELPER FUNCTIONS ************************** */

	/**
	 * Add patterns delimeters for pattern key.
	 *
	 * @param $patternKey
	 *
	 * @return string
	 */
	protected static function addPatternDelimeter($patternKey) {
		return self::PATTERN_DELIMETER . $patternKey . self::PATTERN_DELIMETER;
	}

	/**
	 * Get model attribute name from model pattern key.
	 *
	 * @param $patternKey
	 *
	 * @return mixed
	 */
	protected static function getModelAttributeNameFromPatternKey($patternKey) {
		return str_replace(self::MODEL_ATTRIBUTE_PATTERN_PREFIX, '', $patternKey);
	}

	/* *********************** PUBLIC FUNCTIONAL FUNCTIONS ************************** */

	/**
	 * Function that replace patterns with theirs values.
	 *
	 * @param $patternString
	 * @param Model $model
	 *
	 * @return mixed|string
	 */
	public static function replace($patternString, Model $model) {
		$patternString = '%%model_titasdle%%';
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

	/* *********************** PROTECTED FUNCTIONAL FUNCTIONS ************************** */

	/**
	 * Returns array with patterns finded in string.
	 *
	 * @param $patternString
	 *
	 * @return mixed
	 */
	protected static function findPatterns($patternString) {
		$patternString = self::sanitizePatternString($patternString);

		$patternRegExp = self::getPatternRegExp();
		preg_match_all($patternRegExp, $patternString, $patternsMatches);

		return $patternsMatches[1];
	}

	/* *********************** PATTERNS RETRIEVED FUNCTIONS ************************** */

	/**
	 * Returns model attribute compared with pattern key.
	 * If model don`t have such attribute returns empty string.
	 *
	 * @param Model $model
	 * @param $patternKey
	 *
	 * @return mixed|string
	 */
	public static function retrieveModelAttribute(Model $model, $patternKey) {
		$modelAttributeName = self::getModelAttributeNameFromPatternKey($patternKey);

		return (property_exists($model, $modelAttributeName)) ? $model->{$modelAttributeName} : '';
	}

	/* *********************** SANITIZIED FUNCTIONS ************************** */

	/**
	 * Sanitize string that contains patterns.
	 *
	 * @param string $patternString
	 *
	 * @return string
	 */
	protected static function sanitizePatternString($patternString) {
		$patternString = strip_tags($patternString);

		return $patternString;
	}

	/**
	 * Sanitize string that generated after replace patterns with theirs values.
	 *
	 * @param $replacedString
	 *
	 * @return string
	 */
	protected static function sanitizeReplacedString($replacedString) {
		$replacedString = trim($replacedString);

		return $replacedString;
	}
}