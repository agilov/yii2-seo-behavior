<?php

namespace romi45\seoContent\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

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
	 * Pattern prefix for represents that its pattern need to be replace with application params.
	 */
	const APP_PARAMETER_PATTERN_PREFIX = 'appParam_';

	/**
	 * Pattern prefix for represents that its pattern need to be replace with application configuration params.
	 */
	const APP_CONFIG_PATTERN_PREFIX = 'appConfig_';

	/**
	 * Pattern prefix for represents that its pattern need to be replace with application configuration params.
	 */
	const SEPARATOR_PATTERN_KEY = 'sep';

	/**
	 * Global view separator parameter key name.
	 */
	const SEPARATOR_VIEW_PARAMETER_KEY = 'titleSeparator';

	/**
	 * Default separator value.
	 */
	const SEPARATOR_DEFAULT = '-';

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

	/**
	 * Returns pattern prefixes options associative array
	 * where keys its patterns prefixes names and values
	 * is static callback function name that retrieve value from pattern key.
	 *
	 * @return array
	 */
	protected static function getFunctionalPatternPrefixesOptions() {
		return [
			self::MODEL_ATTRIBUTE_PATTERN_PREFIX => 'retrieveModelAttribute',
			self::APP_PARAMETER_PATTERN_PREFIX => 'retrieveAppParamValue',
			self::APP_CONFIG_PATTERN_PREFIX => 'retrieveAppConfigValue',
		];
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
	 * Returns if its functional pattern key that need to run callback function.
	 *
	 * @param $patternKey
	 *
	 * @return string
	 */
	protected static function getPatternKeyPrefix($patternKey) {
		return preg_match('/^([^_]+_)/i', $patternKey,$patternPrefixesMatches) ? $patternPrefixesMatches[0] : '';
	}

	/**
	 * Get pattern pattern key value.
	 *
	 * @param $patternKey
	 *
	 * @return mixed
	 */
	protected static function getPatternKeyValue($patternKey) {
		$patternKeyPrefix = self::getPatternKeyPrefix($patternKey);
		return str_replace($patternKeyPrefix, '', $patternKey);
	}

	/**
	 * Returns true if its functional pattern key that need to run callback function.
	 *
	 * @param $patternKey
	 *
	 * @return bool
	 */
	protected static function isCallbackPattern($patternKey) {
		$patternKeyPrefix = self::getPatternKeyPrefix($patternKey);
		$patternPrefixesOptions = self::getFunctionalPatternPrefixesOptions();
		return ArrayHelper::keyExists($patternKeyPrefix, $patternPrefixesOptions);
	}

	/**
	 * Returns true if its pattern key that need to replace with separator.
	 *
	 * @param $patternKey
	 *
	 * @return bool
	 */
	protected static function isSeparatorPattern($patternKey) {
		return $patternKey === self::SEPARATOR_PATTERN_KEY;
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
	public static function replace($patternString, $model) {
		$patternString = '%%model_title%% %%sep%% %%appParam_contactEmail%% %%appConfig_name%%';
		$replacedString = '';
		$patterns = self::findPatterns($patternString);

		$replacements = [];
		foreach ($patterns as $patternKey) {
			if (self::isCallbackPattern($patternKey)) {
				$replacement = self::callbackRetrievedStaticFunction($patternKey, $model);
			}

			if (self::isSeparatorPattern($patternKey)) {
				$replacement = self::retrieveSeparator();
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
	 * @return array
	 */
	protected static function findPatterns($patternString) {
		$patternString = self::sanitizePatternString($patternString);

		$patternRegExp = self::getPatternRegExp();

		return (preg_match_all($patternRegExp, $patternString, $patternsMatches)) ? $patternsMatches[1] : [];
	}

	/**
	 * Callback retrieved function based on callback pattern key prefix.
	 *
	 * @param $patternKey
	 * @param $model
	 *
	 * @return mixed
	 * @throws InvalidConfigException
	 */
	protected static function callbackRetrievedStaticFunction($patternKey, $model) {
		$patternPrefixesOptions = self::getFunctionalPatternPrefixesOptions();
		$patternKeyPrefix = self::getPatternKeyPrefix($patternKey);
		$patternKeyValue = self::getPatternKeyValue($patternKey);
		$patternPrefixFunctionName = ArrayHelper::getValue($patternPrefixesOptions, $patternKeyPrefix);

		if (!method_exists(__CLASS__, $patternPrefixFunctionName)) {
			throw new InvalidConfigException('"'.__CLASS__.'" does not exist function with name "'.$patternPrefixFunctionName.'"');
		}

		return call_user_func([__CLASS__, $patternPrefixFunctionName], $patternKeyValue, $model);
	}

	/* *********************** PATTERNS RETRIEVED FUNCTIONS ************************** */

	/**
	 * Returns model attribute compared with pattern key.
	 * If model don`t have such attribute returns empty string.
	 *
	 * @param $patternKeyValue
	 * @param Model $model
	 *
	 * @return mixed|string
	 */
	public static function retrieveModelAttribute($patternKeyValue, Model $model) {
		return ($model->canGetProperty($patternKeyValue)) ? $model->{$patternKeyValue} : '';
	}

	/**
	 * Returns application params compared with pattern key.
	 * If yii parameters don`t have parameter with such key returns empty string.
	 *
	 * @param $patternKey
	 * @param Model $model
	 *
	 * @return mixed|string
	 */
	public static function retrieveAppParamValue($patternKeyValue, Model $model) {
		return ArrayHelper::getValue(Yii::$app->params, $patternKeyValue);
	}

	/**
	 * Returns application global config value compared with pattern key.
	 * If yii parameters don`t have parameter with such key returns empty string.
	 *
	 * @param $patternKey
	 * @param Model $model
	 *
	 * @return mixed|string
	 */
	public static function retrieveAppConfigValue($patternKeyValue, Model $model) {
		return (property_exists(Yii::$app, $patternKeyValue) || Yii::$app->canGetProperty($patternKeyValue)) ? Yii::$app->{$patternKeyValue} : '';
	}

	/**
	 * Returns separator.
	 * If yii parameters don`t have parameter with such key returns empty string.
	 * If yii parameters don`t have parameter with such key returns empty string.
	 *
	 * @param $patternKey
	 * @param Model $model
	 *
	 * @return mixed|string
	 */
	public static function retrieveSeparator() {
		$separatorViewParamKey = self::SEPARATOR_VIEW_PARAMETER_KEY;
		return (ArrayHelper::keyExists($separatorViewParamKey, Yii::$app->view->params)) ? Yii::$app->view->params[$separatorViewParamKey] : self::SEPARATOR_DEFAULT;
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