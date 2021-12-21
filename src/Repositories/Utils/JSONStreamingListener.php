<?php namespace Maclof\Kubernetes\Repositories\Utils;

use Closure;

class JSONStreamingListener
{
	/**
	 * The closure to call.
	 */
	protected Closure $closure;

	/**
	 * The current depth within the JSON stream.
	 */
	protected int $depth = 0;

	/**
	 * The current JSON object stack.
	 */
	protected array $stack = [];

	/**
	 * The current key for each depth.
	 */
	protected array $keyByDepth = [];

	/**
	 * The current position within the JSON stream.
	 */
	protected array $breadcrumbs = [];

	/**
	 * The target containing the JSON objects.
	 */
	protected array $target = [];

	/**
	 * The constructor.
	 */
	public function __construct(Closure $closure)
	{
		$this->closure = $closure;
	}

	/**
	 * Set the target from the given key
	 */
	public function setTargetFromKey(string $key) : self
	{
		// Turn the key using dot notation into an array
		$this->target = array_filter(explode('.', $key));

		return $this;
	}

	/**
	 * Listen to the start of the document.
	 */
	public function startDocument() : void
	{
		// Ignore the start of the document
	}

	/**
	 * Listen to the end of the document.
	 */
	public function endDocument() : void
	{
		// Free the memory at the end of the document
		$this->stack = [];
		$this->keyByDepth = [];
		$this->breadcrumbs = [];
	}

	/**
	 * Listen to the start of the object.
	 */
	public function startObject() : void
	{
		// Every object increases the depth when it starts
		$this->depth++;

		// Only add objects within the target or all objects if no target is set
		if ($this->shouldBeExtracted()) {
			$this->stack[] = [];
		}
	}

	/**
	 * Determine whether the current object should be extracted
	 */
	protected function shouldBeExtracted() : bool
	{
		// All JSON objects should be extracted if no target is set
		if (empty($this->target)) {
			return true;
		}

		// Determine whether the current JSON object is within the target
		$length = count($this->target);
		return array_slice($this->breadcrumbs, 0, $length) === $this->target;
	}

	/**
	 * Listen to the end of the object.
	 */
	public function endObject() : void
	{
		// Every object decreases the depth when it ends
		$this->depth--;

		if ($this->shouldBeSkipped()) {
			return;
		}

		// When an object ends, update the current position to the object's parent key
		array_pop($this->breadcrumbs);

		$object = array_pop($this->stack);

		// If the stack is empty, the object has been fully extracted and can be processed
		// Otherwise it is a nested object to be paired to a key or added to an array
		if (empty($this->stack) && !empty($object)) {
			$this->processExtractedObject($object);
		} else {
			$this->value($object);
		}
	}

	/**
	 * Determine whether the current object should be skipped
	 */
	protected function shouldBeSkipped() : bool
	{
		return !$this->shouldBeExtracted();
	}

	/**
	 * Process the given extracted object.
	 */
	protected function processExtractedObject(array $extractedObject): void
	{
		$type = isset($extractedObject['type']) ? $extractedObject['type'] : null;
		$object = isset($extractedObject['object']) ? $extractedObject['object'] : null;

		call_user_func_array($this->closure, [
			$type,
			$object
		]);
	}

	/**
	 * Listen to the start of the array.
	 */
	public function startArray() : void
	{
		// If the document starts with an array, ignore it
		if ($this->depth === 0) {
			return;
		}

		$this->depth++;

		// Asterisks indicate that the current position is within an array
		if (!empty($this->target)) {
			$this->breadcrumbs[] = '*';
		}

		// If the target is an array, extract its JSON objects but ignore the wrapping array
		if ($this->shouldBeExtracted() && !$this->isTarget()) {
			$this->stack[] = [];
		}
	}

	/**
	 * Determine whether the current element is the target
	 */
	protected function isTarget() : bool
	{
		if (empty($this->target)) {
			return false;
		}

		// An element is the target if their positions and depths coincide
		return $this->shouldBeExtracted() && count($this->target) === $this->depth;
	}

	/**
	 * Listen to the end of the array.
	 */
	public function endArray() : void
	{
		// If the document ends with an array, ignore it
		if ($this->depth === 0) {
			return;
		}

		$this->depth--;

		// Update the current position if a target is set
		if (!empty($this->target)) {
			array_pop($this->breadcrumbs);
		}

		// If the target is an array, extract its JSON objects but ignore the wrapping array
		if ($this->shouldBeSkipped() || $this->isTarget()) {
			return;
		}

		// The nested array is ready to be paired to a key or added to an array
		$this->value(array_pop($this->stack));
	}

	/**
	 * Listen to the key.
	 */
	public function key(string $key) : void
	{
		$this->keyByDepth[$this->depth] = $key;

		// Update the current position if a target is set
		if (!empty($this->target)) {
			$this->breadcrumbs[$this->depth - 1] = $key;
			$this->breadcrumbs = array_slice($this->breadcrumbs, 0, $this->depth);
		}
	}

	/**
	 * Listen to the value.
	 *
	 * @param mixed $value
	 */
	public function value($value) : void
	{
		if ($this->shouldBeSkipped()) {
			return;
		}

		$object = array_pop($this->stack);

		// Pair the value to the current key if set or add the value to an array
		if (empty($this->keyByDepth[$this->depth])) {
			$object[] = $value;
		} else {
			$object[$this->keyByDepth[$this->depth]] = $value;
			$this->keyByDepth[$this->depth] = null;
		}

		$this->stack[] = $object;
	}

	/**
	 * Listen to the whitespace.
	 */
	public function whitespace(string $whitespace) : void
	{
		// Ignore the whitespaces
	}
}
