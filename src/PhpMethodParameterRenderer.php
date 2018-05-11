<?php
namespace Sellastica\PhpGenerator;

use Sellastica\Reflection\ReflectionProperty;

class PhpMethodParameterRenderer extends AbstractRenderer
{
	/** @var string */
	private $name;
	/** @var string|null */
	private $type;
	/** @var mixed */
	private $defaultValue;
	/** @var bool */
	private $renderDefaultValue = false;


	/**
	 * @param string $name
	 */
	public function __construct(string $name)
	{
		$this->name = $name;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function type(string $type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @param mixed $defaultValue
	 * @param bool $convertToPrintable
	 * @return PhpMethodParameterRenderer
	 */
	public function defaultValue($defaultValue, $convertToPrintable = false)
	{
		$this->defaultValue = true === $convertToPrintable
			? $this->convertValueToPrintable($defaultValue)
			: $defaultValue;
		$this->renderDefaultValue = true;
		return $this;
	}

	/**
	 * @param $value
	 * @return string
	 */
	private function convertValueToPrintable($value)
	{
		if (is_array($value)) {
			$value = '[]';
		} elseif (is_string($value)) {
			$value = "'$value'";
		}

		return $value;
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$return = [];
		if ($this->type && $this->type !== 'mixed') {
			$return[] = $this->type;
		}

		$return[] = '$' . $this->name;
		if ($this->renderDefaultValue) {
			$return[] = '=';
			$return[] = $this->renderValue($this->defaultValue);
		}

		return $this->renderLine(implode(' ', $return));
	}

	/**
	 * @param ReflectionProperty $property
	 * @param bool $typehint
	 * @return $this
	 */
	public static function fromReflectionPropertyMapper(ReflectionProperty $property, $typehint = true)
	{
		$parameter = (new self($property->getName()));
		if (true === $typehint) {
			$parameter->type($property->getType());
		}

		if ($property->getDefaultValue()) {
			$parameter->defaultValue($property->getDefaultValue());
		} elseif ($property->isNullable()) {
			$parameter->defaultValue(null);
		}

		return $parameter;
	}

	/**
	 * @param string $name
	 * @return PhpMethodParameterRenderer
	 */
	public static function fromName(string $name)
	{
		return (new self($name));
	}
}