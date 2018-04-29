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
    private $minimalLength;
    private $prefixLength;
    private $suffixLength;

    public function __construct(int $contextLength, ?string $expected, ?string $actual)
    {
        $this->contextLength = $contextLength;
        $this->expected = $expected;
        $this->actual = $actual;
        $this->minimalLength = min(strlen($this->expected), strlen($this->actual));
    }

    public function getResult(?string $message): string
    {
        if ($this->isCompactable()) {
            return $this->compact($message);
        }
        return Asserter::format($message, $this->expected, $this->actual);
    }

    private function compact(?string $message): string
    {
        $this->findCommonPrefix();
        $this->findCommonSuffix();
        $compactedExpected = $this->compactString($this->expected);
        $compactedActual = $this->compactString($this->actual);
        return Asserter::format($message, $compactedExpected, $compactedActual);
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
        for (; $this->prefixLength < $this->minimalLength; $this->prefixLength++) {
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
        $end = $this->minimalLength - $this->prefixLength;
        for (; $this->suffixLength < $end; $this->suffixLength++) {
            if ($reverseExpected[$this->suffixLength] != $reverseActual[$this->suffixLength]) {
                break;
            }
        }
    }

    private function computeCommonPrefix()
    {
        return $this->computePrefix($this->expected, $this->prefixLength, self::ELLIPSIS);
    }

    private function computeCommonSuffix()
    {
        $reverseExpected = strrev($this->expected);
        $reverseEllipsis = strrev(self::ELLIPSIS);
        $reverseSuffix = $this->computePrefix($reverseExpected, $this->suffixLength, $reverseEllipsis);
        return strrev($reverseSuffix);
    }

    private function computePrefix($source, $prefixLength, $ellipsis)
    {
        $prefix = substr(
            $source,
            max(0, $prefixLength - $this->contextLength),
            min($prefixLength, $this->contextLength)
        );
        if ($prefixLength > $this->contextLength) {
            $prefix = $ellipsis . $prefix;
        }
        return $prefix;
    }

    private function areStringsEqual(): bool
    {
        return $this->expected === $this->actual;
    }
}
