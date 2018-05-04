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
        $this->prefixLength = $this->computeCommonPrefixLength();
        $this->suffixLength = $this->computeCommonSuffixLength();
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
        $deltaLength = strlen($source) - ($this->suffixLength + $this->prefixLength);
        $result = self::DELTA_START .
            substr($source, $this->prefixLength, $deltaLength) .
            self::DELTA_END;

        if ($this->prefixLength > 0) {
            $result = $this->getCommonPrefix() . $result;
        }

        if ($this->suffixLength > 0) {
            $result = $result . $this->getCommonSuffix();
        }

        return $result;
    }

    private function computeCommonSuffixLength()
    {
        return $this->computeCommonPrefixLength(
            strrev($this->expected),
            strrev($this->actual),
            $this->minimalLength - $this->prefixLength
        );
    }

    private function computeCommonPrefixLength(?string $first = null, ?string $second = null, ?int $maxIndex = null)
    {
        if (is_null($first)) {
            $first = $this->expected;
        }
        if (is_null($second)) {
            $second = $this->actual;
        }
        if (is_null($maxIndex)) {
            $maxIndex = $this->minimalLength;
        }
        for ($prefixLength = 0; $prefixLength < $maxIndex; $prefixLength++) {
            if ($first[$prefixLength] != $second[$prefixLength]) {
                return $prefixLength;
            }
        }
        return $maxIndex;
    }

    private function getCommonPrefix()
    {
        return $this->computePrefix($this->expected, $this->prefixLength, self::ELLIPSIS);
    }

    private function getCommonSuffix()
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
