<?php

namespace Jlttt\cleanCode15;

class ComparisonCompactor
{

    const ELLIPSIS = "...";
    const DELTA_END = "]";
    const DELTA_START = "[";

    private $contextLength;
    private $expected;
    private $actual;
    private $prefixLength;
    private $suffixLength;

    public function __construct(int $contextLength, ?string $expected, ?string $actual)
    {
        $this->contextLength = $contextLength;
        $this->expected = $expected;
        $this->actual = $actual;
    }

    public function compact(?string $message): string
    {
        if (!$this->isCompactable()) {
            return Asserter::format($message, $this->expected, $this->actual);
        }
        $this->findCommonPrefix();
        $this->findCommonSuffix();
        $expected = $this->compactString($this->expected);
        $actual = $this->compactString($this->actual);
        return Asserter::format($message, $expected, $actual);
    }

    private function isCompactable(): bool
    {
        return !is_null($this->expected) && !is_null($this->actual) && !$this->areStringsEqual();
    }

    private function compactString(string $source): string
    {
        $result = self::DELTA_START .
            substr($source, $this->prefixLength, strlen($source) - $this->suffixLength - $this->prefixLength) .
            self::DELTA_END;

        if ($this->prefixLength > 0) {
            $result = $this->computeCommonPrefix() . $result;
        }

        if ($this->suffixLength > 0) {
            $result = $result . $this->computeCommonSuffix();
        }

        return $result;
    }

    private function findCommonPrefix()
    {
        $this->prefixLength = 0;
        $end = min(strlen($this->expected), strlen($this->actual));
        for (; $this->prefixLength < $end; $this->prefixLength++) {
            if ($this->expected[$this->prefixLength] != $this->actual[$this->prefixLength]) {
                break;
            }
        }
    }

    private function findCommonSuffix()
    {
        $this->suffixLength = 0;
        $reverseExpected = strrev($this->expected);
        $reverseActual = strrev($this->actual);
        $end = min(strlen($this->expected), strlen($this->actual)) - $this->prefixLength;
        for (; $this->suffixLength < $end; $this->suffixLength++) {
            if ($reverseExpected[$this->suffixLength] != $reverseActual[$this->suffixLength]) {
                break;
            }
        }
    }

    private function computeCommonPrefix()
    {
        $length = min($this->prefixLength, $this->contextLength);
        return ($this->prefixLength > $this->contextLength ? self::ELLIPSIS : "") .
        substr($this->expected, max(0, $this->prefixLength - $this->contextLength), $length);
    }

    private function computeCommonSuffix()
    {
        $length = min($this->suffixLength, $this->contextLength);
        return substr($this->expected, strlen($this->expected) - $this->suffixLength, $length) .
            ($this->suffixLength > $this->contextLength ? self::ELLIPSIS : "");
    }

    private function areStringsEqual(): bool
    {
        return $this->expected === $this->actual;
    }
}
