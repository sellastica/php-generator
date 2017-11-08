<?php
namespace Sellastica\PhpGenerator;

class PhpPropertyRenderer extends AbstractRenderer
{
	/** @var string */
	private $name;
	/** @var string */
	private $visibility;
	/** @var string */
	private $defaultValue;
	/** @var PhpDocRenderer|null */
	private $annotation;


	/**
	 * @param string $name
	 * @param string $visibility
	 */
	public function __construct(string $name, string $visibility = 'private')
	{
		$this->name = $name;
		$this->visibility = $visibility;
	}

	/**
	 * @return PhpDocRenderer
	 */
	public function createAnnotation(): PhpDocRenderer
	{
		return $this->annotation = new PhpDocRenderer();
	}

	/**
	 * @param mixed $value
	 * @return $this
	 */
	public function defaultValue($value)
	{
		$this->defaultValue = $value;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function makePublic()
	{
		$this->visibility = 'public';
		return $this;
	}

	/**
	 * @return $this
	 */
	public function makeProtected()
	{
		$this->visibility = 'protected';
		return $this;
	}

	/**
	 * @return $this
	 */
	public function makePrivate()
	{
		$this->visibility = 'private';
		return $this;
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$return = [];
		if ($this->annotation) {
			$return[] = $this->annotation->renderAsPropertyAnnotation();
		}

		$property = $this->visibility . ' $' . $this->name;
		if (isset($this->defaultValue)) {
			$property .= ' = ' . $this->renderValue($this->defaultValue);
		}

		$return[] = $this->renderLine($property . ';');

		return implode("\n", $return);
	}
}